<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionParticipants extends Model
{
    use HasFactory;

    // relations
    public function competition()
    {
        return $this->belongsTo(Competition::class, "competition_id");
    }
    public function participant()
    {
        return $this->belongsTo(User::class, "participant_id");
    }
}
