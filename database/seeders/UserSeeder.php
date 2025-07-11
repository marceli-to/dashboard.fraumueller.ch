<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'firstname' => 'Marcel',
            'name' => 'Stadelmann',
            'email' => 'm@marceli.to',
            'password' => \Hash::make('7aq31rr23'),
            'email_verified_at' => '2025-01-22 18:11:24',
        ]);
    }
}
