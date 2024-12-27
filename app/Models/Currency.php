<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'value',
        'symbol',
        'row_id',
        'created_by',
        'updated_by',
    ];

    /**
     * Générer automatiquement un UUID pour row_id avant d'enregistrer l'enregistrement.
     */
    protected static function booted()
    {
        static::creating(function ($currency) {
            if (!$currency->row_id) {
                $currency->row_id = (string) Str::uuid();
            }
        });
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'currency_id');
    }
}
