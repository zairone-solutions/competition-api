<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostObjection extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "post_id",
        "description",
        "cleared",
    ];

    // relations
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
