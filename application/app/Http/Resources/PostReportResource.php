<?php

namespace App\Http\Resources;

class PostReportResource extends BaseResource
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
            "cleared" => $this->cleared == 1,
            "date" => date("M d, Y", strtotime($this->created_at)),
            "post" => PostMinimalResource::make($this->post),
            "competition" => CompetitionMinimalResource::make($this->post->competition),
        ];
    }
}
