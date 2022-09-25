<?php

namespace App\Http\Resources;

class PostOrganizerResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $votes = $this->number_format_short($this->votes->count());
        return [
            'id' => $this->id,
            'description' => $this->description,
            'posted_at' => $this->time2str($this->created_at),
            'votes' => $votes,
            'hidden' => $this->hidden,
            'objection' => $this->objection ? PostObjectionResource::make($this->objection) : NULL,
            "winner" => $this->competition->winner_id == $this->user->id,
            "user" => UserResource::make($this->user),
            'images' => PostImageResource::collection($this->images),
        ];
    }
}
