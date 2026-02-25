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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('carte_paiement_last4')->nullable()->after('pays');
            $table->string('carte_paiement_type')->nullable()->after('carte_paiement_last4'); // Visa, Mastercard, etc.
            $table->string('carte_paiement_nom')->nullable()->after('carte_paiement_type'); // Nom sur la carte
            $table->string('carte_paiement_expiry')->nullable()->after('carte_paiement_nom'); // MM/YY
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'carte_paiement_last4',
                'carte_paiement_type',
                'carte_paiement_nom',
                'carte_paiement_expiry',
            ]);
        });
    }
};
