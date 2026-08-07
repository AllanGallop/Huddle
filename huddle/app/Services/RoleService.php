<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RoleService
{
    public static function getRoles()
    {
        return Role::query()->orderBy('name')->get();
    }

    public static function getRoleById($id)
    {
        return Role::find($id);
    }

    public static function getRoleByName($name)
    {
        return Role::where('name', $name)->first();
    }

    public static function createRole(string $name, ?string $description = null, array $permissionIds = []): Role
    {
        $role = Role::create([
            'name' => $name,
            'description' => $description,
            'is_system' => false,
        ]);

        if ($permissionIds !== []) {
            $role->permissions()->sync($permissionIds);
        }

        return $role;
    }

    public static function updateRole(int $id, string $name, ?string $description = null, ?array $permissionIds = null): void
    {
        $role = Role::query()->findOrFail($id);

        if ($role->is_system && strcasecmp($role->name, $name) !== 0) {
            throw ValidationException::withMessages([
                'name' => __('System roles cannot be renamed.'),
            ]);
        }

        $role->update([
            'name' => $name,
            'description' => $description,
        ]);

        if ($permissionIds !== null && $role->name !== 'admin') {
            $role->permissions()->sync($permissionIds);
        }
    }

    public static function deleteRole(int $id): void
    {
        $role = Role::query()->findOrFail($id);

        if ($role->is_system) {
            throw ValidationException::withMessages([
                'role' => __('System roles cannot be deleted.'),
            ]);
        }

        if ($role->users()->exists()) {
            throw ValidationException::withMessages([
                'role' => __('Remove this role from all users before deleting it.'),
            ]);
        }

        $role->permissions()->detach();
        $role->delete();
    }

    public static function assignRolesToUser(int $userId, array $roleIds): void
    {
        if (! Auth::user()?->isAdmin()) {
            throw new \Exception('User must be admin to assign roles to user');
        }

        $user = User::query()->findOrFail($userId);
        $user->roles()->sync($roleIds);
    }

    public static function userHasRole(int $roleId): bool
    {
        return Auth::user()?->roles()->where('roles.id', $roleId)->exists() ?? false;
    }
}
