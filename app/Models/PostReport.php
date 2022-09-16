<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostReport extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "reporter_id",
        "organizer_id",
        "post_id",
        "description",
        "cleared",
    ];

    // scopes
    public function scopeCleared($query)
    {
        return $query->where("cleared", 1);
    }

    // relations
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    public function reporter()
    {
        return $this->belongsTo(User::class, "reporter_id");
    }
    public function organizer()
    {
        return $this->belongsTo(User::class, "organizer_id");
    }
}
