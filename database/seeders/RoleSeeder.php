<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $roles = ['Admin', 'User', 'Super_admin'];
        
        foreach ($roles as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                Role::create(['name' => $roleName]);
            }
        }
    }
}