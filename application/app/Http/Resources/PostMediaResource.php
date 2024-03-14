<?php

namespace App\Http\Resources;

class PostMediaResource extends BaseResource
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
            'id' => $this->id,
            'url' => $this->media,
            'thumbnail' => $this->thumbnail,
            'type' => $this->type,
            'mime' => $this->mime,
        ];
    }
}
