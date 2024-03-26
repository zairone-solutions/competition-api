<?php

namespace App\Http\Resources;

use App\Helpers\CompetitionHelper;
use App\Models\Competition;

class CompetitionPaymentResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            "id" => $this->id,
            "title" => $this->title,
            "method" => PaymentMethodResource::make($this->method),
            "user" => UserResource::make($this->user),
            "type" => $this->type,
            "device" => $this->device,
            "discount" => $this->discount,
            "amount" => $this->amount,
            "verified_at" => $this->time2str($this->verified_at),
        ];

        return $data;
    }
}
