<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tags')->truncate();
        DB::table('tags')->insert([
            [
                'title' => 'Tag 1 of Cat 1',
                'slug' => 'tag-1-of-cat-1',
                'category_id' => 1,
                'user_id' => 1,
            ],
            [
                'title' => 'Tag 2 of Cat 1',
                'slug' => 'tag-2-of-cat-1',
                'category_id' => 1,
                'user_id' => 1,
            ],
            [
                'title' => 'Tag 1 of Cat 2',
                'slug' => 'tag-1-of-cat-2',
                'category_id' => 2,
                'user_id' => 1,
            ],
            [
                'title' => 'Tag 2 of Cat 2',
                'slug' => 'tag-2-of-cat-2',
                'category_id' => 2,
                'user_id' => 1,
            ],
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
