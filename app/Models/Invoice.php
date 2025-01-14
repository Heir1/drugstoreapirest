<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    //

    protected $fillable = ['invoice_date', 'total_excl_tax', 'vat', 'total_incl_tax', 'invoice_number'];

    public function invoiceLines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public static function generateInvoiceNumber()
    {
        $lastInvoice = self::latest('id')->first();
        $number = $lastInvoice ? $lastInvoice->id + 1 : 1;
        return 'FACT-' . str_pad($number, 6, '0', STR_PAD_LEFT); // Format: FACT-000001
    }

}
