<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indication extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'row_id',
        'created_by',
        'updated_by',
    ];

    /**
     * Générer automatiquement un UUID pour row_id avant d'enregistrer l'enregistrement.
     */
    protected static function booted()
    {
        static::creating(function ($indication) {
            if (!$indication->row_id) {
                $indication->row_id = (string) Str::uuid();
            }
        });
    }


    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_indication', 'indication_id', 'article_id');
    }
}
