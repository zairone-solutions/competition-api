<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "competition_id",
        "user_id",
        "method_id",
        "type",
        "title",
        "device",
        "discount",
        "amount",
        "verified_at"
    ];


    // scopes
    public function scopeVerified($query)
    {
        return $query->whereNotNull("verified_at");
    }
    public function scopeByOrganizer($query, $organizer_id)
    {
        return $query->where(['type' => "from", 'user_id' => $organizer_id,]);
    }

    // relations
    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, "method_id");
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }
}
