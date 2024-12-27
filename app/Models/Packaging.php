<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Packaging extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'row_id',
        'created_by',
        'updated_by',
    ];

    // Relation One-to-Many avec Article
    public function articles()
    {
        return $this->hasMany(Article::class, 'packaging_id');  // clé étrangère 'packaging_id'
    }

    // Génération automatique du row_id lors de la création d'un packaging
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($packaging) {
            if (!$packaging->row_id) {
                $packaging->row_id = (string) \Str::uuid();  // Générer un UUID pour row_id si non défini
            }
        });
    }

}
