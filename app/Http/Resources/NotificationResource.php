<?php

namespace App\Http\Resources;

class NotificationResource extends BaseResource
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
            'title' => $this->title,
            'description' => $this->description,
            'read' => $this->read,
            'for' => $this->for,
            'data' => $this->data,
            'date' => $this->time2str($this->created_at)
        ];
    }


    private function getFor($model)
    {
    }
}
