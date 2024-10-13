<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'full_name',
        'email',
        'password',
        'phone_code',
        'phone_no',
        'type',
        'balance',
        'notification_token',
        'email_verification_code',
        'email_verification_code_at',
        'email_verified_at',
        'auth_provider',
        'avatar',
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

    public function createToken(string $name, $device = NULL, array $abilities = ['*'])
    {
        $token = $this->tokens()->create([
            'name'      => $name,
            'token'     => hash('sha256', $plainTextToken = Str::random(40)),
            'abilities' => $abilities,
            'device'   => $device,
        ]);

        return new NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
    }
    // scopes
    function scopeEmail()
    {
        return $this->where("auth_provider", "email");
    }
    // relations
    public function password_resets()
    {
        return $this->hasMany(PasswordReset::class);
    }
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
        return $this->hasMany(PostVote::class, 'voter_id');
    }
    public function participations()
    {
        return $this->hasMany(CompetitionParticipant::class, 'participant_id');
    }
    public function post_comments()
    {
        return $this->hasMany(PostComment::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function ledgers()
    {
        return $this->hasMany(Ledger::class);
    }
    public function got_reports()
    {
        return $this->hasMany(PostReport::class, "organizer_id");
    }
    public function reports()
    {
        return $this->hasMany(PostReport::class, "reporter_id");
    }
    public function category_suggests()
    {
        return $this->hasMany(Category::class, "suggest_id");
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
