<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    protected $fillable = ['invoice_id', 'article_id', 'quantity', 'unit_price', 'subtotal'];

    public function articles()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function invoices()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

}