<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::table('categories')->insert([
            [
                'title' => 'Test Category 1',
                'slug' => 'test-category-1',
                'user_id' => 1,
                'menu_id' => 1,
                'sort_number' => 1,
            ],
            [
                'title' => 'Test Category 2',
                'slug' => 'test-category-2',
                'user_id' => 1,
                'menu_id' => 1,
                'sort_number' => 2,
            ],
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
