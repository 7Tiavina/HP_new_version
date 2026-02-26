<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes the clients table for SQLite by recreating it with nullable password_hash
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // For SQLite, we need to recreate the table
            DB::statement('PRAGMA foreign_keys=OFF;');
            
            // Create a new table with the correct schema
            Schema::create('clients_new', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->string('password_hash')->nullable();
                $table->string('nom');
                $table->string('prenom');
                $table->string('telephone')->nullable();
                $table->string('carte_paiement_type')->nullable();
                $table->string('carte_paiement_last4')->nullable();
                $table->string('carte_paiement_nom')->nullable();
                $table->string('carte_paiement_expiry')->nullable();
                $table->string('monetico_card_token')->nullable();
                $table->string('civilite')->nullable();
                $table->string('nomSociete')->nullable();
                $table->string('adresse')->nullable();
                $table->string('complementAdresse')->nullable();
                $table->string('ville')->nullable();
                $table->string('codePostal')->nullable();
                $table->string('pays')->nullable();
                $table->timestamps();
            });
            
            // Copy existing data
            DB::statement('INSERT INTO clients_new SELECT * FROM clients;');
            
            // Drop old table
            Schema::dropIfExists('clients');
            
            // Rename new table
            DB::statement('ALTER TABLE clients_new RENAME TO clients;');
            
            DB::statement('PRAGMA foreign_keys=ON;');
        } else {
            // For MySQL/PostgreSQL
            Schema::table('clients', function (Blueprint $table) {
                $table->string('password_hash')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('password_hash')->nullable(false)->change();
            });
        }
    }
};
