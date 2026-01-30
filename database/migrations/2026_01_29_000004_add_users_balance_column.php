<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 如果 users 表不存在，先创建
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->decimal('balance', 12, 2)->default(0)->comment('账户余额');
                $table->rememberToken();
                $table->timestamps();
            });
        } else {
            // 表已存在，只添加新字段
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'balance')) {
                    $table->decimal('balance', 12, 2)->default(0)->after('password')->comment('账户余额');
                }
                if (!Schema::hasColumn('users', 'email_verified_at')) {
                    $table->timestamp('email_verified_at')->nullable()->after('email');
                }
                if (!Schema::hasColumn('users', 'remember_token')) {
                    $table->rememberToken();
                }
            });
        }
    }

    public function down(): void
    {
        // 只删除我们添加的字段，不删除整个表
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['balance']);
            });
        }
    }
};
