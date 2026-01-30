<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 创建测试用户
        User::create([
            'name' => '测试用户',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'balance' => 100.00,
        ]);

        User::create([
            'name' => 'VIP用户',
            'email' => 'vip@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'balance' => 500.00,
        ]);
    }
}
