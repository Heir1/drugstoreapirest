<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Molecule;

class Molecule extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    public function articles()
    {
        return $this->belongsToMany(Article::class);
    }
}
