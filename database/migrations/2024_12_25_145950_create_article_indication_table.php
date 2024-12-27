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
        Schema::create('article_indication', function (Blueprint $table) {
            $table->uuid('article_id');
            $table->uuid('indication_id');
            $table->timestamps();
        
            // Définir les clés étrangères
            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            $table->foreign('indication_id')->references('id')->on('indications')->onDelete('cascade');
        
            // Ajouter une contrainte d'unicité pour éviter les doublons
            $table->unique(['article_id', 'indication_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_indication');
    }
};
