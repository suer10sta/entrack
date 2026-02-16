<?php

namespace App\Http\Controllers\manager;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ManagerDashboardController extends Controller
{
    public function index()
    {
        $totalEntreprises = Entreprise::count();
        
        // Count only entreprises with active subscriptions (not canceled)
        // An active subscription means:
        // 1. They have a plan (type_compt is not null)
        // 2. Their account_status is not 'inactive' (webhooks set this when canceled)
        // 3. They have an active subscription in the subscriptions table
        $activeEntreprises = Entreprise::whereNotNull('type_compt')
            ->where(function ($query) {
                // Exclude entreprises with account_status = 'inactive' (canceled subscriptions)
                $query->whereNull('account_status')
                    ->orWhere('account_status', '!=', 'inactive');
            })
            ->whereHas('subscriptions', function ($subQuery) {
                // Only count subscriptions with active statuses
                $activeStatuses = ['active', 'trialing', 'past_due', 'unpaid'];
                $subQuery->whereIn('stripe_status', $activeStatuses);
            })
            ->count();

        $totalUsers = User::count();

        // Earnings overview from Stripe invoices (last 12 months)
        $now = Carbon::now();
        $startPeriod = $now->copy()->subMonths(11)->startOfMonth();
        $endPeriod = $now->copy()->endOfMonth();

        // Prepare month labels
        $monthLabels = [];
        $invoicedPerMonth = [];
        $paidPerMonth = [];

        for ($i = 11; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $key = $monthStart->format('Y-m');
            $monthLabels[$key] = $monthStart->format('M Y');
            $invoicedPerMonth[$key] = 0.0;
            $paidPerMonth[$key] = 0.0;
        }

        // Aggregate Stripe invoice totals via Cashier
        $entreprises = Entreprise::whereNotNull('stripe_id')->get();

        foreach ($entreprises as $entreprise) {
            try {
                $invoices = $entreprise->invoicesIncludingPending();
            } catch (\Throwable $e) {
                continue;
            }

            foreach ($invoices as $invoice) {
                $date = $invoice->date();
                if ($date->lt($startPeriod) || $date->gt($endPeriod)) {
                    continue;
                }

                $key = $date->format('Y-m');
                if (!array_key_exists($key, $invoicedPerMonth)) {
                    continue;
                }

                $stripeInvoice = $invoice->asStripeInvoice();
                $amount = isset($stripeInvoice->total) ? $stripeInvoice->total / 100 : 0;

                $invoicedPerMonth[$key] += $amount;
                if (($stripeInvoice->status ?? null) === 'paid') {
                    $paidPerMonth[$key] += $amount;
                }
            }
        }

        $chartMonths = collect($monthLabels)->values();
        $chartInvoiced = collect($invoicedPerMonth)->values();
        $chartPaid = collect($paidPerMonth)->values();

        $totalInvoiced = $chartInvoiced->sum();
        $totalPaid = $chartPaid->sum();
        $totalOutstanding = max($totalInvoiced - $totalPaid, 0);

        return view('manager.dashboard.index', [
            'totalEntreprises' => $totalEntreprises,
            'activeEntreprises' => $activeEntreprises,
            'totalUsers' => $totalUsers,
            'chartMonths' => $chartMonths,
            'chartInvoiced' => $chartInvoiced,
            'chartPaid' => $chartPaid,
            'totalInvoiced' => $totalInvoiced,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
        ]);
    }
}


