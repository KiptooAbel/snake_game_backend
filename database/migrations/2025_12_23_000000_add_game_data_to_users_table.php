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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('gems')->default(0)->after('total_score');
            $table->integer('hearts')->default(0)->after('gems');
            $table->json('unlocked_levels')->nullable()->after('hearts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gems', 'hearts', 'unlocked_levels']);
        });
    }
};
