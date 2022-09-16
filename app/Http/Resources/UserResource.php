<?php

namespace App\Http\Resources;

class UserResource extends BaseResource
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
