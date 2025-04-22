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
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->integer('old_article_stock');
            $table->integer('quantity');
            $table->foreignId('movement_type_id')->constrained('movement_types')->onDelete('cascade'); // Foreign key to movement_types
            $table->date('movement_date')->useCurrent();
            $table->string('reference', 100)->nullable();
            $table->timestamps();
        
            // Ajoute manuellement la contrainte étrangère
            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
