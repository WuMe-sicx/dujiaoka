<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    protected $table = 'transaction_logs';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'order_sn',
        'remark',
    ];

    const TYPE_TOPUP = 'topup';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_REFUND = 'refund';
    const TYPE_ADJUSTMENT = 'adjustment';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function getTypeMap(): array
    {
        return [
            self::TYPE_TOPUP => '充值',
            self::TYPE_PURCHASE => '消费',
            self::TYPE_REFUND => '退款',
            self::TYPE_ADJUSTMENT => '调整',
        ];
    }
}
