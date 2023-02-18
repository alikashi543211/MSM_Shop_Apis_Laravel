<?php
namespace App\Traits\Api;


trait RoleTrait
{

    private function updateRolesWithPermissions($inputs)
    {

        foreach($inputs['roles'] as $roleKey => $role)
        {
            $actions = [];
            $this->rolePermission->newQuery()->where('role_id', $role['id'])->delete();
            foreach($role['permissions'] as $permissionKey => $permission)
            {

                $actions['1'] = $permission['read'];
                $actions['2'] = $permission['write'];
                foreach($actions as $key => $value)
                {
                    if($value)
                    {
                        $rolePermission = $this->rolePermission->newInstance();
                        $rolePermission->permission_id = $permission['id'];
                        $rolePermission->role_id = $role['id'];
                        $rolePermission->action = $key;
                        if (!$rolePermission->save()) {
                            return false;
                        }
                    }

                }
            }
        }
        return true;
    }


}
