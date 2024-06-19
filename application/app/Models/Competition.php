<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "category_id",
        "winner_id",
        "winner_post",
        "title",
        "description",
        "slug",
        "participants_allowed",
        "announcement_at",
        "voting_start_at",
        "published_at",
        "payment_verified_at",
    ];

    // conditions
    public function isPublished()
    {
        return $this->published_at;
    }
    public function isExpired()
    {
        return strtotime($this->announcement_at) < time();
    }
    // scopes
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
    public function scopeNotOrganizerBySelf($query)
    {
        return $query->where("organizer_id", "!=", auth()->id());
    }
    public function scopeUpForParticipation($query)
    {
        return $query->published()->notOrganizerBySelf()->where("voting_start_at", ">", date("Y-m-d H:i:s"));
    }

    // relations
    public function organizer()
    {
        return $this->belongsTo(User::class, "organizer_id");
    }
    public function financial()
    {
        return $this->hasOne(CompetitionFinancial::class);
    }
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    public function votes()
    {
        return $this->hasMany(PostVote::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function participants()
    {
        return $this->hasMany(CompetitionParticipant::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
