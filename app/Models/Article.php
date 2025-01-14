<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string'; // Because we're using UUID

    // Définir les attributs que vous pouvez remplir en masse
    protected $fillable = [
        'id',
        'barcode',
        'description',
        'alert',
        'expiration_date',
        'quantity',
        'purchase_price',
        'selling_price',
        'is_active',
        'comment',
        'row_id',
        'currency_id', // Relation vers la devise
        'category_id',  // Référence à la catégorie
        'packaging_id',  // Référence au packaging
        'created_by',
        'updated_by',
    ];


    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');  // clé étrangère 'category_id'
    }

    public function molecules()
    {
        return $this->belongsToMany(Molecule::class, 'article_molecule', 'article_id', 'molecule_id');
    }

    public function indications()
    {
        return $this->belongsToMany(Indication::class, 'article_indication', 'article_id', 'indication_id');
    }

    public function placements()
    {
        return $this->belongsToMany(Placement::class, 'article_placement', 'article_id', 'placement_id');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'article_supplier', 'article_id', 'supplier_id');
    }

    public function packaging()
    {
        return $this->belongsTo(Packaging::class, 'packaging_id');  // clé étrangère 'packaging_id'
    }

    public function invoiceLines()
    {
        return $this->hasMany(InvoiceLine::class);
    }


    protected static function booted()
    {
        static::creating(function ($article) {
            if (!$article->row_id) {
                $article->row_id = (string) Str::uuid();
            }
        });
    }
    
}
