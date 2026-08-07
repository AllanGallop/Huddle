<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
            $table->boolean('is_system')->default(false)->after('description');
        });

        DB::table('roles')->whereIn('name', ['admin', 'member'])->update(['is_system' => true]);

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
        });

        $now = now();

        $permissions = [
            ['slug' => 'projects.edit_any', 'name' => 'Edit any project', 'description' => 'Edit projects created by others'],
            ['slug' => 'projects.delete_any', 'name' => 'Delete any project', 'description' => 'Delete projects created by others'],
            ['slug' => 'events.edit_any', 'name' => 'Edit any event', 'description' => 'Edit events created by others'],
            ['slug' => 'events.delete_any', 'name' => 'Delete any event', 'description' => 'Delete events created by others'],
            ['slug' => 'wiki.edit', 'name' => 'Edit wiki', 'description' => 'Create and edit wiki pages'],
            ['slug' => 'forms.manage', 'name' => 'Manage forms', 'description' => 'Create forms and edit any form'],
            ['slug' => 'accreditations.assign_via_exam', 'name' => 'Assign exam to accreditation', 'description' => 'Link exams to accreditations and manage accreditation assignments'],
        ];

        $permissionIds = [];
        foreach ($permissions as $permission) {
            $permissionIds[$permission['slug']] = DB::table('permissions')->insertGetId([
                ...$permission,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        $memberRoleId = DB::table('roles')->where('name', 'member')->value('id');

        if (! $adminRoleId) {
            $adminRoleId = DB::table('roles')->insertGetId([
                'name' => 'admin',
                'description' => 'Full system access',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! $memberRoleId) {
            $memberRoleId = DB::table('roles')->insertGetId([
                'name' => 'member',
                'description' => 'Standard member access',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $mentorRoleId = DB::table('roles')->where('name', 'Mentor')->value('id');
        if (! $mentorRoleId) {
            $mentorRoleId = DB::table('roles')->insertGetId([
                'name' => 'Mentor',
                'description' => 'Wiki, forms, and accreditation exam linking',
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (['wiki.edit', 'forms.manage', 'accreditations.assign_via_exam'] as $slug) {
            DB::table('role_permission')->insertOrIgnore([
                'role_id' => $mentorRoleId,
                'permission_id' => $permissionIds[$slug],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (Schema::hasColumn('users', 'role_id')) {
            $users = DB::table('users')->select('id', 'role_id')->get();
            foreach ($users as $user) {
                $roleId = $user->role_id ?: $memberRoleId;
                DB::table('role_user')->insertOrIgnore([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $mentorFlagId = DB::table('user_flags')->whereRaw('LOWER(name) = ?', ['mentor'])->value('id');
        if ($mentorFlagId) {
            $mentorUserIds = DB::table('user_flag_assignments')
                ->where('user_flag_id', $mentorFlagId)
                ->pluck('user_id');

            foreach ($mentorUserIds as $userId) {
                DB::table('role_user')->insertOrIgnore([
                    'user_id' => $userId,
                    'role_id' => $mentorRoleId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->foreignId('accreditation_id')
                ->nullable()
                ->after('created_by')
                ->constrained('accreditations')
                ->nullOnDelete();
        });

        Schema::create('accreditation_mentors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accreditation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['accreditation_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accreditation_mentors');

        Schema::table('forms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('accreditation_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles');
        });

        $memberRoleId = DB::table('roles')->where('name', 'member')->value('id') ?? 2;
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id') ?? 1;

        foreach (DB::table('role_user')->get() as $row) {
            $preferred = $row->role_id === $adminRoleId ? $adminRoleId : $row->role_id;
            DB::table('users')->where('id', $row->user_id)->update([
                'role_id' => $preferred ?: $memberRoleId,
            ]);
        }

        DB::table('users')->whereNull('role_id')->update(['role_id' => $memberRoleId]);

        Schema::dropIfExists('role_user');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_system']);
        });

        DB::table('roles')->where('name', 'Mentor')->delete();
    }
};
