<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('wins')->update([
            'created_at' => DB::raw('logged_at'),
            'updated_at' => DB::raw('logged_at'),
        ]);

        Schema::table('wins', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'local_date']);
            $table->dropIndex(['user_id', 'logged_at']);
            $table->dropColumn(['local_date', 'logged_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wins', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->date('local_date')->nullable();
            $table->timestamp('logged_at')->nullable();
            $table->index(['user_id', 'local_date']);
            $table->index(['user_id', 'logged_at']);
        });
    }
};
