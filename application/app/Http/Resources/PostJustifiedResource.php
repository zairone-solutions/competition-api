<?php

namespace App\Http\Resources;

class PostJustifiedResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $votes = time() > strtotime($this->competition->announcement_at) ? $this->number_format_short($this->votes->count()) : NULL;
        $objection = !($this->approved_at) ? PostObjectionResource::make($this->objection) : NULL;

        return [
            'id' => $this->id,
            'description' => $this->description,
            'posted_at' => $this->time2str($this->created_at),
            'votes' => $votes,
            'objection' => $objection,
            "winner" => $this->competition->winner_id == $this->user->id,
            "user" => UserResource::make($this->user),
            'images' => PostImageResource::collection($this->images),
        ];
    }

}
