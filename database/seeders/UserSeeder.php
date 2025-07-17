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

      User::create([
        'firstname' => 'Andrea',
        'name' => 'Müller',
        'email' => 'mueller@pandaundpinguin.ch',
        'password' => \Hash::make('Fr@U-mU3l-d@sH-$32c'),
        'email_verified_at' => '2025-01-22 18:11:24',
      ]);

      User::create([
        'firstname' => 'Sabina',
        'name' => 'Sturzenegger',
        'email' => 'sturzenegger@pandaundpinguin.ch',
        'password' => \Hash::make('Fr@U-mU3l-d@sH-!48b'),
        'email_verified_at' => '2025-01-22 18:11:24',
      ]);

      User::create([
        'firstname' => 'Claudia',
        'name' => 'Bleicher',
        'email' => 'Claudia.bleicher@bluewin.ch',
        'password' => \Hash::make('Fr@U-mU3l-d@sH-*f32K'),
        'email_verified_at' => '2025-01-22 18:11:24',
      ]);

      User::create([
        'firstname' => 'Sarah',
        'name' => 'Pietrasanta',
        'email' => 'ps@pietrasanta.ch',
        'password' => \Hash::make('Fr@U-mU3l-d@sH-F$V9'),
        'email_verified_at' => '2025-01-22 18:11:24',
      ]);
    }
}
