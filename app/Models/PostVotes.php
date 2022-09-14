<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostVotes extends Model
{
    use HasFactory;

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
