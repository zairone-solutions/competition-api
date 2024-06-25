<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_id',
        'user_id',
        'title',
        'amount',
        'type',
    ];

    // relations
    public function user_by()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
