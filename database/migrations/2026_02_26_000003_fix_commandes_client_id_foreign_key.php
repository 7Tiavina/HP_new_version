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
     * This migration fixes the commandes table to use client_id instead of user_id
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
            
            // Drop the old foreign key constraint and rename column
            Schema::table('commandes', function (Blueprint $table) {
                // For SQLite, we need to recreate the table
            });
            
            // Get all existing data
            $existingData = DB::table('commandes')->get();
            
            // Drop old table
            Schema::dropIfExists('commandes');
            
            // Create new table with correct schema
            Schema::create('commandes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
                $table->string('id_api_commande')->nullable();
                $table->uuid('id_plateforme');
                $table->string('client_email');
                $table->string('client_telephone')->nullable();
                $table->string('client_nom');
                $table->string('client_prenom');
                $table->string('client_civilite')->nullable();
                $table->string('client_nom_societe')->nullable();
                $table->string('client_adresse')->nullable();
                $table->string('client_complement_adresse')->nullable();
                $table->string('client_ville')->nullable();
                $table->string('client_code_postal')->nullable();
                $table->string('client_pays')->nullable();
                $table->decimal('total_prix_ttc', 10, 2);
                $table->string('statut')->default('pending');
                $table->json('details_commande_lignes');
                $table->json('invoice_content')->nullable();
                $table->timestamps();
            });
            
            // Restore data (mapping user_id to client_id if it exists)
            foreach ($existingData as $row) {
                $insertData = (array) $row;
                if (isset($insertData['user_id'])) {
                    $insertData['client_id'] = $insertData['user_id'];
                    unset($insertData['user_id']);
                }
                unset($insertData['id']); // Let auto-increment handle this
                DB::table('commandes')->insert($insertData);
            }
            
            DB::statement('PRAGMA foreign_keys=ON;');
        } else {
            // For MySQL/PostgreSQL
            Schema::table('commandes', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->renameColumn('user_id', 'client_id');
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Reverse would require another table recreation
            DB::statement('PRAGMA foreign_keys=OFF;');
            Schema::dropIfExists('commandes');
            
            Schema::create('commandes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('id_api_commande')->nullable();
                $table->uuid('id_plateforme');
                $table->string('client_email');
                $table->string('client_telephone')->nullable();
                $table->string('client_nom');
                $table->string('client_prenom');
                $table->string('client_civilite')->nullable();
                $table->string('client_nom_societe')->nullable();
                $table->string('client_adresse')->nullable();
                $table->string('client_complement_adresse')->nullable();
                $table->string('client_ville')->nullable();
                $table->string('client_code_postal')->nullable();
                $table->string('client_pays')->nullable();
                $table->decimal('total_prix_ttc', 10, 2);
                $table->string('statut')->default('pending');
                $table->json('details_commande_lignes');
                $table->timestamps();
            });
            DB::statement('PRAGMA foreign_keys=ON;');
        } else {
            Schema::table('commandes', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
                $table->renameColumn('client_id', 'user_id');
                $table->foreign('user_id')->references('id')->on('clients')->onDelete('set null');
            });
        }
    }
};
