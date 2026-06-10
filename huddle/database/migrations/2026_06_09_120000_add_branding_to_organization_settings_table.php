<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('payment_instructions');
            $table->string('favicon_path')->nullable()->after('logo_path');
            $table->string('banner_light_path')->nullable()->after('favicon_path');
            $table->string('banner_dark_path')->nullable()->after('banner_light_path');
        });
    }

    public function down(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path',
                'favicon_path',
                'banner_light_path',
                'banner_dark_path',
            ]);
        });
    }
};
