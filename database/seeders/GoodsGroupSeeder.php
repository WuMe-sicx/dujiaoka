<?php

namespace Database\Seeders;

use App\Models\GoodsGroup;
use Illuminate\Database\Seeder;

class GoodsGroupSeeder extends Seeder
{
    public function run()
    {
        $groups = [
            ['gp_name' => '游戏点卡', 'is_open' => 1, 'ord' => 1],
            ['gp_name' => '软件激活码', 'is_open' => 1, 'ord' => 2],
            ['gp_name' => '会员服务', 'is_open' => 1, 'ord' => 3],
        ];

        foreach ($groups as $group) {
            GoodsGroup::create($group);
        }
    }
}
