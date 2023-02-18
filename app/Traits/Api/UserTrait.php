<?php
namespace App\Traits\Api;


trait UserTrait
{

    private function getFormattedPermissions($role)
    {
        $permissions = [];
        $per = $this->permission->newQuery()->get();
        foreach ($per as $key => $permission) {
            $permissions[$key]['id'] = $permission->id;
            $permissions[$key]['title'] = $permission->name;
            $permissions[$key]['read'] = $this->rolePermission->newQuery()->where('role_id', $role->id)->where('permission_id', $permission->id)->where('action', ROLE_ACTION_READ)->exists();
            $permissions[$key]['write'] = $this->rolePermission->newQuery()->where('role_id', $role->id)->where('permission_id', $permission->id)->where('action', ROLE_ACTION_WRITE)->exists();
        }
        return $permissions;
    }

    private function getUserDetail($id)
    {
        $user = $this->user->newQuery()->whereId($id)->with(['role'])->first();
        $user->permissions = $this->getFormattedPermissions($user->role);
        return $user;
    }

    private function updateRolePermissions($inputs, $roleId)
    {
        $this->rolePermission->newQuery()->where('role_id', $roleId)->delete();
        foreach ($inputs['permissions'] as $id => $permission) {
            foreach ($permission['action'] as $action => $value) {
                if ($value) {
                    $rolePermission = $this->rolePermission->newInstance();
                    $rolePermission->permission_id = $permission['id'];
                    $rolePermission->role_id = $roleId;
                    $rolePermission->action = $action;
                    if (!$rolePermission->save()) {
                        return false;
                    }
                }
            }
        }
        return true;
    }


}
