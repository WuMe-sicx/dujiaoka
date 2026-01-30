<?php

namespace App\Notifications;

use App\Models\Goods;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockAlertNotification extends Notification
{
    use Queueable;

    protected Goods $goods;
    protected int $currentStock;

    public function __construct(Goods $goods, int $currentStock)
    {
        $this->goods = $goods;
        $this->currentStock = $currentStock;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $siteName = dujiaoka_config_get('title', 'DuJiaoKa');

        return (new MailMessage)
            ->subject("[{$siteName}] 库存预警 - {$this->goods->gd_name}")
            ->greeting('库存预警通知')
            ->line("商品 **{$this->goods->gd_name}** 库存不足！")
            ->line("当前库存: **{$this->currentStock}**")
            ->line("预警阈值: **{$this->goods->stock_alert_threshold}**")
            ->action('前往后台管理', url('/admin/goods/' . $this->goods->id . '/edit'))
            ->line('请及时补充库存。');
    }
}
