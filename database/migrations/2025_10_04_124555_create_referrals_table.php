<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('referrals', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUIDv7 compliant via Laravel
            $table->uuid('conversation_id'); // Foreign to conversations.id
            $table->uuid('trigger_message_id'); // Foreign to messages.id (user message that triggered)
            $table->uuid('user_id'); // Foreign to users.id
            $table->string('assigned_role', 50); // e.g., 'support_agent' from Spatie roles
            $table->uuid('assigned_agent_id')->nullable(); // Nullable until assigned
            $table->text('description'); // User-provided reason
            $table->enum('status', ['pending', 'assigned', 'responded', 'closed'])->default('pending')->index();
            $table->text('agent_response')->nullable(); // Agent reply content
            $table->enum('response_visibility', ['public', 'internal'])->default('public');
            $table->timestamps();
            $table->softDeletes(); // ISO-8601 compliant deletes
//            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
//            $table->foreign('trigger_message_id')->references('id')->on('messages')->onDelete('restrict');
//            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
//            $table->foreign('org_id')->references('id')->on('organizations'); // Assume orgs table exists
            $table->index(['conversation_id', 'status']); // For agent triage
//            $table->index(['user_id', 'created_at desc']); // For user view
        });
    }

    public function down(): void {
        Schema::dropIfExists('referrals');
    }
};
