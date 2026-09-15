<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Employees;
use App\Models\Entreprise;
use App\Models\employees_tasks;
use App\Models\Permission;
use App\Models\EnterpriseRolePermission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Tasks;
use App\Models\tasks_timer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TransferTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->cleanupTransferTestAccounts();

        $password = Hash::make('password');

        $companyA = $this->seedCompany(
            'Entreprise A Transfert',
            'admin-a@transfert.test',
            $password,
            'Projet Chantier A',
            [
                ['date' => '2026-03-01', 'task' => 'Câblage', 'minutes' => 240],
                ['date' => '2026-03-01', 'task' => 'Tests', 'minutes' => 120],
                ['date' => '2026-03-02', 'task' => 'Câblage', 'minutes' => 180],
                ['date' => '2026-03-02', 'task' => 'Mise en service', 'minutes' => 90],
            ]
        );

        $this->seedCompany(
            'Entreprise B Transfert',
            'admin-b@transfert.test',
            $password,
            'Projet Chantier B',
            []
        );

        $this->command?->info('Transfer test data created.');
        $this->command?->info('Entreprise A: admin-a@transfert.test / password');
        $this->command?->info('Entreprise B: admin-b@transfert.test / password');
        $this->command?->info('Projet A ID: ' . $companyA['project_id']);
    }

    private function seedCompany(string $companyName, string $email, string $password, string $projectName, array $timerRows): array
    {
        $entreprise = Entreprise::create([
            'name' => $companyName,
            'is_activated' => true,
            'duration' => now()->addYear(),
            'image' => 'default.jpg',
            'trial_ends_at' => now()->addDays(30),
        ]);

        $user = User::create([
            'name' => 'admin',
            'email' => $email,
            'password' => $password,
            'entreprise_id' => $entreprise->id,
            'email_verified_at' => now(),
            'timezone' => 'Europe/Paris',
        ]);
        $user->assignRole(['Admin', 'SuperAdmin']);

        $this->assignEnterprisePermissions($entreprise->id);

        $employee = Employees::create([
            'name' => 'Technicien ' . substr($companyName, -1),
            'email' => 'tech-' . $entreprise->id . '@transfert.test',
            'password' => $password,
            'phoneNumber' => '0000000000',
            'salary' => 0,
            'cost_per_hour' => 45,
            'workHours_limit' => 8,
            'entreprise_id' => $entreprise->id,
            'user_id' => $user->id,
            'archived' => 0,
        ]);

        $client = Client::create([
            'name' => 'Client ' . substr($companyName, -1),
            'email' => 'client-' . $entreprise->id . '@transfert.test',
            'phone' => '0000000000',
            'address' => '1 rue Test',
            'city' => 'Paris',
            'zip' => '75001',
            'country' => 'France',
            'entreprise_id' => $entreprise->id,
        ]);

        $project = Project::create([
            'name' => $projectName,
            'description' => 'Projet de test pour export/import transfert',
            'budget' => 50000,
            'cost_per_hour' => 75,
            'start_date' => '2026-03-01',
            'end_date' => '2026-12-31',
            'status' => 0,
            'client_id' => $client->id,
            'entreprise_id' => $entreprise->id,
            'tva' => 21,
        ]);

        $taskMap = [];

        foreach ($timerRows as $row) {
            $taskTitle = $row['task'];
            if (!isset($taskMap[$taskTitle])) {
                $task = Tasks::create([
                    'title' => $taskTitle,
                    'description' => 'Tâche de test',
                    'project_name' => $project->name,
                    'project_id' => $project->id,
                    'entreprise_id' => $entreprise->id,
                    'user_id' => $user->id,
                    'consumed_hours' => 0,
                ]);
                $taskMap[$taskTitle] = $task;

                employees_tasks::create([
                    'task_id' => $task->id,
                    'task_name' => $task->title,
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'entreprise_id' => $entreprise->id,
                ]);
            } else {
                $task = $taskMap[$taskTitle];
            }

            $employeeTask = employees_tasks::where('task_id', $task->id)
                ->where('employee_id', $employee->id)
                ->first();

            $start = Carbon::parse($row['date'])->setTime(8, 0, 0);
            $end = $start->copy()->addMinutes($row['minutes']);

            tasks_timer::create([
                'task_id' => $task->id,
                'task_name' => $task->title,
                'task_employee_id' => $employeeTask->id,
                'project_id' => $project->id,
                'employee_id' => $employee->id,
                'start_date' => $start,
                'end_date' => $end,
                'duration' => $row['minutes'],
                'cost' => $employee->cost_per_hour * ($row['minutes'] / 60),
                'entreprise_id' => $entreprise->id,
                'paused' => 0,
            ]);
        }

        return [
            'entreprise_id' => $entreprise->id,
            'project_id' => $project->id,
            'user_email' => $email,
        ];
    }

    private function cleanupTransferTestAccounts(): void
    {
        foreach (['admin-a@transfert.test', 'admin-b@transfert.test'] as $email) {
            $user = User::where('email', $email)->first();
            if (!$user) {
                continue;
            }

            $entrepriseId = $user->entreprise_id;
            tasks_timer::where('entreprise_id', $entrepriseId)->delete();
            employees_tasks::where('entreprise_id', $entrepriseId)->delete();
            Tasks::where('entreprise_id', $entrepriseId)->delete();
            Project::where('entreprise_id', $entrepriseId)->delete();
            Client::where('entreprise_id', $entrepriseId)->delete();
            Employees::where('entreprise_id', $entrepriseId)->delete();
            $user->delete();
            Entreprise::where('id', $entrepriseId)->delete();
        }
    }

    private function assignEnterprisePermissions(int $entrepriseId): void
    {
        $allPermissions = Permission::all();
        $superAdminRole = Role::where('name', 'SuperAdmin')->first();
        $adminRole = Role::where('name', 'Admin')->first();

        if ($superAdminRole) {
            foreach ($allPermissions as $permission) {
                EnterpriseRolePermission::firstOrCreate([
                    'entreprise_id' => $entrepriseId,
                    'role_id' => $superAdminRole->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }

        if ($adminRole) {
            foreach ($allPermissions as $permission) {
                if ($permission->name === 'Assign Superadmin role') {
                    continue;
                }

                EnterpriseRolePermission::firstOrCreate([
                    'entreprise_id' => $entrepriseId,
                    'role_id' => $adminRole->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }
    }
}
