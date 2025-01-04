<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'quantity',
        'movement_type_id',
        'movement_date',
        'reference',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function movementType()
    {
        return $this->belongsTo(MovementType::class, 'movement_type_id');
    }
}
