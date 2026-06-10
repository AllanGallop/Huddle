<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type');
            $table->boolean('is_published')->default(false);
            $table->unsignedTinyInteger('pass_percentage')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('form_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('type');
            $table->text('body');
            $table->unsignedSmallInteger('points')->default(0);
            $table->boolean('correct_yes_no')->nullable();
            $table->timestamps();
        });

        Schema::create('form_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_question_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('label');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('submitted_at');
            $table->unsignedSmallInteger('score')->nullable();
            $table->unsignedSmallInteger('max_score')->nullable();
            $table->boolean('passed')->nullable();
            $table->timestamps();

            $table->unique(['form_id', 'user_id']);
        });

        Schema::create('form_submission_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_question_id')->constrained()->cascadeOnDelete();
            $table->json('value');
            $table->timestamps();

            $table->unique(
                ['form_submission_id', 'form_question_id'],
                'fsa_submission_question_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submission_answers');
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_question_options');
        Schema::dropIfExists('form_questions');
        Schema::dropIfExists('forms');
    }
};
