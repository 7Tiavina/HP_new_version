<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
        'email'         => 'admin@hello.com',
        'password_hash' => \Illuminate\Support\Facades\Hash::make('Secret123!'),
        'role'          => 'admin',
    ]);
    }
}
