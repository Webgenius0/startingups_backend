<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('plans')->insert([
            [
                'name' => 'Annual Plan',
                'stripe_price_id' => 'price_1QXzMnB1kC1m7lm14gMl3Ge7',
                'price' => 34.44,
                'interval' => 'yearly',
                'description' => 'Get access to analytics and marketing tools for one year.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Monthly Plan',
                'stripe_price_id' => 'price_1QXzMHB1kC1m7lm1uwwt7RAz',
                'price' => 6.88,
                'interval' => 'monthly',
                'description' => 'Get access to analytics and marketing tools on a monthly basis.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
