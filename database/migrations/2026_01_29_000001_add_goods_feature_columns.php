<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->string('access_password', 100)->nullable()->after('is_open')->comment('商品访问密码');
            $table->integer('stock_alert_threshold')->default(10)->after('in_stock')->comment('库存预警阈值');
            $table->json('purchase_limits')->nullable()->after('buy_limit_num')->comment('限购规则');
        });
    }

    public function down(): void
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->dropColumn(['access_password', 'stock_alert_threshold', 'purchase_limits']);
        });
    }
};
