<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexes extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('order_sn', 'idx_orders_order_sn');
            $table->index('email', 'idx_orders_email');
            $table->index('status', 'idx_orders_status');
        });

        Schema::table('carmis', function (Blueprint $table) {
            $table->index(['goods_id', 'status'], 'idx_carmis_goods_status');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_order_sn');
            $table->dropIndex('idx_orders_email');
            $table->dropIndex('idx_orders_status');
        });

        Schema::table('carmis', function (Blueprint $table) {
            $table->dropIndex('idx_carmis_goods_status');
        });
    }
}
