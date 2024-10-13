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
        "state",
        "approved_at",
    ];

    // scopes
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }
    public function scopeWon($query)
    {
        return $query->whereNotNull('approved_at');
    }
    public function scopeVisible($query)
    {
        return $query->where('hidden', "0");
    }
    public function scopeVoted($query)
    {
        return $query->where('state', "voted");
    }
    public function scopeDraft($query)
    {
        return $query->where('state', "draft");
    }
    public function scopeCreated($query)
    {
        return $query->where('state', "created");
    }
    public function scopeWithMaxVotes($query, Competition $competition)
    {
        $max_votes = $this->select('posts.*')
            ->where("posts.competition_id", $competition->id)
            ->leftJoin('post_votes', 'posts.id', '=', 'post_votes.post_id')
            ->leftJoin('competitions', 'posts.competition_id', '=', 'competitions.id')
            ->selectRaw('posts.*, COUNT(post_votes.id) as votes_count')
            ->groupBy('posts.id')
            ->havingRaw('COUNT(post_votes.id) = (SELECT COUNT(post_votes.id) FROM post_votes WHERE post_votes.post_id = posts.id GROUP BY post_votes.post_id ORDER BY COUNT(post_votes.id) DESC LIMIT 1)')
            ->get()->pluck("votes_count")->first();

        return $query->selectRaw('posts.*')
            ->where("posts.competition_id", $competition->id)
            ->leftJoin('post_votes', 'posts.id', '=', 'post_votes.post_id')
            ->leftJoin('competitions', 'posts.competition_id', '=', 'competitions.id')
            ->selectRaw('posts.*, COUNT(post_votes.id) as votes_count')
            ->groupBy('posts.id')
            ->havingRaw("votes_count = $max_votes");

    }
    // relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function media()
    {
        return $this->hasMany(PostMedia::class);
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
    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }
    public function objection()
    {
        return $this->hasOne(PostObjection::class);
    }
}
