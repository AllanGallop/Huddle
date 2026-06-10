<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_images', function (Blueprint $table) {
            $table->string('disk', 32)->default('local')->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('project_images', function (Blueprint $table) {
            $table->dropColumn('disk');
        });
    }
};
