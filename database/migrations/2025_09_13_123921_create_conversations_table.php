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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->bigIncrements('support_agent_id')->nullable();
            $table->enum('status', ['ai', 'human', 'closed'])->default('ai')->index();
            $table->jsonb('user_profile')->nullable();  // JSONB برای PostgreSQL – efficient
            $table->char('title',250)->nullable();  // encrypt later
            $table->timestamps();
            $table->softDeletes();  // برای recovery
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
