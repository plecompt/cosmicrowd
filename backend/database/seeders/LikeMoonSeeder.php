<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LikeMoonSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('like_moon')->insert([
            [
                'user_id' => 1,
                'moon_id' => 1,
                'like_moon_date' => now(),
            ],
            [
                'user_id' => 2,
                'moon_id' => 1,
                'like_moon_date' => now(),
            ],
        ]);
    }
}
