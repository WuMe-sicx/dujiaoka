<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topup_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_sn', 32)->unique()->comment('充值订单号');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->decimal('amount', 12, 2)->comment('充值金额');
            $table->unsignedBigInteger('pay_id')->comment('支付方式');
            $table->tinyInteger('status')->default(1)->comment('状态: 1待支付 2已完成 -1已取消');
            $table->string('trade_no', 64)->nullable()->comment('第三方交易号');
            $table->string('buy_ip', 45)->nullable()->comment('IP地址');
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topup_orders');
    }
};
