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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('leader_id')->constrained('users');
            $table->boolean('volunteer_required')->default(false);
            $table->enum('project_status', ['draft', 'outstanding', 'in-progress', 'completed', 'cancelled', 'archived']);
            $table->timestamps();
        });

        Schema::create('project_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_comment_id')->nullable()->constrained('project_comments');
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('user_id')->constrained('users');
            $table->text('comment');
            $table->timestamps();
        });

        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('user_id')->constrained('users');
            $table->text('task');
            $table->enum('task_status', ['pending', 'in-progress', 'completed', 'cancelled']);
            $table->timestamps();
        });

        Schema::create('project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects');
            $table->string('image_url');
            $table->timestamps();
        });

        Schema::create('project_volunteers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_volunteers');
        Schema::dropIfExists('project_images');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('project_comments');
        Schema::dropIfExists('projects');
    }
};
