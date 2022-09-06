<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionComments extends Model
{
    use HasFactory;

    // relations
    public function replies()
    {
        return $this->hasMany(self::class, 'comment_id');
    }
}
