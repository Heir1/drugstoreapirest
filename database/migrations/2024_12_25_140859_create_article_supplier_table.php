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
        Schema::create('article_supplier', function (Blueprint $table) {
            // Supprimez cette ligne car vous utilisez une clé primaire composite
            // $table->uuid('id')->primary()->default(DB::raw('NEWID()'));
            
            // Ajoutez d'abord les colonnes avant de créer les contraintes
            $table->unsignedBigInteger('article_id'); // Colonne article_id manquante
            $table->unsignedBigInteger('supplier_id');
            $table->timestamps();
        
            // Définir les clés étrangères
            $table->foreign('article_id')
                  ->references('id')
                  ->on('articles')
                  ->onDelete('cascade');
                  
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->onDelete('cascade');
        
            // Clé primaire composite (pas besoin de ->primary() si vous utilisez uuid() comme ci-dessus)
            $table->unique(['article_id', 'supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_supplier');
    }
};
