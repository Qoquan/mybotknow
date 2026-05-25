<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_model_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('model');
            $table->integer('total_messages')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->date('usage_date');
            $table->timestamps();

            $table->unique(['user_id', 'model', 'usage_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_model_usage');
    }
};
