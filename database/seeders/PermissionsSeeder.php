<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // name => entreprise_id
            ['name' => 'Add task', 'entreprise_id' => 26],
            ['name' => 'Delete task', 'entreprise_id' => 26],
            ['name' => 'Update task', 'entreprise_id' => 26],
            ['name' => 'Pointage', 'entreprise_id' => 0],
            ['name' => 'View teams', 'entreprise_id' => 0],
            ['name' => 'Manage workDaysReports', 'entreprise_id' => 0],
            ['name' => 'View pointage', 'entreprise_id' => 0],
            ['name' => 'Edit employee roles', 'entreprise_id' => 0],
            ['name' => 'Add employee', 'entreprise_id' => 26],
            ['name' => 'Delete employee', 'entreprise_id' => 26],
            ['name' => 'Update employee', 'entreprise_id' => 26],
            ['name' => 'Add client', 'entreprise_id' => 26],
            ['name' => 'Delete client', 'entreprise_id' => 26],
            ['name' => 'Update client', 'entreprise_id' => 26],
            ['name' => 'Add project', 'entreprise_id' => 26],
            ['name' => 'Delete project', 'entreprise_id' => 26],
            ['name' => 'Update project', 'entreprise_id' => 26],
            ['name' => 'Create invoice', 'entreprise_id' => 26],
            ['name' => 'Delete invoice', 'entreprise_id' => 26],
            ['name' => 'Create devis', 'entreprise_id' => 26],
            ['name' => 'Delete devis', 'entreprise_id' => 26],
            ['name' => 'Send invoice', 'entreprise_id' => 26],
            ['name' => 'Send devis', 'entreprise_id' => 0],
            ['name' => 'Pointage reports', 'entreprise_id' => 0],
            ['name' => 'Assign Superadmin role', 'entreprise_id' => 0],
            ['name' => 'View employee', 'entreprise_id' => 0],
            ['name' => 'View client', 'entreprise_id' => 0],
            ['name' => 'View project', 'entreprise_id' => 0],
            ['name' => 'View task', 'entreprise_id' => 0],
            ['name' => 'Update timer', 'entreprise_id' => 0],
            ['name' => 'Delete expense note', 'entreprise_id' => 0],
            ['name' => 'Create expense note', 'entreprise_id' => 0],
            ['name' => 'view invoices', 'entreprise_id' => 0],
            ['name' => 'view devis', 'entreprise_id' => 0],
            ['name' => 'view contacts', 'entreprise_id' => 0],
            ['name' => 'Billings', 'entreprise_id' => 0],
        ];

        foreach ($permissions as $perm) {
            Permission::query()->updateOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                ['entreprise_id' => $perm['entreprise_id']]
            );
        }
    }
}


