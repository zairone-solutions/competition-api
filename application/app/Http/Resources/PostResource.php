<?php

namespace App\Http\Resources;


class PostResource extends BaseResource
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
        $data = [
            'id' => $this->id,
            'description' => $this->description,
            'state' => $this->state,
            'posted_at' => $this->time2str($this->created_at),
            'votes' => $votes,
            "winner" => $this->won === 1,
            "approved" => $this->approved_at !== null,
            "votedByMe" => FALSE,
            "user" => UserResource::make($this->user),
            'media' => PostMediaResource::collection($this->media),
            "competition" => CompetitionPostResource::make($this->competition)
        ];

        if ($this->votes()->where("voter_id", auth()->id())->count()) {
            $data['votedByMe'] = TRUE;
        }
        return $data;
    }

}
