<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();
            $table->string('player_name');
            $table->integer('high_score');
            $table->enum('difficulty', ['EASY', 'MEDIUM', 'HARD']);
            $table->integer('total_games_played')->default(1);
            $table->integer('total_score')->default(0);
            $table->decimal('average_score', 8, 2)->default(0);
            $table->string('device_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['device_id', 'difficulty']);
            $table->unique(['user_id', 'difficulty']);
            $table->index(['difficulty', 'high_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboards');
    }
};