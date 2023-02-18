<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('users')->insert([
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'phone_no' => +923057502419,
                'email' => 'admin@getnada.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => Carbon::now(),
                'status' => ACTIVE,
                'role_id' => ROLE_ADMIN,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Zuill',
                'phone_no' => +14415056633,
                'email' => 'info@zuill.com',
                'password' => Hash::make('!Rxty2134'),
                'email_verified_at' => Carbon::now(),
                'status' => ACTIVE,
                'role_id' => ROLE_ADMIN,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],

            [
                'first_name' => 'Editor',
                'last_name' => 'User',
                'phone_no' => +923057502410,
                'email' => 'editor@getnada.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => Carbon::now(),
                'status' => ACTIVE,
                'role_id' => ROLE_EDITOR,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'first_name' => 'Manager',
                'last_name' => 'User',
                'phone_no' => +923057502411,
                'email' => 'manager@getnada.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => Carbon::now(),
                'status' => ACTIVE,
                'role_id' => ROLE_MANAGER,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
