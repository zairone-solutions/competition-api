<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\RuleHelper;
use App\Http\Resources\CompetitionOrganizerResource;
use App\Http\Resources\CompetitionResource;
use App\Jobs\CompetitionParticipatedJob;
use App\Jobs\CompetitionPublishedJob;
use App\Mail\Competition\CompetitionParticipation;
use App\Mail\Competition\CompetitionParticipationAlert;
use App\Mail\Competition\CompetitionPublished;
use App\Models\Competition;
use App\Models\CompetitionComment;
use App\Models\PostComment;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class CompetitionController extends BaseController
{
    private function voted(User $user)
    {
        $voted = [];
        foreach ($user->votes()->get() as $vote) {
            $voted[] = $vote->competition()->first();
        }
        return $voted;
    }
    private function calculateCompetitionCost(float $participants)
    {
        $competition_rules = RuleHelper::rules("competition");
        $cost_per_participant = (float) $competition_rules["cost_per_participant"];

        return $cost_per_participant * $participants;
    }
    private function participated(User $user)
    {
        $participated = [];
        foreach ($user->participations()->get() as $vote) {
            $participated[] = $vote->competition()->first();
        }
        return $participated;
    }

    public function category_all(Request $request, Category $category)
    {
        try {
            return $this->resData(CompetitionResource::collection($category->competitions()->get()));
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function participation(Request $request)
    {
        try {
            return $this->resData(CompetitionResource::collection(Competition::upForParticipation()->get()));
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function explore(Request $request)
    {
        try {
            return $this->resData(CompetitionResource::collection(Competition::upForVoting()->get()));
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function get_single(Request $request, Competition $competition)
    {
        try {
            return $this->resData(CompetitionResource::make($competition));
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function calculate_financials(Request $request)
    {
        try {

            $competition_rules = RuleHelper::rules("competition");

            $cost = $this->calculateCompetitionCost($request->participants ?? 0);

            return $this->resData([
                'platform_charges' => (float) $competition_rules["platform_charges"],
                'cost' => $cost
            ]);
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }

    public function all(Request $request)
    {

        try {

            $user = auth()->user();
            if ($request->has("username")) {
                if ($findUser = User::where(["username" => $request->get("username")])->first()) {
                    $user = $findUser;
                }
            }

            switch ($request->get("type")) {
                case 'voted':
                    return $this->resData(CompetitionResource::collection($this->voted($user)));
                case 'participated':
                    return $this->resData(CompetitionResource::collection($this->participated($user)));
                case 'organized':
                    return $user->id !== auth()->id() ?
                        $this->resData(CompetitionResource::collection($user->competitions()->get())) :
                        $this->resData(CompetitionOrganizerResource::collection($user->competitions()->get()));
                default:
                    return $this->resData([
                        'organized' => $user->id !== auth()->id() ?
                            CompetitionResource::collection($user->competitions()->get()) :
                            CompetitionOrganizerResource::collection($user->competitions()->get()),
                        'participated' => CompetitionResource::collection($this->participated($user)),
                        'voted' => CompetitionResource::collection($this->voted($user))
                    ]);
            }
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }

    private function generateCompetitionSlug($title)
    {
        $slug = Str::slug($title);
        if (Competition::where("slug", $slug)->count() == 0) {
            return $slug;
        }
        do {
            $slug = Str::slug($title) . "-" . rand(111, 9999);
        } while (Competition::where("slug", $slug)->count() != 0);

        return $slug;
    }
    public function store(Request $request)
    {
        try {
            $competition_rules = RuleHelper::rules("competition");

            $rules = [
                'category_id' => [
                    "required",
                    Rule::exists('categories', "id")->where(function ($query) use ($request) {
                        return $query->where(['id' => $request->category_id, "verified" => 1]);
                    }),
                ],
                'title' => ["required", "max:50", "min:3", "bad_word"],
                'description' => ["nullable", "max:450", "bad_word"],
                'entry_fee' => ["required", "numeric", "min:" . $competition_rules["min_entry_fee"], "max:" . $competition_rules["max_prize_money"]],
                'prize_money' => ["required", "numeric", "min:" . $competition_rules["min_prize_money"], "max:" . $competition_rules["max_prize_money"]],
                'participants_allowed' => ["required", "numeric", "min:" . $competition_rules["min_participants_allowed"], "max:" . $competition_rules["max_participants_allowed"]],
                "announcement_at" => ["required", "after_or_equal:" . $competition_rules['min_competition_days'] . " days", "before_or_equal:" . $competition_rules['max_competition_days'] . " days"],
                "voting_start_at" => ["required", "after_or_equal:" . $competition_rules['voting_delay_days'] . " days"],
            ];
            $errors = $this->reqValidate($request->all(), $rules, [
                'category_id.exists' => "Invalid category.",
                'bad_word' => 'The :attribute cannot contain any inappropriate word.',
                'voting_start_at.after_or_equal' => "The voting must start after {$competition_rules['voting_delay_days']} days from today.",
                'announcement_at.after_or_equal' => "The announcement must be {$competition_rules['min_competition_days']} days after starting the competition.",
                'announcement_at.before_or_equal' => "The announcement must be {$competition_rules['max_competition_days']} days from now."
            ]);
            if ($errors)
                return $errors;

            DB::beginTransaction();

            $competition = auth()->user()->competitions()->create([
                "category_id" => $request->category_id,
                "title" => $request->title,
                "description" => $request->description,
                "slug" => $this->generateCompetitionSlug($request->title),
                "paid" => (float) $request->entry_fee > 0,
                "participants_allowed" => $request->participants_allowed,
                "announcement_at" => date("Y-m-d H:i:s", strtotime($request->announcement_at)),
                "voting_start_at" => date("Y-m-d H:i:s", strtotime($request->voting_start_at)),
            ]);

            $cost = $this->calculateCompetitionCost($request->participants_allowed);
            $total = $cost + (float) $request->prize_money + (float) $competition_rules["platform_charges"];
            $competition->financial()->create([
                "cost" => (float) $cost,
                "entry_fee" => (float) $request->entry_fee,
                "prize_money" => (float) $request->prize_money,
                "total" => (float) $total,
                "platform_charges" => $competition_rules["platform_charges"],
            ]);

            DB::commit();

            return $this->resData(CompetitionOrganizerResource::make($competition));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function update(Request $request, Competition $competition)
    {
        try {
            if ($competition->isPublished()) {
                return $this->resMsg(["error" => "Published competition can not be edited."], "authentication", 400);
            }

            $competition_rules = RuleHelper::rules("competition");

            $rules = [
                'category_id' => [
                    "required",
                    Rule::exists('categories', "id")->where(function ($query) use ($request) {
                        return $query->where(['id' => $request->category_id, "verified" => 1]);
                    }),
                ],
                'title' => ["required", "max:50", "min:3", "bad_word"],
                'slug' => ["required", "unique:competitions,slug," . $competition->id, "max:100", "min:3", "regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/", "bad_word"],
                'description' => ["nullable", "max:450", "bad_word"],
                'entry_fee' => ["required", "numeric", "min:" . $competition_rules["min_entry_fee"], "max:" . $competition_rules["max_prize_money"]],
                'prize_money' => ["required", "numeric", "min:" . $competition_rules["min_prize_money"], "max:" . $competition_rules["max_prize_money"]],
                'participants_allowed' => ["required", "numeric", "min:" . $competition_rules["min_participants_allowed"], "max:" . $competition_rules["max_participants_allowed"]],
                "announcement_at" => ["required", "after_or_equal:" . $competition_rules['min_competition_days'] . " days", "before_or_equal:" . $competition_rules['max_competition_days'] . " days"],
                "voting_start_at" => ["required", "after_or_equal:" . $competition_rules['voting_delay_days'] . " days"],
            ];
            $errors = $this->reqValidate($request->all(), $rules, [
                'category_id.exists' => "Invalid category.",
                "slug.regex" => "Hashtag format is not valid.",
                "slug.unique" => "The #hashtag has already been taken.",
                'bad_word' => 'The :attribute cannot contain any inappropriate word.',
                'voting_start_at.after_or_equal' => "The voting date must be after {$competition_rules['voting_delay_days']} days from today.",
                'announcement_at.after_or_equal' => "The announcement date must be {$competition_rules['min_competition_days']} days after starting the competition.",
                'announcement_at.before_or_equal' => "The announcement date must be {$competition_rules['max_competition_days']} days from now."
            ]);
            if ($errors)
                return $errors;
            // time validations
            if (strtotime($request->announcement_at) > strtotime("+" . $competition_rules['max_competition_days'] . " days")) {
                return $this->resMsg(["error" => "Announcement date must be before " . $competition_rules['max_competition_days'] . " days."], "validation", 403);
            }
            if (strtotime($request->announcement_at) < strtotime("+" . $competition_rules['min_competition_days'] . " days")) {
                return $this->resMsg(["error" => "Announcement date must be after " . $competition_rules['min_competition_days'] . " days."], "validation", 403);
            }
            if (strtotime($request->voting_start_at) < (strtotime($request->voting_start_at))) {
                return $this->resMsg(["error" => "Voting date must be after " . $competition_rules['voting_delay_days'] . " days."], "validation", 403);
            }

            // $slug_matches = Competition::where("slug", $request->slug)->where("id", "!=", $competition->id)->count();

            DB::beginTransaction();

            $competition->update([
                "category_id" => $request->category_id,
                "title" => $request->title,
                "paid" => (float) $request->entry_fee > 0,
                "description" => $request->description,
                "slug" => $request->slug,
                "participants_allowed" => $request->participants_allowed,
                "announcement_at" => date("Y-m-d H:i:s", strtotime($request->announcement_at)),
                "voting_start_at" => date("Y-m-d H:i:s", strtotime($request->voting_start_at)),
            ]);

            $cost = $this->calculateCompetitionCost($request->participants_allowed);
            $total = $cost + (float) $request->prize_money + (float) $competition_rules["platform_charges"];
            $competition->financial->update([
                "cost" => (float) $cost,
                "platform_charges" => $competition_rules["platform_charges"],
                "entry_fee" => (float) $request->entry_fee,
                "prize_money" => (float) $request->prize_money,
                "total" => (float) $total,
            ]);

            DB::commit();

            return $this->resData(CompetitionOrganizerResource::make($competition));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function verify_dates(Request $request, Competition $competition)
    {
        try {
            if ($competition->isPublished()) {
                return $this->resMsg(["error" => "Published competition can not be edited."], "validation", 403);
            }

            $competition_rules = RuleHelper::rules("competition");

            $rules = [
                "announcement_at" => ["required", "after_or_equal:" . $competition_rules['min_competition_days'] . " days", "before_or_equal:" . $competition_rules['max_competition_days'] . " days"],
                "voting_start_at" => ["required", "after_or_equal:" . $competition_rules['voting_delay_days'] . " days"],
            ];
            $errors = $this->reqValidate($request->all(), $rules, [
                'voting_start_at.after_or_equal' => "The voting must start after {$competition_rules['voting_delay_days']} days from today.",
                'announcement_at.after_or_equal' => "The announcement must be {$competition_rules['min_competition_days']} days after starting the competition.",
                'announcement_at.before_or_equal' => "The announcement must be {$competition_rules['max_competition_days']} days from now."
            ]);
            if ($errors)
                return $errors;
            // time validations
            if (strtotime($request->announcement_at) > strtotime("+" . $competition_rules['max_competition_days'] . " days")) {
                return $this->resMsg(["error" => "Announcement must be before " . $competition_rules['max_competition_days'] . " days."], "validation", 403);
            }
            if (strtotime($request->announcement_at) < strtotime("+" . $competition_rules['min_competition_days'] . " days")) {
                return $this->resMsg(["error" => "Announcement must be after " . $competition_rules['min_competition_days'] . " days."], "validation", 403);
            }
            if (strtotime($request->voting_start_at) < (strtotime($request->voting_start_at))) {
                return $this->resMsg(["error" => "Voting must start after " . $competition_rules['voting_delay_days'] . " days."], "validation", 403);
            }

            return $this->resData(CompetitionOrganizerResource::make($competition));
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function publish(Request $request, Competition $competition)
    {
        try {
            $competition_rules = RuleHelper::rules("competition");
            $user = auth()->user();
            if ($user->id !== $competition->organizer_id) {
                return $this->resMsg(["error" => "Only organizer can publish a competition."], "validation", 403);
            }
            if (!$competition->payment_verified_at) {
                return $this->resMsg(["error" => "Payment not verified yet."], "validation", 403);
            }
            if ($competition->isPublished()) {
                return $this->resMsg(["error" => "Competition already published."], "validation", 403);
            }

            if (strtotime($competition->voting_start_at) <= (strtotime('now') + (((int) $competition_rules['voting_delay_days']) * 24 * 60 * 60))) {
                return $this->resMsg(["error" => "Voting date must be after " . $competition_rules['voting_delay_days'] . " days from today. Please update to publish."], "validation", 403);
            }
            if (strtotime($competition->announcement_at) <= (strtotime('now') + ((int) $competition_rules['min_competition_days'] * 24 * 60 * 60))) {
                return $this->resMsg(["error" => "Announcement date must be after " . $competition_rules['min_competition_days'] . " days from today. Please update to publish."], "validation", 403);
            }

            $competition->published_at = date("Y-m-d H:i:s", strtotime("now"));
            $competition->state = "participation_period";

            DB::beginTransaction();

            $competition->update();

            // Dispatch job
            CompetitionPublishedJob::dispatch($user, $competition);

            DB::commit();

            return $this->resData(CompetitionOrganizerResource::make($competition));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }

    public function delete(Request $request, Competition $competition)
    {
        try {
            if ($competition->isPublished() && !$competition->isExpired()) {
                return $this->resMsg(["error" => "Published competition can not be deleted, wait for its completion."], "validation", 403);
            }

            DB::beginTransaction();

            $competition->delete();

            // Dispatch job
            CompetitionPublishedJob::dispatch(auth()->user(), $competition);

            DB::commit();
            return $this->resMsg(["success" => "Competition deleted successfully."]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function participate(Request $request, Competition $competition)
    {
        try {

            if (auth()->user()->id == $competition->organizer_id) {
                return $this->resMsg(["error" => "Organizer can not participate in the competition."], "authorization", 400);
            }
            if (!$competition->isPublished()) {
                return $this->resMsg(["error" => "Competition has not been published yet."], "authorization", 400);
            }
            if (auth()->user()->participations()->where('competition_id', $competition->id)->first()) {
                return $this->resMsg(["error" => "You have already participated."], "authentication", 400);
            }

            DB::beginTransaction();

            $competition->participants()->create(['participant_id' => auth()->user()->id]);

            auth()->user()->update(['type' => "participant"]);

            // Dispatch job
            CompetitionParticipatedJob::dispatch(auth()->user(), $competition);

            DB::commit();

            return $this->resData(CompetitionResource::make($competition));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
}
