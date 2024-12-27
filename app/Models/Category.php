<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    // Définir les attributs que vous pouvez remplir en masse
    protected $fillable = [
        'id',
        'name',
        'row_id',
        'created_by',
        'updated_by',
    ];

    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id');  // clé étrangère 'category_id'
    }

    protected static function booted()
    {
        static::creating(function ($category) {
            if (!$category->row_id) {
                $category->row_id = (string) Str::uuid();
            }
        });
    }
}
