<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        //
        $permissions = array(
            // permissions
            array('module' => 'user', 'name' => 'User', 'code' => 'user'),
            array('module' => 'product', 'name' => 'Product', 'code' => 'product'),
            array('module' => 'menu', 'name' => 'Menu', 'code' => 'menu'),
            array('module' => 'category', 'name' => 'Category', 'code' => 'category'),
            array('module' => 'setting', 'name' => 'Setting', 'code' => 'setting'),
            array('module' => 'cart', 'name' => 'Cart', 'code' => 'cart'),

        );
        Permission::insert($permissions);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
