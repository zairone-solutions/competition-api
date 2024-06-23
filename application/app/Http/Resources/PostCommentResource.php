<?php

namespace App\Http\Resources;

class PostCommentResource extends BaseResource
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
            'post_id' => $this->post_id,
            'type' => $this->type,
            'text' => $this->text,
            'hidden' => $this->hidden === 1,
            'by' => UserResource::make($this->user),
            'date' => $this->time2str($this->created_at),
            'replies' => PostCommentResource::collection($this->replies)
        ];
    }
}
