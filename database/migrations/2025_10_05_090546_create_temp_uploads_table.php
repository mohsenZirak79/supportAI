<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('temp_uploads', function (Blueprint $table) {
            $table->uuid('id')->primary();   // ← UUID
            $table->uuid('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('temp_uploads');
    }
};
