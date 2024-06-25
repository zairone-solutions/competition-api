<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CompetitionOrganizerResource;
use App\Http\Resources\CompetitionResource;
use App\Http\Resources\PaymentMethodResource;
use App\Jobs\Competitions\CompetitionPaymentNotification;
use App\Models\Competition;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends BaseController
{
    private const CARD_RULES = [

        'card_number' => [
            'required',
            'string',
            'digits:16', // Ensure 16 digits
            'regex:/^\d+$/', // Numeric characters only
            // function ($attribute, $value, $fail) {
            //     // Optional: Implement Luhn algorithm check or integrate with a payment gateway's validation API for more robust verification.
            //     if (!app('some.card.validation.service')->isValid($value)) {
            //         $fail('The card number seems to be invalid. Please double-check.');
            //     }
            // },
        ],
        'card_name' => ['required', 'string'],
        'expiry_date' => [
            'required',
            'string',
            'regex:/^(0[1-9]|1[0-2])\/\d{2}$/', // MM/YY format
        ],
        'cvv' => [
            'required',
            'string',
            'max:3', // Ensure 3 digits
            'regex:/^\d+$/', // Numeric characters only                    ],
        ]
    ]

    ;

    public function all(Request $request)
    {

        try {

            return $this->resData(PaymentMethodResource::collection(PaymentMethod::active()->get()));
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function card_participation(Request $request)
    {

        try {

            $rules = self::CARD_RULES;

            $errors = $this->reqValidate($request->all(), $rules);
            if ($errors)
                return $errors;

            $user = auth()->user();
            $payment_method = PaymentMethod::where("code", "CC")->first();

            $competition = Competition::find($request->competition_id);

            DB::beginTransaction();

            $payment = $user->payments()->create([
                'competition_id' => $competition->id,
                'method_id' => $payment_method->id,
                'title' => $user->username . " paid competition participating fee",
                'amount' => $competition->financial->entry_fee
            ]);

            $payment->update(["verified_at" => date("Y-m-d H:i:s")]);

            $competition->update(["payment_verified_at" => date("Y-m-d H:i:s")]);

            if ($user->type !== "organizer" && $user->type !== "participant") {
                $user->update(['type' => 'participant']);
            }

            $competition->participants()->create(['participant_id' => $user->id]);

            $ledger = $user->ledgers()->create([
                'payment_id' => $payment->id,
                'title' => $payment->title,
                'amount' => $payment->amount,
                'type' => 'debit',
            ]);

            DB::commit();

            return $this->resData(CompetitionResource::make($competition));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }


    public function card_competition(Request $request)
    {

        try {

            $rules = self::CARD_RULES;

            $errors = $this->reqValidate($request->all(), $rules);
            if ($errors)
                return $errors;

            $user = auth()->user();
            $payment_method = PaymentMethod::where("code", "CC")->first();

            $competition = Competition::find($request->competition_id);

            DB::beginTransaction();

            $payment = $user->payments()->create([
                'competition_id' => $competition->id,
                'method_id' => $payment_method->id,
                'title' => $user->username . " paid competition hosting fee.",
                'amount' => $competition->financial->cost
            ]);

            $payment->update(["verified_at" => date("Y-m-d H:i:s")]);
            $competition->update(["state" => "pending_publish", "payment_verified_at" => date("Y-m-d H:i:s")]);

            if ($user->type !== "organizer") {
                $user->update(['type' => 'organizer']);
            }

            $ledger = $user->ledgers()->create([
                'payment_id' => $payment->id,
                'title' => $payment->title,
                'amount' => $payment->amount,
                'type' => 'debit',
            ]);

            DB::commit();

            return $this->resData(CompetitionOrganizerResource::make($competition));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function easy_paisa_competition(Request $request)
    {
        $competition = Competition::find($request->competition_id);
        if ($competition->payment_verified_at) {
            return $this->resMsg(["error" => "Competition bill has been paid already."], "validation", 403);
        }
        try {
            $rules = [
                'phone_number' => [
                    'required',
                    'numeric',
                    'digits:11', // Ensure 11 digits
                    'regex:/^\d+$/',
                ],
            ];
            $errors = $this->reqValidate($request->all(), $rules);
            if ($errors)
                return $errors;

            $user = auth()->user();
            $payment_method = PaymentMethod::where("code", "EP")->first();


            DB::beginTransaction();

            $payment = $user->payments()->create([
                'competition_id' => $competition->id,
                'method_id' => $payment_method->id,
                'title' => $user->username . " paid competition hosting fee.",
                'amount' => $competition->financial->cost
            ]);

            $payment->update(["verified_at" => date("Y-m-d H:i:s")]);
            $competition->update(["payment_verified_at" => date("Y-m-d H:i:s")]);

            if ($user->type !== "organizer") {
                $user->update(['type' => 'organizer']);
            }

            $ledger = $user->ledgers()->create([
                'payment_id' => $payment->id,
                'title' => $payment->title,
                'amount' => $payment->amount,
                'type' => 'debit',
            ]);

            DB::commit();

            CompetitionPaymentNotification::dispatch($user, $competition, $payment);

            return $this->resData(CompetitionOrganizerResource::make($competition));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function jazz_cash_competition(Request $request)
    {
        $competition = Competition::find($request->competition_id);
        if ($competition->payment_verified_at) {
            return $this->resMsg(["error" => "Competition bill has been paid already."], "validation", 403);
        }
        try {
            $rules = [
                'phone_number' => [
                    'required',
                    'numeric',
                    'digits:11', // Ensure 11 digits
                    'regex:/^\d+$/',
                ],
                'pin_code' => [
                    'required',
                    'numeric',
                    'digits:4', // Ensure 4 digits
                    'regex:/^\d+$/',
                ],
            ];
            $errors = $this->reqValidate($request->all(), $rules);
            if ($errors)
                return $errors;

            $user = auth()->user();
            $payment_method = PaymentMethod::where("code", "JC")->first();

            DB::beginTransaction();

            $payment = $user->payments()->create([
                'competition_id' => $competition->id,
                'method_id' => $payment_method->id,
                'title' => $user->username . " paid competition hosting fee.",
                'amount' => $competition->financial->cost
            ]);

            $payment->update(["verified_at" => date("Y-m-d H:i:s")]);
            $competition->update(["payment_verified_at" => date("Y-m-d H:i:s")]);

            if ($user->type !== "organizer") {
                $user->update(['type' => 'organizer']);
            }

            $ledger = $user->ledgers()->create([
                'payment_id' => $payment->id,
                'title' => $payment->title,
                'amount' => $payment->amount,
                'type' => 'debit',
            ]);

            DB::commit();

            CompetitionPaymentNotification::dispatch($user, $competition, $payment);

            return $this->resData(CompetitionOrganizerResource::make($competition));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
}
