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
        // Update the default value for hearts column
        Schema::table('users', function (Blueprint $table) {
            $table->integer('hearts')->default(0)->change();
        });
        
        // Optionally: Update existing users who still have the old default of 5
        // Uncomment if you want to reset all existing users to 0 hearts
        // DB::table('users')->where('hearts', 5)->update(['hearts' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('hearts')->default(5)->change();
        });
    }
};
