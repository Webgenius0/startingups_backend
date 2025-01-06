<?php

namespace Database\Seeders;

use App\Models\SocialMedia;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SocialMediaSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        SocialMedia::insert([
            [
                'id'           => 1,
                'social_media' => 'facebook',
                'profile_link' => 'https://www.facebook.com/',
                'created_at'   => Carbon::create('2024', '06', '24', '12', '20', '59'),
                'updated_at'   => Carbon::create('2024', '06', '24', '12', '20', '59'),
                'deleted_at'   => null,
            ],
            [
                'id'           => 2,
                'social_media' => 'twitter',
                'profile_link' => 'https://x.com/?lang=en',
                'created_at'   => Carbon::create('2024', '06', '24', '12', '20', '59'),
                'updated_at'   => Carbon::create('2024', '06', '24', '12', '20', '59'),
                'deleted_at'   => null,
            ],
            [
                'id'           => 3,
                'social_media' => 'linkedin',
                'profile_link' => 'https://bd.linkedin.com/',
                'created_at'   => Carbon::create('2024', '06', '24', '12', '20', '59'),
                'updated_at'   => Carbon::create('2024', '06', '24', '12', '20', '59'),
                'deleted_at'   => null,
            ],
            [
                'id'           => 4,
                'social_media' => 'instagram',
                'profile_link' => 'https://www.instagram.com/',
                'created_at'   => Carbon::create('2024', '06', '24', '12', '20', '59'),
                'updated_at'   => Carbon::create('2024', '06', '24', '12', '20', '59'),
                'deleted_at'   => null,
            ],
        ]);
    }
}
