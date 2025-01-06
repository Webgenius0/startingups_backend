<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        SystemSetting::insert([
            [
                'id'             => 1,
                'title'          => 'Trade Support Pros',
                'email'          => 'Tradespeople@gmail.com',
                'system_name'    => 'Trade Support Pros',
                'copyright_text' => 'The contents of this website are 2024 Copyright Family Tradespeople.',
                'logo'           => 'backend/images/logo.svg',
                'favicon'        => 'backend/images/logo.svg',
                'description'    => '<p>We connect employees with employers, with no hassle</p>',
                'created_at'     => Carbon::create('2024', '06', '24', '12', '28', '31'),
                'updated_at'     => Carbon::create('2024', '06', '24', '12', '30', '55'),
                'deleted_at'     => null,
            ],
        ]);
    }
}
