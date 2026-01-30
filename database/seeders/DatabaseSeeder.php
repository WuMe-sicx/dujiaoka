<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AdminUserSeeder::class,
            GoodsGroupSeeder::class,
            GoodsSeeder::class,
            CarmisSeeder::class,
            CouponSeeder::class,
            SystemSettingSeeder::class,
            OrderTableSeeder::class,
            UserSeeder::class,
        ]);
    }
}
