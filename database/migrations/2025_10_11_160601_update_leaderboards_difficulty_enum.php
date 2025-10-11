<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any existing 'MEDIUM' records to 'NORMAL'
        DB::table('leaderboards')
            ->where('difficulty', 'MEDIUM')
            ->update(['difficulty' => 'NORMAL']);

        // Update the enum to include NORMAL instead of MEDIUM
        Schema::table('leaderboards', function (Blueprint $table) {
            $table->enum('difficulty', ['EASY', 'NORMAL', 'HARD'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert NORMAL back to MEDIUM
        DB::table('leaderboards')
            ->where('difficulty', 'NORMAL')
            ->update(['difficulty' => 'MEDIUM']);

        // Revert the enum back to the original
        Schema::table('leaderboards', function (Blueprint $table) {
            $table->enum('difficulty', ['EASY', 'MEDIUM', 'HARD'])->change();
        });
    }
};
