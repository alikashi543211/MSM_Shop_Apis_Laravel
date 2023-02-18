<?php

namespace Database\Seeders;

use App\Models\RolePermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_permissions')->truncate();

        // Modules Permissions
        // 1 => User
        // 2 => Product
        // 3 => Menu
        // 4 => Category
        // 5 => Setting
        // 6 => Cart
        $permissions = [
            ROLE_ADMIN => [1, 2, 3, 4, 5, 6],
            ROLE_EDITOR => [2],
            ROLE_MANAGER => [3, 4],
        ];
        foreach ($permissions as $role => $permission) {
            $rolePermissions = [];
            foreach ($permission as $key => $value) {
                $rolePermissions[] = [
                    "permission_id" => $value,
                    "role_id" => $role,
                    "action" => ROLE_ACTION_READ
                ];
                $rolePermissions[] = [
                    "permission_id" => $value,
                    "role_id" => $role,
                    "action" => ROLE_ACTION_WRITE
                ];
            }
            RolePermission::insert($rolePermissions);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    }
}
