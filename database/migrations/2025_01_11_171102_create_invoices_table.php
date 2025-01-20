<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->date('invoice_date')->default(DB::raw('CURRENT_DATE'));
            $table->string('invoice_number')->unique()->after('id');
            $table->string('client_name')->nullable();
            $table->decimal('total_excl_tax', 10, 2)->default(0);
            $table->decimal('vat', 10, 2)->default(0.16);
            $table->decimal('total_incl_tax', 10, 2)->default(0);
            // Définition de la clé étrangère
            // $table->foreignId('paymentmode_id')->constrained('payment_modes')->onDelete('cascade');
            // $table->foreignId('paymentmode_id')->default(1) ;// Valeur par défaut pour les données existantes;
            
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
