<?php

namespace App\Observers;

use App\Models\Carmis;
use App\Models\Goods;
use App\Notifications\StockAlertNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CarmisObserver
{
    /**
     * 库存预警缓存时间（秒）：10分钟内不重复发送
     */
    private const ALERT_COOLDOWN_SECONDS = 600;

    /**
     * 当卡密状态更新时检查库存
     */
    public function updated(Carmis $carmis): void
    {
        // 只在状态变为已售出时检查
        if ($carmis->isDirty('status') && $carmis->status === Carmis::STATUS_SOLD) {
            $this->checkStockLevel($carmis->goods_id);
        }
    }

    /**
     * 检查商品库存是否低于阈值
     */
    protected function checkStockLevel(int $goodsId): void
    {
        $goods = Goods::find($goodsId);
        if (!$goods) {
            return;
        }

        // 只检查自动发货商品
        if ($goods->type != Goods::AUTOMATIC_DELIVERY) {
            return;
        }

        $unsoldCount = Carmis::where('goods_id', $goodsId)
            ->where('status', Carmis::STATUS_UNSOLD)
            ->count();

        $threshold = $goods->stock_alert_threshold ?? dujiaoka_config_get('default_stock_threshold', 10);

        if ($unsoldCount <= $threshold) {
            $this->sendStockAlert($goods, $unsoldCount);
        }
    }

    /**
     * 发送库存预警通知（带防抖机制）
     */
    protected function sendStockAlert(Goods $goods, int $currentStock): void
    {
        // 防抖：检查是否在冷却期内
        $cacheKey = "stock_alert_sent:{$goods->id}";
        if (Cache::has($cacheKey)) {
            Log::debug('Stock alert skipped (cooldown)', [
                'goods_id' => $goods->id,
                'goods_name' => $goods->gd_name,
            ]);
            return;
        }

        // 设置冷却缓存
        Cache::put($cacheKey, true, self::ALERT_COOLDOWN_SECONDS);

        Log::warning('Stock alert triggered', [
            'goods_id' => $goods->id,
            'goods_name' => $goods->gd_name,
            'current_stock' => $currentStock,
            'threshold' => $goods->stock_alert_threshold,
        ]);

        // 发送邮件通知
        $adminEmail = dujiaoka_config_get('manage_email');
        if ($adminEmail) {
            try {
                Notification::route('mail', $adminEmail)
                    ->notify(new StockAlertNotification($goods, $currentStock));
            } catch (\Exception $e) {
                Log::error('Failed to send stock alert email', [
                    'error' => $e->getMessage(),
                    'goods_id' => $goods->id,
                ]);
            }
        }
    }
}
