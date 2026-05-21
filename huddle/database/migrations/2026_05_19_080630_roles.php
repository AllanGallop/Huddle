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
        // Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Seed default roles
        DB::table('roles')->insert([
            'name' => 'admin',
        ]);
        DB::table('roles')->insert([
            'name' => 'member',
        ]);

        // Add role_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->default(2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
        // Drop role_id from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_id');
        });
        } catch (\Exception $e) {
            // Do nothing
        }

        // Drop roles table
        try {
            Schema::dropIfExists('roles');
        } catch (\Exception $e) {
            // Do nothing
        }
    }
};
