<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('civilite', 10)->nullable()->after('id');
            $table->string('nomSociete')->nullable()->after('prenom');
            $table->string('adresse')->nullable()->after('nomSociete');
            $table->string('complementAdresse')->nullable()->after('adresse');
            $table->string('ville', 150)->nullable()->after('complementAdresse');
            $table->string('codePostal', 20)->nullable()->after('ville');
            $table->string('pays', 100)->nullable()->after('codePostal');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'civilite',
                'nomSociete',
                'adresse',
                'complementAdresse',
                'ville',
                'codePostal',
                'pays',
            ]);
        });
    }

};
