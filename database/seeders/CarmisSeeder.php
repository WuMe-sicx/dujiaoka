<?php

namespace Database\Seeders;

use App\Models\Carmis;
use App\Models\Goods;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CarmisSeeder extends Seeder
{
    public function run()
    {
        // 为所有自动发货商品创建卡密
        $autoGoods = Goods::where('type', 1)->get();

        foreach ($autoGoods as $goods) {
            for ($i = 1; $i <= 15; $i++) {
                $carmi = new Carmis();
                $carmi->goods_id = $goods->id;
                $carmi->is_loop = 0;
                $carmi->carmi = strtoupper('CARD-' . Str::random(8) . '-' . str_pad($i, 3, '0', STR_PAD_LEFT));
                $carmi->status = Carmis::STATUS_UNSOLD;
                $carmi->save();
            }
        }
    }
}
