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
        Schema::create('articles', function (Blueprint $table) {

            $table->id();
            $table->string('barcode')->unique();
            $table->text('description')->unique();
            $table->boolean('alert')->default(false);
            $table->date('expiration_date');
            $table->integer('quantity')->default(0);
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->text('comment')->nullable();
            $table->uuid('row_id')->unique();
            $table->timestamps();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

            // Définition de la clé étrangère
            $table->uuid('currency_id'); // Add the currency_id column
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null'); // Set up foreign key constraint
            $table->uuid('category_id')->nullable();  // Ajouter la colonne pour la clé étrangère
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');  // Définir la contrainte de clé étrangère
            $table->uuid('packaging_id')->nullable();  // Ajouter la colonne pour la clé étrangère
            $table->foreign('packaging_id')->references('id')->on('packagings')->onDelete('set null');  // Définir la contrainte de clé étrangère

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
