<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "title",
        "code",
        "active",
        "image",
        "credentials"
    ];
    public $timestamps = false;


    // scope
    public function scopeActive()
    {
        return $this->where("active", "1");
    }

    // relations
    public function payments()
    {
        return $this->hasMany(Payment::class, "method_id");
    }
}
