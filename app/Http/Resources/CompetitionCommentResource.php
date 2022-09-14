<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetitionCommentResource extends JsonResource
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

    private function time2str($ts)
    {
        if (!ctype_digit($ts))
            $ts = strtotime($ts);

        $diff = time() - $ts;
        if ($diff == 0)
            return 'now';
        elseif ($diff > 0) {
            $day_diff = floor($diff / 86400);
            if ($day_diff == 0) {
                if ($diff < 60) return 'just now';
                if ($diff < 120) return '1 min';
                if ($diff < 3600) return floor($diff / 60) . ' mins';
                if ($diff < 7200) return '1 ho';
                if ($diff < 86400) return floor($diff / 3600) . ' hos';
            }
            if ($day_diff == 1) return 'Yesterday';
            if ($day_diff < 7) return $day_diff . ' days';
            if ($day_diff < 31) return ceil($day_diff / 7) . ' weeks';
            if ($day_diff < 60) return 'last month';
            return date('F Y', $ts);
        } else {
            $diff = abs($diff);
            $day_diff = floor($diff / 86400);
            if ($day_diff == 0) {
                if ($diff < 120) return 'in a min';
                if ($diff < 3600) return 'in ' . floor($diff / 60) . ' mins';
                if ($diff < 7200) return 'in an ho';
                if ($diff < 86400) return 'in ' . floor($diff / 3600) . ' hos';
            }
            if ($day_diff == 1) return 'Tomorrow';
            if ($day_diff < 4) return date('l', $ts);
            if ($day_diff < 7 + (7 - date('w'))) return 'next week';
            if (ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
            if (date('n', $ts) == date('n') + 1) return 'next month';
            return date('F Y', $ts);
        }
    }
}
