<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // relations
    public function competitions()
    {
        return $this->hasMany(Competition::class, 'organizer_id');
    }
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    public function votes()
    {
        return $this->hasMany(PostVotes::class, 'voter_id');
    }
    public function competition_comments()
    {
        return $this->hasMany(CompetitionComments::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function ledgers()
    {
        return $this->hasMany(Ledger::class);
    }
}
