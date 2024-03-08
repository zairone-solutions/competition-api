<?php

namespace App\Http\Resources;

class AuthUserResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $avatar = $this->avatar ?? asset('storage/images/user.jpg');
        return [
            "full_name" => $this->full_name,
            "email" => $this->email,
            "username" => $this->username,
            "avatar" => $avatar,
            "phone_code" => $this->phone_code,
            "phone_no" => $this->phone_no,
            "balance" => $this->balance,
            "auth_provider" => $this->auth_provider,
        ];
    }
}
