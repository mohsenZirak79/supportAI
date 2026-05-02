<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_callback_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('conversation_id');
            $table->uuid('trigger_message_id');
            $table->string('status', 32)->default('pending');
            $table->uuid('handled_by')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->string('admin_note', 500)->nullable();
            $table->string('source', 64)->default('floating_widget');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('conversation_id')->references('id')->on('conversations')->cascadeOnDelete();
            $table->foreign('trigger_message_id')->references('id')->on('messages')->cascadeOnDelete();
            $table->foreign('handled_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['user_id', 'trigger_message_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_callback_requests');
    }
};
