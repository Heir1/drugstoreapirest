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
        Schema::table('cash_journals', function (Blueprint $table) {
            $table->unsignedBigInteger('ticket_counter')
                  ->nullable()
                  ->after('updated_by'); // Optional: place the column after updated_by

            $table->foreign('ticket_counter')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_journals', function (Blueprint $table) {
            // First drop the foreign key constraint
            $table->dropForeign(['ticket_counter']);
            
            // Then drop the column
            $table->dropColumn('ticket_counter');
        });
    }
};