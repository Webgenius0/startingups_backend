<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('business_categories')->insert([
            [
                'name' => 'Explore',
                'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQznftc7j83m96-SmfTDe3gqO_EfXPldtRhyg&s',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tailored',
                'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQznftc7j83m96-SmfTDe3gqO_EfXPldtRhyg&s',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Randomize',
                'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQznftc7j83m96-SmfTDe3gqO_EfXPldtRhyg&s',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
