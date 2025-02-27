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
            $table->enum('transaction_type', ['income', 'expense']); // Type de transaction (recette ou dépense)
            $table->decimal('amount', 10, 2); // Montant de la transaction
            $table->text('description')->nullable(); // Description facultative
            $table->string('currency_id'); // Clé étrangère vers la table des devises
            $table->unsignedBigInteger('created_by')->nullable(); // ID de l'utilisateur qui a créé l'entrée (nullable)
            $table->unsignedBigInteger('updated_by')->nullable(); // ID de l'utilisateur qui a mis à jour l'entrée (nullable)
            $table->timestamps(); // created_at et updated_at

            // Clés étrangères
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
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
