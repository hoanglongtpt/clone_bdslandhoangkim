<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@hoangkimland.local'],
            ['name' => 'QUẢN TRỊ VIÊN', 'password' => 'Admin@123456', 'role' => 'admin', 'is_active' => true]
        );
    }
}
