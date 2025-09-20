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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->uuid('sender_id');
            $table->enum('type', ['text', 'image', 'file'])->default('text');
            $table->text('content')->nullable();  // encrypt later
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->index(['conversation_id', 'created_at']);  // برای sorting
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
