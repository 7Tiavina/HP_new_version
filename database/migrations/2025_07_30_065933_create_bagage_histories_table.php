<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bagage_histories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
        $table->enum('status', ['en_attente', 'collecté', 'stocké', 'livré']);
        $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null');
        $table->timestamp('timestamp')->nullable();
        $table->string('photo_url')->nullable();
        $table->timestamps();
    });

    }

    public function down(): void {
        Schema::dropIfExists('bagage_histories');
    }
};

