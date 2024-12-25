<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string'; // Because we're using UUID


    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function molecules()
    {
        return $this->belongsToMany(Molecule::class);
    }

    public function indications()
    {
        return $this->belongsToMany(Indication::class);
    }

    public function placements()
    {
        return $this->belongsToMany(Placement::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function packaging()
    {
        return $this->belongsTo(Packaging::class);
    }
    
}
