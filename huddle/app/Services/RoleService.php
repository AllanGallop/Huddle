<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RoleService
{
    public static function getRoles()
    {
        return Role::all();
    }

    public static function getRoleById($id)
    {
        return Role::find($id);
    }

    public static function getRoleByName($name)
    {
        return Role::where('name', $name)->first();
    }

    public static function createRole($name)
    {
        return Role::create(['name' => $name]);
    }

    public static function updateRole($id, $name)
    {
        return Role::where('id', $id)->update(['name' => $name]);
    }

    public static function deleteRole($id)
    {
        $memberRoleId = Role::query()->where('name', 'member')->value('id');

        if ($memberRoleId) {
            User::where('role_id', $id)->update(['role_id' => $memberRoleId]);
        }

        Role::where('id', $id)->delete();
    }

    public static function assignRoleToUser($userId, $roleId)
    {
        if (! Auth::user()?->isAdmin()) {
            throw new \Exception('User must be admin to assign role to user');
        }

        User::where('id', $userId)->update(['role_id' => $roleId]);
    }

    public static function removeRoleFromUser($userId)
    {
        if (! Auth::user()?->isAdmin()) {
            throw new \Exception('User must be admin to remove role from user');
        }

        $memberRoleId = Role::query()->where('name', 'member')->value('id');

        if ($memberRoleId) {
            User::where('id', $userId)->update(['role_id' => $memberRoleId]);
        }
    }

    public static function userHasRole($roleId)
    {
        return Auth::user()?->role_id === $roleId;
    }
}
