<?php

namespace App\Http\Resources;

class PostObjectionResource extends BaseResource
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
            "id" => $this->id,
            "description" => $this->description,
            "cleared" => $this->cleared,
            "date" => date("M d, Y", strtotime($this->created_at)),
        ];
    }
}
