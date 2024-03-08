<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'verified',
        'suggest_id',
    ];
    // scopes
    public function scopeVerified($query)
    {
        return $query->where('verified', '=', '1');
    }
    // relations
    public function suggested_by()
    {
        return $this->belongsTo(User::class, "suggest_id");
    }
}
