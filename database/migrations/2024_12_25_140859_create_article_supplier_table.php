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
            $table->uuid('article_id');  // Clé étrangère vers la table articles
            $table->uuid('supplier_id')->nullable();  // Clé étrangère vers la table suppliers, maintenant nullable
            $table->timestamps();  // Created at et Updated at

            // Définir les clés étrangères
            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade')->nullable();

            // Assurer l'unicité de la combinaison article_id + supplier_id
            $table->primary(['article_id', 'supplier_id']);
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
