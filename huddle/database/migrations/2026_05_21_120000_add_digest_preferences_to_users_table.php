<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('digest_opt_out')->default(false)->after('remember_token');
            $table->timestamp('last_digest_sent_at')->nullable()->after('digest_opt_out');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['digest_opt_out', 'last_digest_sent_at']);
        });
    }
};
