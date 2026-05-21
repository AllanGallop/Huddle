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
        Schema::create('membership_renewals', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->date('start_date');
            $table->date('end_date');
        });

        Schema::create('membership_renewal_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('membership_renewal_id')->constrained('membership_renewals');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_renewal_assignments');
        Schema::dropIfExists('membership_renewals');
    }
};
