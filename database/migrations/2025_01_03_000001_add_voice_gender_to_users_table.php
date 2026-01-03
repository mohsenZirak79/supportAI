<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds voice_gender preference for TTS (Text-to-Speech)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('voice_gender', ['male', 'female'])
                ->default('female')
                ->after('address')
                ->comment('User preferred voice gender for TTS');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('voice_gender');
        });
    }
};

