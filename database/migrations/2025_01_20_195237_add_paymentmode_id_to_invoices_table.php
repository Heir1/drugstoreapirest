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
        Schema::table('invoices', function (Blueprint $table) {
            // Ajoute la colonne avec une valeur par défaut temporaire
            $table->foreignId('paymentmode_id')
                ->default(1) // Valeur par défaut pour les données existantes
                ->after('total_incl_tax');
        });

        // Ajoute la clé étrangère après avoir ajouté la colonne
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('paymentmode_id')->references('id')->on('payment_modes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Supprime la contrainte de clé étrangère
            $table->dropForeign(['paymentmode_id']);
            // Supprime la colonne
            $table->dropColumn('paymentmode_id');
        });
    }
};
