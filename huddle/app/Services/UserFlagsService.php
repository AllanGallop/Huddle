<?php

namespace App\Services;

use App\Models\UserFlags;
use App\Services\RoleService;

class UserFlagsService {
    public function getUserFlags() {
        return UserFlags::all();
    }

    public function getUserFlagById($id) {
        return UserFlags::find($id);
    }

    public function createUserFlag($name, $description) {
        if (RoleService::userHasRole(1)) {
            throw new \Exception('User must be admin to create user flags');
        }
        return UserFlags::create(['name' => $name, 'description' => $description]);
    }

    public function updateUserFlag($id, $name, $description) {
        if (RoleService::userHasRole(1)) {
            throw new \Exception('User must be admin to update user flags');
        }
        return UserFlags::where('id', $id)->update(['name' => $name, 'description' => $description]);
    }

    public function deleteUserFlag($id) {
        if (RoleService::userHasRole(1)) {
            throw new \Exception('User must be admin to delete user flags');
        }
        return UserFlags::where('id', $id)->delete();
    }

    public function assignUserFlagToUser($userId, $flagId) {
        if (RoleService::userHasRole(1)) {
            throw new \Exception('User must be admin to assign user flags to users');
        }
        return UserFlagAssignments::create(['user_id' => $userId, 'flag_id' => $flagId]);
    }

    public function removeUserFlagFromUser($userId, $flagId) {
        if (RoleService::userHasRole(1)) {
            throw new \Exception('User must be admin to remove user flags from users');
        }
        return UserFlagAssignments::where('user_id', $userId)->where('flag_id', $flagId)->delete();
    }
}