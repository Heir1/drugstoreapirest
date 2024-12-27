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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // Nom du fournisseur
            $table->uuid('row_id')->unique();  // Générer automatiquement un UUID avec la fonction PostgreSQL uuid_generate_v4()
            $table->timestamps();  // Colonnes created_at et updated_at
            $table->string('created_by')->nullable();  // Identifiant de l'utilisateur créant l'enregistrement
            $table->string('updated_by')->nullable();  // Identifiant de l'utilisateur mettant à jour l'enregistrement
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
