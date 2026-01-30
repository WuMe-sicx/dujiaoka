<?php

namespace Database\Seeders;

use App\Models\Goods;
use Illuminate\Database\Seeder;

class GoodsSeeder extends Seeder
{
    public function run()
    {
        $goods = [
            [
                'group_id' => 1,
                'gd_name' => 'Steam 充值卡 50元',
                'gd_description' => 'Steam平台50元充值卡，自动发货',
                'gd_keywords' => 'steam,充值卡,50元',
                'picture' => null,
                'retail_price' => 55.00,
                'actual_price' => 50.00,
                'in_stock' => 0,
                'sales_volume' => 0,
                'ord' => 1,
                'buy_limit_num' => 5,
                'buy_prompt' => '请填写正确的邮箱以接收卡密信息',
                'description' => '<p>Steam平台50元充值卡，购买后自动发送卡密到邮箱。</p>',
                'type' => 1, // AUTOMATIC_DELIVERY
                'is_open' => 1,
            ],
            [
                'group_id' => 1,
                'gd_name' => 'Steam 充值卡 100元',
                'gd_description' => 'Steam平台100元充值卡，自动发货',
                'gd_keywords' => 'steam,充值卡,100元',
                'picture' => null,
                'retail_price' => 108.00,
                'actual_price' => 100.00,
                'in_stock' => 0,
                'sales_volume' => 0,
                'ord' => 2,
                'buy_limit_num' => 3,
                'buy_prompt' => '请填写正确的邮箱以接收卡密信息',
                'description' => '<p>Steam平台100元充值卡，购买后自动发送卡密到邮箱。</p>',
                'type' => 1,
                'is_open' => 1,
            ],
            [
                'group_id' => 2,
                'gd_name' => 'Windows 11 Pro 激活码',
                'gd_description' => 'Windows 11 专业版永久激活码',
                'gd_keywords' => 'windows,激活码,win11',
                'picture' => null,
                'retail_price' => 299.00,
                'actual_price' => 199.00,
                'in_stock' => 0,
                'sales_volume' => 0,
                'ord' => 1,
                'buy_limit_num' => 2,
                'buy_prompt' => '激活码将发送到您的邮箱，请注意查收',
                'description' => '<p>Windows 11 专业版正版激活码，永久有效。</p>',
                'type' => 1,
                'is_open' => 1,
            ],
            [
                'group_id' => 2,
                'gd_name' => 'Office 365 激活码',
                'gd_description' => 'Microsoft Office 365 一年订阅激活码',
                'gd_keywords' => 'office,365,激活码',
                'picture' => null,
                'retail_price' => 399.00,
                'actual_price' => 299.00,
                'in_stock' => 0,
                'sales_volume' => 0,
                'ord' => 2,
                'buy_limit_num' => 2,
                'buy_prompt' => '请填写正确邮箱',
                'description' => '<p>Office 365 一年订阅，包含Word、Excel、PPT等全套软件。</p>',
                'type' => 1,
                'is_open' => 1,
            ],
            [
                'group_id' => 3,
                'gd_name' => 'VIP会员月卡',
                'gd_description' => 'VIP会员一个月服务',
                'gd_keywords' => 'vip,会员,月卡',
                'picture' => null,
                'retail_price' => 50.00,
                'actual_price' => 30.00,
                'in_stock' => 99,
                'sales_volume' => 0,
                'ord' => 1,
                'buy_limit_num' => 1,
                'buy_prompt' => '请留下您的用户名，客服将在24小时内为您开通',
                'description' => '<p>VIP会员月卡，人工处理，24小时内开通。</p>',
                'type' => 2, // MANUAL_PROCESSING
                'is_open' => 1,
            ],
            [
                'group_id' => 1,
                'gd_name' => 'Netflix 礼品卡 25美元',
                'gd_description' => 'Netflix 25美元礼品卡',
                'gd_keywords' => 'netflix,礼品卡',
                'picture' => null,
                'retail_price' => 198.00,
                'actual_price' => 178.00,
                'in_stock' => 0,
                'sales_volume' => 0,
                'ord' => 3,
                'buy_limit_num' => 3,
                'buy_prompt' => '卡密将自动发送至邮箱',
                'description' => '<p>Netflix 25美元礼品卡，全球通用。</p>',
                'type' => 1,
                'is_open' => 1,
            ],
        ];

        foreach ($goods as $item) {
            Goods::create($item);
        }
    }
}
