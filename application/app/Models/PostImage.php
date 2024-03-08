<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "post_id",
        "image",
        "mime_type"
    ];

    public $timestamps = false;

    // relations
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

}
