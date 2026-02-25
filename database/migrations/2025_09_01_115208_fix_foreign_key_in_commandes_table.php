<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite can't drop foreign keys by constraint name with Schema builder.
        // This migration is only relevant for MySQL/MariaDB where the legacy FK exists.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('commandes', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte incorrecte
            // Le nom par défaut est `table_column_foreign`, donc `commandes_user_id_foreign`
            $table->dropForeign('commandes_user_id_foreign');

            // Ajouter la nouvelle contrainte correcte
            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->onDelete('cascade'); // ou set null selon la logique métier
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('commandes', function (Blueprint $table) {
            // Annuler les changements : supprimer la nouvelle contrainte et remettre l'ancienne
            $table->dropForeign(['client_id']);

            $table->foreign('client_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};