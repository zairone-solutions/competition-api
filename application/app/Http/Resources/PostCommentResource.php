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
            'competition_id' => $this->competition_id,
            'type' => $this->type,
            'text' => $this->text,
            'hidden' => $this->hidden,
            'date' => $this->time2str($this->created_at),
        ];
    }
}
