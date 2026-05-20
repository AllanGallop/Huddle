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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->text('location');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->foreignId('created_by')->constrained('users');
            $table->enum('event_type', ['public', 'private']);
            $table->enum('event_status', ['draft', 'published', 'cancelled', 'archived']);
            $table->boolean('volunteer_required')->default(false);
        });

        Schema::create('event_volunteers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('event_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_comment_id')->nullable()->constrained('event_comments');
            $table->foreignId('event_id')->constrained('events');
            $table->foreignId('user_id')->constrained('users');
            $table->text('comment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_comments');
        Schema::dropIfExists('event_volunteers');
        Schema::dropIfExists('events');
    }
};
