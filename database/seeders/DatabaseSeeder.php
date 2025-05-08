<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrator',
            'username' => 'administrator',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'),
        ]);
        User::factory()->create([
            'name' => 'User',
            'username' => 'user',
            'email' => 'user@user.com',
            'password' => Hash::make('user123'),
        ]);
        User::factory(997)->create();
        User::factory()->create([
            'name' => 'Michael',
            'username' => 'michael',
            'email' => 'michel@michel.com',
            'password' => Hash::make('michel123'),
        ]);
    }
}
