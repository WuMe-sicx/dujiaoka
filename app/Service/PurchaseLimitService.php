<?php

namespace App\Service;

use App\Exceptions\RuleValidationException;
use App\Models\Goods;
use App\Models\Order;
use Illuminate\Http\Request;

class PurchaseLimitService
{
    /**
     * 检查购买限制
     */
    public function checkPurchaseLimit(Goods $goods, Request $request, ?int $userId = null, int $quantity = 1): void
    {
        $limits = $goods->purchase_limits ?? [];

        // 按用户ID限制（需要登录）
        if ($userId && !empty($limits['per_user'])) {
            $userOrders = Order::where('goods_id', $goods->id)
                ->where('user_id', $userId)
                ->whereNotIn('status', [Order::STATUS_EXPIRED, Order::STATUS_FAILURE])
                ->sum('buy_amount');

            if (($userOrders + $quantity) > $limits['per_user']) {
                throw new RuleValidationException(__('dujiaoka.prompt.user_purchase_limit_exceeded', [
                    'limit' => $limits['per_user'],
                    'bought' => $userOrders,
                ]));
            }
        }

        // 按IP限制（当日）
        if (!empty($limits['per_ip'])) {
            $ipOrders = Order::where('goods_id', $goods->id)
                ->where('buy_ip', $request->ip())
                ->whereNotIn('status', [Order::STATUS_EXPIRED, Order::STATUS_FAILURE])
                ->whereDate('created_at', today())
                ->sum('buy_amount');

            if (($ipOrders + $quantity) > $limits['per_ip']) {
                throw new RuleValidationException(__('dujiaoka.prompt.ip_purchase_limit_exceeded', [
                    'limit' => $limits['per_ip'],
                ]));
            }
        }

        // 按Session限制
        if (!empty($limits['per_session'])) {
            $sessionKey = 'purchase_count_' . $goods->id;
            $sessionCount = session($sessionKey, 0);

            if (($sessionCount + $quantity) > $limits['per_session']) {
                throw new RuleValidationException(__('dujiaoka.prompt.session_purchase_limit_exceeded', [
                    'limit' => $limits['per_session'],
                ]));
            }
        }
    }

    /**
     * 记录Session购买数量
     */
    public function recordSessionPurchase(int $goodsId, int $quantity): void
    {
        $sessionKey = 'purchase_count_' . $goodsId;
        $currentCount = session($sessionKey, 0);
        session([$sessionKey => $currentCount + $quantity]);
    }
}
