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
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Si l'utilisateur est connecté
            $table->string('id_api_commande')->nullable(); // ID de la commande retourné par l'API externe
            $table->uuid('id_plateforme'); // UUID de la plateforme
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
            $table->string('statut')->default('pending'); // Statut de la commande (pending, completed, failed)
            $table->json('details_commande_lignes'); // Stocke le tableau commandeLignes en JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
