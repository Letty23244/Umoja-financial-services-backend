<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name'              => 'Umoja Admin',
            'email'             => 'akulluleticia23@gmail.com',
            'phone'             => '0765451895',
            'password'          => Hash::make('password123'),
            'role'              => 'admin',
            'email_verified_at' => now(), // pre-verified
        ]);

        // Regular user
        User::create([
            'name'              => 'Leticia Nakamya',
            'email'             => 'travisakullu@gmail.com',
            'phone'             => '0700000002',
            'password'          => Hash::make('password123'),
            'role'              => 'user',
            'email_verified_at' => now(), // pre-verified
        ]);
    }
}