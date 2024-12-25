<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
