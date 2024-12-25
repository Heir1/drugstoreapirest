<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    public function articles()
    {
        return $this->belongsToMany(Article::class);
    }
}
