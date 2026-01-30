<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type', 20)->comment('类型: topup/purchase/refund/adjustment');
            $table->decimal('amount', 12, 2)->comment('变动金额');
            $table->decimal('balance_before', 12, 2)->comment('变动前余额');
            $table->decimal('balance_after', 12, 2)->comment('变动后余额');
            $table->string('order_sn', 32)->nullable()->comment('关联订单号');
            $table->string('remark')->nullable()->comment('备注');
            $table->timestamps();

            $table->index('user_id');
            $table->index('type');
            $table->index('order_sn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_logs');
    }
};
