<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_renewal_assignments', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'membership_renewal_id'],
                'mra_user_renewal_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('membership_renewal_assignments', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'membership_renewal_id']);
        });
    }
};
