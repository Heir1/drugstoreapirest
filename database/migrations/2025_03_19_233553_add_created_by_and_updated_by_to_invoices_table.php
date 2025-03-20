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
            //
            $table->string('created_by')->nullable(); // ID de l'utilisateur qui a créé l'enregistrement
            $table->string('updated_by')->nullable(); // ID de l'utilisateur qui a mis à jour l'enregistrement
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Supprimer les colonnes
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
    }
};
