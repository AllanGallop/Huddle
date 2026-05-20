<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;

class RoleService {

    static function getRoles() {
        return Role::all();
    }

    static function getRoleById($id) {
        return Role::find($id);
    }

    static function getRoleByName($name) {
        return Role::where('name', $name)->first();
    }

    static function createRole($name) {
        return Role::create(['name' => $name]);
    }

    static function updateRole($id, $name) {
        return Role::where('id', $id)->update(['name' => $name]);
    }

    static function deleteRole($id) {
        // Reset all users to member role
        User::where('role_id', $id)->update(['role_id' => 2]);
        // Delete role
        Role::where('id', $id)->delete();
    }

    static function assignRoleToUser($userId, $roleId) {
        // User must be admin to assign role to user
        if (Auth::user()->role_id !== 1) {
            throw new \Exception('User must be admin to assign role to user');
        }
        // Assign role to user
        User::where('id', $userId)->update(['role_id' => $roleId]);
    }

    static function removeRoleFromUser($userId) {
        // User must be admin to remove role from user
        if (Auth::user()->role_id !== 1) {
            throw new \Exception('User must be admin to remove role from user');
        }
        // Reset user to member role
        User::where('id', $userId)->update(['role_id' => 2]);
    }

    static function userHasRole($roleId) {
        $currentUserRole = Auth::user()->role_id;
        return $currentUserRole === $roleId;
    }
}