<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    

    public function run(): void
    {
        $categories = [
            ['name' => 'Fitness & Wellness'],
            ['name' => 'Visual Arts & Creative Creations'],
            ['name' => 'Performing Arts'],
            ['name' => 'Martial Arts & Self-Defense'],
            ['name' => 'Outdoor & Adventure'],
            ['name' => 'Animal-Based Activities'],
            ['name' => 'Cooking & Culinary'],
            ['name' => 'Mindfulness & Meditation'],
            ['name' => 'Unique Niche'],
            ['name' => 'Team Sports & Recreation'],
        ];

        foreach ($categories as &$category) {
            $category['created_at'] = now();
            $category['updated_at'] = now();
        }

        DB::table('categories')->insert($categories);
    }
}
