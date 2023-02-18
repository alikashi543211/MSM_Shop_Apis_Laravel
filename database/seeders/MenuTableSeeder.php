<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('menus')->truncate();
        DB::table('menus')->insert([
            [
                'title' => 'Home-A',
                'slug' => 'home-jsT',
                'image' => 'uploads/menus/1648105582-5V22-menu-image.jpg',
                'user_id' => 1,
                'sort_number' => 1,
                'text_color' => 'Dark',
                'image_style' => 'Fill',
            ],
            [
                'title' => 'Home-B',
                'slug' => 'home-jsT',
                'image' => 'uploads/menus/1648105920-gNy9-menu-image.jpg',
                'user_id' => 1,
                'sort_number' => 2,
                'text_color' => 'Light',
                'image_style' => 'Fill',
            ],
            [
                'title' => 'Home-C',
                'slug' => 'home-jsT',
                'image' => 'uploads/menus/1648105687-bSFw-menu-image.jpg',
                'user_id' => 1,
                'sort_number' => 3,
                'text_color' => 'Dark',
                'image_style' => 'Fit',
            ],
            [
                'title' => 'Home-D',
                'slug' => 'home-jsT',
                'image' => 'uploads/menus/1648105710-VNik-menu-image.jpg',
                'user_id' => 1,
                'sort_number' => 4,
                'text_color' => 'Light',
                'image_style' => 'Fill',
            ],
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
