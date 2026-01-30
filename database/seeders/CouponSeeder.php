<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Goods;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run()
    {
        // 10% 折扣 - 一次性，适用全部商品
        $coupon1 = new Coupon();
        $coupon1->coupon = 'TEST10';
        $coupon1->discount = 10;
        $coupon1->is_open = 1;
        $coupon1->ret = 1;
        $coupon1->is_use = Coupon::STATUS_UNUSED;
        $coupon1->save();
        $coupon1->goods()->attach(Goods::pluck('id')->toArray());

        // 20% 折扣 - 可重复使用，适用会员服务
        $coupon2 = new Coupon();
        $coupon2->coupon = 'VIP20';
        $coupon2->discount = 20;
        $coupon2->is_open = 1;
        $coupon2->ret = 10;
        $coupon2->is_use = Coupon::STATUS_UNUSED;
        $coupon2->save();
        $coupon2->goods()->attach(Goods::where('group_id', 3)->pluck('id')->toArray());

        // 5元折扣 - 一次性，适用全部商品
        $coupon3 = new Coupon();
        $coupon3->coupon = 'NEW5';
        $coupon3->discount = 5;
        $coupon3->is_open = 1;
        $coupon3->ret = 1;
        $coupon3->is_use = Coupon::STATUS_UNUSED;
        $coupon3->save();
        $coupon3->goods()->attach(Goods::pluck('id')->toArray());

        // 15% 折扣 - 多次使用，适用游戏点卡
        $coupon4 = new Coupon();
        $coupon4->coupon = 'GAME15';
        $coupon4->discount = 15;
        $coupon4->is_open = 1;
        $coupon4->ret = 5;
        $coupon4->is_use = Coupon::STATUS_UNUSED;
        $coupon4->save();
        $coupon4->goods()->attach(Goods::where('group_id', 1)->pluck('id')->toArray());
    }
}
