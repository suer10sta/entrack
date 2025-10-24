<?php

namespace App\Http\Controllers\admin;

use App\Models\User;
use App\Models\Tasks;
use App\Models\Teams;
use App\Models\Client;
use App\Models\Project;
use App\Models\Employees;
use App\Models\todoliste;
use App\Models\Entreprise;
use App\Models\invoices_history;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\calendars;
use Carbon\Carbon;
use App\Models\tasks_timer;

class adminController extends Controller
{
    public function index()
    {
        // Récupère l'ID de l'entreprise de l'utilisateur connecté
        $enterpriseId = Auth::user()->entreprise_id;
        $now = Carbon::now(); // Date actuelle pour les calculs

        // --- 1. Récupération des Stats pour les Cartes ---
        $stat_clients = Client::where('entreprise_id', $enterpriseId)
                                ->where(function($query) {
                                    $query->whereNull('AcceptedDevisClient')
                                          ->orWhere('AcceptedDevisClient', '=', 1);
                                })
                                ->count();

        $stat_employees = Employees::where('entreprise_id', $enterpriseId)
                                   ->where('archived', 0)
                                   ->count();

        // Compte les projets non archivés (ajustez si 'status' est plus pertinent)
        $stat_projects_in_progress = Project::where('entreprise_id', $enterpriseId)
                                            ->where('archived', 0) 
                                            ->count(); 
        
        // Calcul du revenu mensuel (factures payées ce mois-ci)
        $stat_revenue = invoices_history::where('entreprise_id', $enterpriseId)
                                        ->where('Payment_status', 1)
                                        ->whereMonth('created_at', $now->month)
                                        ->whereYear('created_at', $now->year)
                                        ->sum('total_ttc');
        $stat_revenue_formatted = '€' . number_format($stat_revenue ?? 0, 2, ',', ' ');


        // --- 2. Récupération des Membres de l'Équipe ---
        $team_members = Employees::where('entreprise_id', $enterpriseId)
                                 ->where('archived', 0)
                                 // Eager Load : Charge les relations nécessaires en une seule fois
                                 ->with([ 
                                     'user' => function ($query) { // Charge l'utilisateur associé
                                         $query->with('roles'); // Charge les rôles de cet utilisateur
                                     }, 
                                     'teams', // Charge les équipes de l'employé
                                     'activeTaskTimer' => function ($query) { // Charge le timer actif
                                         $query->with('task'); // Charge la tâche associée au timer actif
                                     }
                                 ])
                                 ->orderBy('name', 'asc')
                                 ->get();

        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Calculs additionnels pour chaque membre
        foreach ($team_members as $member) {
            // Stats mensuelles depuis la table tasks_timer
            $monthlyStats = tasks_timer::where('employee_id', $member->id)
                                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                                ->selectRaw('SUM(duration) as total_duration, COUNT(DISTINCT task_id) as total_tasks, COUNT(DISTINCT DATE(created_at)) as total_days')
                                ->first();

            $totalMinutes = $monthlyStats->total_duration ?? 0;
            $member->monthly_hours = floor($totalMinutes / 60) . 'h ' . ($totalMinutes % 60) . 'm';
            $member->monthly_tasks = $monthlyStats->total_tasks ?? 0;
            $member->monthly_days = $monthlyStats->total_days ?? 0;

            // Calcul Utilisation
            $limitHours = $member->workHours_limit ?? 160; // Limite par défaut
            $limitMinutes = $limitHours * 60;
            $utilizationPercentage = ($limitMinutes > 0) ? round(($totalMinutes / $limitMinutes) * 100) : 0;
            $member->utilization = $utilizationPercentage . '%';
            $member->limit_formatted = $limitHours . 'h';

            // Status (Actif/Inactif)
            $member->current_status = $member->user && $member->user->is_activate == 1 ? 'Active' : 'Inactive';
            
            // Type (Ex: 'Full-time' - A adapter si cette info est dans la DB)
            $member->employment_type = 'Full-time'; // Mettez la vraie valeur si disponible

             // Tâche actuelle (via la relation eager loaded)
            $member->current_task_name = $member->activeTaskTimer && $member->activeTaskTimer->task ? $member->activeTaskTimer->task->title : 'No active task';

             // Équipes formatées (via la relation eager loaded)
            $member->assigned_teams = $member->teams->isEmpty() ? 'Not assigned' : $member->teams->pluck('name')->implode(', ');
            
             // Rôles (via la relation eager loaded)
             $member->role_names = $member->user ? $member->user->roles->pluck('name') : collect();
        }


        // --- 3. Récupération de l'Aperçu des Projets ---
        $projects_overview = Project::where('entreprise_id', $enterpriseId)
                                     ->where('archived', 0)
                                     // Eager Load : Charge les relations nécessaires
                                     ->with(['client', 'teams', 'timers']) 
                                     ->orderBy('name', 'asc')
                                     ->get();

        // Calculs additionnels pour chaque projet
        foreach ($projects_overview as $project) {
            // Somme des durées via la relation 'timers'
            $totalDurationMinutes = $project->timers->sum('duration'); 
            $estimatedMinutes = ($project->estimated_hours ?? 0) * 60;
            
            // Durée formatée
            $hours = floor($totalDurationMinutes / 60);
            $minutes = $totalDurationMinutes % 60;
            $project->duration_formatted = $hours . 'h ' . $minutes . 'm';

            // Différence formatée
            $differenceMinutes = $estimatedMinutes - $totalDurationMinutes;
            $diffHours = floor(abs($differenceMinutes) / 60);
            $diffMinutes = abs($differenceMinutes) % 60;
            $sign = $differenceMinutes >= 0 ? '+' : '-';
            $project->difference_formatted = $sign . $diffHours . 'h ' . $diffMinutes . 'm';
            $project->difference_color = $differenceMinutes >= 0 ? 'text-green-500' : 'text-red-500';

            // Progression
            $progressPercentage = ($estimatedMinutes > 0) ? ($totalDurationMinutes / $estimatedMinutes) * 100 : 0;
            $project->progress_formatted = number_format($progressPercentage, 2) . '%';
        }

        // --- 4. Récupération des Événements du Jour ---
        $dayEvents = calendars::where('entreprise_id', $enterpriseId)
                               ->whereDate('start_date', today())
                               ->orderBy('start_date')
                               ->get();


        // --- 5. Envoi des données UNIQUEMENT à la nouvelle vue ---
        return view("components.dashboard.content", compact( // MODIFIÉ ICI pour pointer vers la nouvelle vue
            'stat_clients',
            'stat_employees',
            'stat_projects_in_progress',
            'stat_revenue_formatted',
            'team_members', // La liste formatée des membres
            'projects_overview', // La liste formatée des projets
            'dayEvents' // Les événements du jour
            // Ajoutez d'autres variables si absolument nécessaires pour cette vue spécifique
        ));
    }

    public function filterInvoices(Request $request)
    {
        try {
            $query = invoices_history::where('entreprise_id', Auth::user()->entreprise_id);

            // Apply filters
            if ($request->has('client') && $request->client != '') {
                $query->where('client_id', $request->client);
            }
            if ($request->has('project') && $request->project != '') {
                $query->where('project_id', $request->project);
            }
            if ($request->has('month') && $request->month != '') {
                $query->whereMonth('created_at', $request->month);
            }

            // Get paginated results
            $invoices = $query->orderBy('number', 'desc')->paginate(5);
            $entreprise_infos = Entreprise::where('id', auth()->user()->entreprise_id)->first();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'html' => view('admin.partials.invoice-table', compact('invoices', 'entreprise_infos'))->render()
                ]);
            }

            return view('admin.index', compact('invoices', 'entreprise_infos'));
        } catch (\Exception $e) {
            \Log::error('Error in filterInvoices: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while filtering invoices: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'An error occurred while filtering invoices.');
        }
    }
}
