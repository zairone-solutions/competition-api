<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostVote extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "post_id",
        "voter_id",
        "competition_id"
    ];
    // relations
    public function voters()
    {
        return $this->belongsToMany(User::class, "voter_id");
    }
    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
