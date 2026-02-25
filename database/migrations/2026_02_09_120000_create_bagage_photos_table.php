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
        Schema::create('bagage_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->onDelete('cascade');
            $table->enum('type', ['depot', 'restitution'])->comment('Type de photo: dépôt ou restitution');
            $table->string('photo_path')->comment('Chemin vers la photo stockée');
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null')->comment('Agent qui a pris la photo');
            $table->text('notes')->nullable()->comment('Notes optionnelles de l\'agent');
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['commande_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bagage_photos');
    }
};
