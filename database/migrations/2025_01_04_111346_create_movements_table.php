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
            $table->foreignId('article_id')->constrained('articles')->onDelete('cascade');
            $table->integer('old_article_stock');
            $table->integer('quantity');
            $table->foreignId('movement_type_id')->constrained('movement_types')->onDelete('cascade'); // Foreign key to movement_types
            $table->date('movement_date')->default(DB::raw('CURRENT_DATE'));
            $table->string('reference', 100)->nullable();
            $table->timestamps();
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
