<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionWinner extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "competition_id",
        "winner_id",
    ];

    public $timestamps = FALSE;

    // relations
    public function competition()
    {
        return $this->belongsTo(Competition::class, "competition_id");
    }
    public function winner()
    {
        return $this->belongsTo(User::class, "winner_id");
    }
}
