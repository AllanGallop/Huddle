<?php

use App\Models\UserFlags;
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
        Schema::create('accreditations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('accreditation_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('accreditation_id')->constrained('accreditations');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        UserFlags::firstOrCreate(
            ['name' => 'Mentor'],
            ['description' => 'Accreditation Mentor'],
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accreditation_assignments');
        Schema::dropIfExists('accreditations');
    }
};
