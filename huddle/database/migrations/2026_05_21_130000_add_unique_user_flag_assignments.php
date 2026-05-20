<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_flag_assignments', function (Blueprint $table) {
            $table->unique(['user_id', 'user_flag_id']);
        });
    }

    public function down(): void
    {
        Schema::table('user_flag_assignments', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'user_flag_id']);
        });
    }
};
