<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wiki_directories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('wiki_directories')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['parent_id', 'slug']);
        });

        Schema::create('wiki_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wiki_directory_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['wiki_directory_id', 'slug']);
        });

        Schema::create('wiki_page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wiki_page_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('title');
            $table->longText('body');
            $table->string('change_summary')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['wiki_page_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wiki_page_versions');
        Schema::dropIfExists('wiki_pages');
        Schema::dropIfExists('wiki_directories');
    }
};
