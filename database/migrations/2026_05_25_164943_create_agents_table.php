<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('emoji')->default('🤖');
            $table->text('persona')->nullable();
            $table->text('context')->nullable();
            $table->text('response_style')->nullable();
            $table->string('language', 10)->default('fr');
            $table->string('model')->default('openai/gpt-4o-mini');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Ajouter agent_id dans conversations
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn('agent_id');
        });
        Schema::dropIfExists('agents');
    }
};
