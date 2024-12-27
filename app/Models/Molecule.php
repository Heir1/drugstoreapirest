<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use App\Models\Molecule;

    class Molecule extends Model
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
            return $this->belongsToMany(Article::class, 'article_molecule', 'molecule_id', 'article_id');
        }

        // Génération automatique du row_id lors de la création d'un molecule
        protected static function boot()
        {
            parent::boot();

            static::creating(function ($molecule) {
                if (!$molecule->row_id) {
                    $molecule->row_id = (string) \Str::uuid();  // Générer un UUID pour row_id si non défini
                }
            });
        }
}
