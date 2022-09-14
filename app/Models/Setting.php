<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        "key",
        "title",
        "value",
        "type",
        "rule",
        "parent_id"
    ];

    // scopes
    public function scopeIsParent()
    {
        return self::where("parent_id", NULL);
    }
    public function scopeIsChildren()
    {
        return self::where("parent_id", "!=", NULL);
    }
    // relations
    public function parent()
    {
        return $this->belongsTo(Setting::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(Setting::class, 'parent_id');
    }
}
