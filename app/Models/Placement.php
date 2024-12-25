<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Placement extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    public function articles()
    {
        return $this->belongsToMany(Article::class);
    }
}
