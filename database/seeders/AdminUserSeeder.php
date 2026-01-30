<?php

namespace Database\Seeders;

use App\Models\FilamentAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        FilamentAdmin::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin'),
            ]
        );
    }
}
