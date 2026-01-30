<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 修复商品默认图片路径
     * 将无效的 /images/default.png 路径设为 null，让前端使用内置默认图
     */
    public function up(): void
    {
        DB::table('goods')
            ->where('picture', '/images/default.png')
            ->update(['picture' => null]);
    }

    public function down(): void
    {
        // 不需要回滚
    }
};
