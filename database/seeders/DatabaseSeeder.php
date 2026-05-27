<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
      User::updateOrCreate(
    [
        'email' => 'akulluleticia23@gmail.com'
    ],
    [
        'name' => 'Umoja Admin',
        'phone' => '0765451895',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]
);
       User::updateOrCreate(
    [
        'email' => 'travisakullu@gmail.com'
    ],
    [
        'name' => 'Leticia Nakamya',
        'phone' => '0700000002',
        'password' => bcrypt('password'),
        'role' => 'user',
        'email_verified_at' => now(),
    ]
);
    }
}