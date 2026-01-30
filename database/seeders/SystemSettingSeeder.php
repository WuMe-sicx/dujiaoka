<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            'title' => '独角数卡测试商城',
            'text_logo' => '独角数卡',
            'keywords' => '数卡,自动发卡,虚拟商品',
            'description' => '独角数卡 - 自动化虚拟商品销售平台测试环境',
            'notice' => '欢迎来到测试商城！这是一个测试环境。',
            'footer' => 'Copyright © 2026 独角数卡测试站',
            'order_expire_time' => '30',
            'is_open_search_pwd' => '1',
            'is_captcha' => '0',
            'is_open_anti_red' => '0',
            'is_open_img_code' => '0',
        ];

        foreach ($settings as $key => $value) {
            DB::table('admin_settings')->updateOrInsert(
                ['slug' => $key],
                ['slug' => $key, 'value' => json_encode($value)]
            );
        }
    }
}
