<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id(); // ou $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ✅ suffit
            $table->string('ref', 20)->unique();
            $table->string('departure', 10);
            $table->string('arrival', 10);
            $table->date('collect_date');
            $table->date('deliver_date');
            $table->string('status', 50);
            $table->timestamps();

            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
