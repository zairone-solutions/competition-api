<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasFactory;

    // relations
    public function organizer()
    {
        return $this->belongsTo(User::class, "organizer_id");
    }
    public function votes()
    {
        return $this->hasMany(PostVotes::class);
    }
    public function participants()
    {
        return $this->hasMany(CompetitionParticipants::class, "competition_id");
    }
    public function comments()
    {
        return $this->hasMany(CompetitionComments::class);
    }
}
