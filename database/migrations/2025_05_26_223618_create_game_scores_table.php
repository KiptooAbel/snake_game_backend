<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('score');
            $table->integer('level');
            $table->integer('game_duration'); // in seconds
            $table->string('difficulty')->default('normal');
            $table->json('game_stats')->nullable(); // food eaten, power-ups used, etc.
            $table->timestamps();
            
            $table->index(['user_id', 'score']);
            $table->index(['score', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
