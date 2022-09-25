<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "description",
        "hidden",
        "user_id",
        "competition_id",
        "approved_at",
    ];

    // scopes
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }
    public function scopeVisible($query)
    {
        return $query->where('hidden', "0");
    }

    // relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function images()
    {
        return $this->hasMany(PostImage::class);
    }
    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }
    public function votes()
    {
        return $this->hasMany(PostVote::class);
    }
    public function reports()
    {
        return $this->hasMany(PostReport::class);
    }
    public function objection()
    {
        return $this->hasOne(PostObjection::class);
    }
}
