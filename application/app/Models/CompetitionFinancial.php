<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionFinancial extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "competition_id",
        "cost",
        "platform_charges",
        "entry_fee",
        "prize_money",
        "total",
    ];
    // relations
    public function competition()
    {
        return $this->belongsTo(Competition::class, "competition_id");
    }
}
