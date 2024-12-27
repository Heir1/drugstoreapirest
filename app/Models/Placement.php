<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Placement extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'row_id',
        'created_by',
        'updated_by',
    ];


    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_placement', 'placement_id', 'article_id');
    }


    // Génération automatique du row_id lors de la création d'un placement
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($placement) {
            if (!$placement->row_id) {
                $placement->row_id = (string) \Str::uuid();  // Générer un UUID pour row_id si non défini
            }
        });
    }
}
