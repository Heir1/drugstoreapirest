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
        Schema::create('cash_journals', function (Blueprint $table) {
            $table->id();
            $table->enum('transaction_type', ['income', 'expense']);
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            
            // Utilisez le même type que currencies.id (UUID)
            $table->unsignedBigInteger('currency_id');
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        
            // Clé étrangère pour currency (cascade conservée)
            $table->foreign('currency_id')
                  ->references('id')
                  ->on('currencies')
                  ->onDelete('cascade');
        
            // Pour les utilisateurs, utilisez onDelete('no action')
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('no action'); // Changé à 'no action'
        
            $table->foreign('updated_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('no action'); // Changé à 'no action'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_journals');
    }
};
