<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Models\Tasks;
use App\Models\Employees;
use App\Models\Teams;
use App\Models\invoices_history;
use App\Models\devis_history;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    private function getRoutePrefix()
    {
        $user = Auth::user();
        if ($user->hasRole('Admin') || $user->hasRole('SuperAdmin')) {
            return 'admin';
        } elseif ($user->hasRole('Responsable de projet et gestionnaire')) {
            return 'Responsable_gestionnaire';
        } elseif ($user->hasRole('Responsable de projet')) {
            return 'Responsable_de_projet';
        } elseif ($user->hasRole('Employee')) {
            return 'employee';
        }
        return 'admin';
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $entrepriseId = Auth::user()->entreprise_id;
        $prefix = $this->getRoutePrefix();

        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'results' => []
            ]);
        }

        $results = [];

        // Search Clients
        $clients = Client::where('entreprise_id', $entrepriseId)
            ->where(function($q) use ($query) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(phone) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(client_number) LIKE ?', ['%' . strtolower($query) . '%']);
            })
            ->limit(5)
            ->get();

        foreach ($clients as $client) {
            $results[] = [
                'type' => 'client',
                'id' => $client->id,
                'title' => $client->name,
                'subtitle' => $client->email ?: $client->phone,
                'url' => route($prefix . '.clients.show', $client->id),
                'icon' => 'user',
                'archived' => !empty($client->archived),
            ];
        }

        // Search Projects
        $projects = Project::where('entreprise_id', $entrepriseId)
            ->where(function($q) use ($query) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(description) LIKE ?', ['%' . strtolower($query) . '%']);
            })
            ->with('client')
            ->limit(5)
            ->get();

        foreach ($projects as $project) {
            $results[] = [
                'type' => 'project',
                'id' => $project->id,
                'title' => $project->name,
                'subtitle' => $project->client ? $project->client->name : 'No client',
                'url' => route($prefix . '.projects.show', $project->id),
                'icon' => 'folder',
                'archived' => !empty($project->archived),
            ];
        }

        // Search Tasks
        $tasks = Tasks::where('entreprise_id', $entrepriseId)
            ->where(function($q) use ($query) {
                $q->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(description) LIKE ?', ['%' . strtolower($query) . '%']);
            })
            ->limit(5)
            ->get();

        foreach ($tasks as $task) {
            $results[] = [
                'type' => 'task',
                'id' => $task->id,
                'title' => $task->title,
                'subtitle' => $task->project_name ?: 'No project',
                'url' => route($prefix . '.tasks.show', $task->id),
                'icon' => 'check-circle'
            ];
        }

        // Search Employees
        $employees = Employees::where('entreprise_id', $entrepriseId)
            ->where(function($q) use ($query) {
                $q->whereRaw('LOWER(employees.name) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(employees.email) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(employees."phoneNumber") LIKE ?', ['%' . strtolower($query) . '%']);
            })
            ->limit(5)
            ->get();

        foreach ($employees as $employee) {
            $results[] = [
                'type' => 'employee',
                'id' => $employee->id,
                'title' => $employee->name,
                'subtitle' => $employee->email ?: $employee->phoneNumber,
                'url' => route($prefix . '.employees.show', $employee->id),
                'icon' => 'users',
                'archived' => !empty($employee->archived),
            ];
        }

        // Search Teams
        $teams = Teams::where('entreprise_id', $entrepriseId)
            ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%'])
            ->limit(5)
            ->get();

        foreach ($teams as $team) {
            $results[] = [
                'type' => 'team',
                'id' => $team->id,
                'title' => $team->name,
                'subtitle' => 'Team',
                'url' => route($prefix . '.teams.index') . '#team-' . $team->id,
                'icon' => 'user-group'
            ];
        }

        // Search Invoices
        $invoices = invoices_history::where('entreprise_id', $entrepriseId)
            ->where(function($q) use ($query) {
                $q->whereRaw('LOWER(number) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(project_name) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(comment) LIKE ?', ['%' . strtolower($query) . '%']);
            })
            ->limit(5)
            ->get();

        foreach ($invoices as $invoice) {
            $invoiceNumber = $invoice->number;
            $results[] = [
                'type' => 'invoice',
                'id' => $invoice->id,
                'title' => 'Invoice #' . $invoiceNumber . ' - ' . $invoice->project_name,
                'subtitle' => 'Total: €' . number_format($invoice->total_ttc ?? 0, 2),
                'url' => route($prefix . '.invoices.servePdf', ['invoiceName' => $invoice->id]),
                'icon' => 'document-text'
            ];
        }

        // Search Devis
        $devis = devis_history::where('entreprise_id', $entrepriseId)
            ->where(function($q) use ($query) {
                $q->whereRaw('LOWER(devis_history.devis_number) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(devis_history.client_name) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(devis_history."Project_name") LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(devis_history.description) LIKE ?', ['%' . strtolower($query) . '%']);
            })
            ->limit(5)
            ->get();

        foreach ($devis as $devi) {
            $results[] = [
                'type' => 'devis',
                'id' => $devi->id,
                'title' => 'Devis #' . $devi->devis_number . ' - ' . ($devi->Project_name ?: $devi->client_name),
                'subtitle' => 'Budget: €' . number_format($devi->budget ?? 0, 2),
                'url' => route($prefix . '.devis.downloadDevis', ['id' => $devi->devis_number]),
                'icon' => 'document-duplicate'
            ];
        }

        return response()->json([
            'results' => $results,
            'query' => $query
        ]);
    }
}

