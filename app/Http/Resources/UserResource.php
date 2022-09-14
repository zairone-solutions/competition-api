<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $avatar = $this->avatar ?? asset('storage/images/"user.png');
        return [
            "full_name" => $this->full_name,
            "email" => $this->email,
            "username" => $this->username,
            "avatar" => $avatar
        ];
    }
}
