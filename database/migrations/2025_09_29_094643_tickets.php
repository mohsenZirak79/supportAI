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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable()->index();
            $table->uuid('root_id')->index(); // برای query سریع
            $table->string('title');
            $table->text('message');
            $table->string('sender_type'); // 'user' یا 'agent'
            $table->uuid('sender_id')->nullable();
            $table->string('department')->nullable(); // فقط برای تیکت اصلی
            $table->bigInteger('department_role_id')->nullable(); // فقط برای تیکت اصلی
            $table->string('status')->default('pending');
            $table->string('priority')->nullable(); // فقط برای تیکت اصلی
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
