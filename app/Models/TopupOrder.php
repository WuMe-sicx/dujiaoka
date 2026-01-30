<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TopupOrder extends Model
{
    use SoftDeletes;

    protected $table = 'topup_orders';

    protected $fillable = [
        'order_sn',
        'user_id',
        'amount',
        'pay_id',
        'status',
        'trade_no',
        'buy_ip',
    ];

    const STATUS_WAIT_PAY = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_CANCELLED = -1;

    public static function getStatusMap(): array
    {
        return [
            self::STATUS_WAIT_PAY => '待支付',
            self::STATUS_COMPLETED => '已完成',
            self::STATUS_CANCELLED => '已取消',
        ];
    }

    /**
     * 生成充值订单号
     */
    public static function generateOrderSN(): string
    {
        return 'TOPUP_' . date('YmdHis') . mt_rand(1000, 9999);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pay()
    {
        return $this->belongsTo(Pay::class, 'pay_id');
    }
}
