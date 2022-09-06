<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    // relations
    public function images()
    {
        return $this->hasMany(PostImages::class);
    }
    public function votes()
    {
        return $this->hasMany(PostVotes::class);
    }
}
