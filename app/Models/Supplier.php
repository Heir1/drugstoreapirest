<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';


    protected $fillable = [
        'name',
        'row_id',  // Ajoute row_id dans les attributs modifiables
        'created_by',
        'updated_by',
    ];

    /**
     * Générer automatiquement un UUID pour row_id avant d'enregistrer l'enregistrement.
     */
    protected static function booted()
    {
        static::creating(function ($supplier) {
            // Générer un UUID pour row_id si ce n'est pas déjà défini
            if (empty($supplier->row_id)) {
                $supplier->row_id = (string) Str::uuid();
            }
        });
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_supplier', 'supplier_id', 'article_id');
    }
}
