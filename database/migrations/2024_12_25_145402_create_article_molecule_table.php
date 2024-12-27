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
        Schema::create('article_molecule', function (Blueprint $table) {
            $table->uuid('article_id');
            $table->uuid('molecule_id');
            $table->timestamps();
        
            // Définir les clés étrangères
            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            $table->foreign('molecule_id')->references('id')->on('molecules')->onDelete('cascade');
        
            // Ajouter une contrainte d'unicité pour éviter les doublons
            $table->unique(['article_id', 'molecule_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_molecule');
    }
};
