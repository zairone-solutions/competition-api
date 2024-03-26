<?php

namespace App\Http\Resources;

class PaymentMethodResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'title' => $this->title,
            'code' => $this->code,
            // 'credentials' => json_decode($this->credentials),
        ];
    }
}
