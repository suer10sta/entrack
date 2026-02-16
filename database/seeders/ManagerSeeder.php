<?php

namespace Database\Seeders;

use App\Models\Manager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Manager::where('email', 'manager@example.com')->exists()) {
            Manager::create([
                'name' => 'Default Manager',
                'email' => 'manager@example.com',
                'password' => Hash::make('password'),
            ]);
        }
    }
}


