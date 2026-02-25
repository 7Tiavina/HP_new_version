<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('monetico_card_token', 255)->nullable()->after('carte_paiement_expiry')->comment('Token Monetico pour réutiliser la carte sans ressaisir');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('monetico_card_token');
        });
    }
};
