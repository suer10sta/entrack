<?php

namespace App\Http\Controllers\manager;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\User;
use App\Models\Visit;
use App\Models\Client;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ManagerDashboardController extends Controller
{
    public function index(Request $request)
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

        // Time reference for activity metrics
        $now = Carbon::now();

        // Daily / Monthly active users based on recent updates (logins and activity update the user record)
        $dau = User::where('updated_at', '>=', $now->copy()->subDay())->count();
        $mau = User::where('updated_at', '>=', $now->copy()->subDays(30))->count();

        // Signups, trials and onboarding metrics

        // Visits from people that do NOT have a user account in our database
        // We use the last_login_ip on users to detect "clients" and exclude those IPs.
        $nonClientVisitsQuery = Visit::where(function ($query) {
            $query
                // Visits with no IP can never be matched to a user, keep them
                ->whereNull('ip')
                // Or visits whose IP does not appear as a user last_login_ip
                ->orWhereNotIn('ip', function ($subQuery) {
                    $subQuery
                        ->select('last_login_ip')
                        ->from('users')
                        ->whereNotNull('last_login_ip');
                });
        })->where(function ($query) {
            // Exclude localhost traffic from dashboard analytics
            $query
                ->whereNull('ip')
                ->orWhereNotIn('ip', ['127.0.0.1', '::1']);
        });

        // Total visits from non-clients
        $totalVisits = (clone $nonClientVisitsQuery)->count();

        // Unique visitors (non-clients): count distinct IPs among non-client visits that have an IP
        $uniqueNonClientVisits = (clone $nonClientVisitsQuery)
            ->whereNotNull('ip')
            ->distinct('ip')
            ->count('ip');

        // Conversion rate: signups ÷ unique non-client visitors (last 30 days)
        $periodDays = 30;
        $fromDate = $now->copy()->subDays($periodDays);

        $periodNonClientVisits = (clone $nonClientVisitsQuery)
            ->where('created_at', '>=', $fromDate);

        $uniqueNonClientVisitorsPeriod = (clone $periodNonClientVisits)
            ->whereNotNull('ip')
            ->distinct('ip')
            ->count('ip');

        // New signups (non-employees) in period (30d) and last 7 days
        $signupsPeriod = User::where('created_at', '>=', $fromDate)
            ->where('is_employee', 0)
            ->count();

        $newSignupsLast7 = User::where('created_at', '>=', $now->copy()->subDays(7))
            ->where('is_employee', 0)
            ->count();

        $newSignupsLast30 = User::where('created_at', '>=', $fromDate)
            ->where('is_employee', 0)
            ->count();

        $visitToSignupConversionRate = $uniqueNonClientVisitorsPeriod > 0
            ? round(($signupsPeriod / $uniqueNonClientVisitorsPeriod) * 100, 1)
            : 0.0;

        // Active trials and trial → paid conversion (lifetime)
        $activeTrials = Entreprise::whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', $now)
            ->whereNull('type_compt')
            ->count();

        $totalTrialEntreprises = Entreprise::whereNotNull('trial_ends_at')->count();

        $trialConvertedToPaid = Entreprise::whereNotNull('trial_ends_at')
            ->whereNotNull('type_compt')
            ->count();

        $trialToPaidConversionRate = $totalTrialEntreprises > 0
            ? round(($trialConvertedToPaid / $totalTrialEntreprises) * 100, 1)
            : 0.0;

        // Average time to first key action (first client or project) per entreprise
        $signupByEntreprise = User::where('is_employee', 0)
            ->where('entreprise_id', '>', 0)
            ->select('entreprise_id', DB::raw('MIN(created_at) as first_signup_at'))
            ->groupBy('entreprise_id')
            ->get()
            ->pluck('first_signup_at', 'entreprise_id');

        $firstClientMap = Client::select('entreprise_id', DB::raw('MIN(created_at) as first_client_at'))
            ->groupBy('entreprise_id')
            ->get()
            ->pluck('first_client_at', 'entreprise_id');

        $firstProjectMap = Project::select('entreprise_id', DB::raw('MIN(created_at) as first_project_at'))
            ->groupBy('entreprise_id')
            ->get()
            ->pluck('first_project_at', 'entreprise_id');

        $firstKeyActionMap = [];

        foreach ($firstClientMap as $entrepriseId => $date) {
            if ($entrepriseId) {
                $firstKeyActionMap[$entrepriseId] = Carbon::parse($date);
            }
        }

        foreach ($firstProjectMap as $entrepriseId => $date) {
            if (!$entrepriseId || !$date) {
                continue;
            }

            $projectDate = Carbon::parse($date);
            if (!isset($firstKeyActionMap[$entrepriseId]) || $projectDate->lt($firstKeyActionMap[$entrepriseId])) {
                $firstKeyActionMap[$entrepriseId] = $projectDate;
            }
        }

        $diffSeconds = [];
        foreach ($signupByEntreprise as $entrepriseId => $signupAt) {
            if (!isset($firstKeyActionMap[$entrepriseId]) || !$signupAt) {
                continue;
            }

            $signupAtCarbon = Carbon::parse($signupAt);
            $actionAt = $firstKeyActionMap[$entrepriseId];

            if ($actionAt->lessThanOrEqualTo($signupAtCarbon)) {
                continue;
            }

            $diffSeconds[] = $signupAtCarbon->diffInSeconds($actionAt);
        }

        $avgTimeToFirstKeyActionDays = 0.0;
        if (count($diffSeconds) > 0) {
            $avgSeconds = array_sum($diffSeconds) / count($diffSeconds);
            $avgTimeToFirstKeyActionDays = round($avgSeconds / 86400, 1);
        }

        // Top visitor locations, restricted to non-client visits
        // Also compute unique non-client visitors per country (distinct IPs).
        $visitsByCountry = (clone $nonClientVisitsQuery)
            ->select(
                'country',
                'country_code',
                DB::raw('count(*) as total'),
                DB::raw('count(distinct ip) as unique_total')
            )
            ->groupBy('country', 'country_code')
            ->orderByDesc('total')
            ->paginate(10, ['*'], 'locations_page');

        // Top traffic sources (utm_source / referrer), restricted to non-client visits
        $topTrafficSources = (clone $nonClientVisitsQuery)
            ->selectRaw("
                COALESCE(
                    NULLIF(utm_source, ''),
                    CASE
                        WHEN referrer IS NULL OR referrer = '' THEN 'direct'
                        ELSE referrer
                    END
                ) as source,
                count(*) as total
            ")
            ->groupBy('source')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // Visits over time (last 30 days) split by device type
        $visitsOverTimeRaw = (clone $periodNonClientVisits)
            ->selectRaw("DATE(created_at) as visit_date, COALESCE(device_type, 'unknown') as device_type, count(*) as total")
            ->groupBy('visit_date', 'device_type')
            ->orderBy('visit_date')
            ->get();

        $visitsOverTime = [];
        foreach ($visitsOverTimeRaw as $row) {
            $dateKey = $row->visit_date;
            if (!isset($visitsOverTime[$dateKey])) {
                $visitsOverTime[$dateKey] = [
                    'date' => $dateKey,
                    'desktop' => 0,
                    'mobile' => 0,
                    'tablet' => 0,
                    'unknown' => 0,
                ];
            }
            $device = $row->device_type;
            if (!in_array($device, ['desktop', 'mobile', 'tablet', 'unknown'], true)) {
                $device = 'unknown';
            }
            $visitsOverTime[$dateKey][$device] += $row->total;
        }

        // Top landing pages with a simple bounce-rate approximation:
        // bounce = visits where this URL is the only visit ever recorded for that IP.
        // Limit to the app's main domain (from config('app.url')) so we ignore old / external hosts.
        $appUrl = rtrim(config('app.url'), '/');
        $mainHost = parse_url($appUrl, PHP_URL_HOST);

        $topLandingPagesBase = (clone $nonClientVisitsQuery)
            ->when($mainHost, function ($query) use ($mainHost) {
                $query->where(function ($inner) use ($mainHost) {
                    $inner
                        ->where('url', 'like', 'http://' . $mainHost . '%')
                        ->orWhere('url', 'like', 'https://' . $mainHost . '%')
                        ->orWhere('url', 'like', 'http://www.' . $mainHost . '%')
                        ->orWhere('url', 'like', 'https://www.' . $mainHost . '%');
                });
            })
            ->select('url', DB::raw('count(*) as total'))
            ->groupBy('url')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $singleVisitIps = Visit::select('ip')
            ->whereNotNull('ip')
            ->whereNotIn('ip', ['127.0.0.1', '::1'])
            ->groupBy('ip')
            ->havingRaw('count(*) = 1')
            ->pluck('ip');

        $topLandingPages = $topLandingPagesBase->map(function ($row) use ($singleVisitIps) {
            $bounceVisits = Visit::where('url', $row->url)
                ->whereIn('ip', $singleVisitIps)
                ->count();

            $bounceRate = $row->total > 0
                ? round(($bounceVisits / $row->total) * 100, 1)
                : 0.0;

            return (object) [
                'url' => $row->url,
                'total' => $row->total,
                'bounce_rate' => $bounceRate,
            ];
        });

        // Onboarding funnel (last 30 days)
        $completedOnboardingCount = collect($firstClientMap)
            ->filter(function ($date) use ($fromDate) {
                return $date && Carbon::parse($date)->greaterThanOrEqualTo($fromDate);
            })
            ->count();

        $firstKeyActionCount = collect($firstProjectMap)
            ->filter(function ($date) use ($fromDate) {
                return $date && Carbon::parse($date)->greaterThanOrEqualTo($fromDate);
            })
            ->count();

        $newPayingCustomers = Entreprise::whereNotNull('type_compt')
            ->where('created_at', '>=', $fromDate)
            ->count();

        $onboardingFunnel = [
            'visitors' => $uniqueNonClientVisitorsPeriod,
            'signups' => $signupsPeriod,
            'completedOnboarding' => $completedOnboardingCount,
            'firstKeyAction' => $firstKeyActionCount,
            'payingCustomers' => $newPayingCustomers,
        ];

        // Earnings overview from Stripe invoices (last 12 months)
        $now = Carbon::now();
        $locale = app()->getLocale();
        $startPeriod = $now->copy()->subMonths(11)->startOfMonth();
        $endPeriod = $now->copy()->endOfMonth();

        // Prepare month labels
        $monthLabels = [];
        $invoicedPerMonth = [];
        $paidPerMonth = [];

        for ($i = 11; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $key = $monthStart->format('Y-m');
            // Use locale-aware, translated month labels for the chart
            $monthLabels[$key] = $monthStart->locale($locale)->translatedFormat('M Y');
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
            'dau' => $dau,
            'mau' => $mau,
            'totalVisits' => $totalVisits,
            'uniqueNonClientVisits' => $uniqueNonClientVisits,
            'newSignupsLast7' => $newSignupsLast7,
            'newSignupsLast30' => $newSignupsLast30,
            'activeTrials' => $activeTrials,
            'trialToPaidConversionRate' => $trialToPaidConversionRate,
            'avgTimeToFirstKeyActionDays' => $avgTimeToFirstKeyActionDays,
            'visitToSignupConversionRate' => $visitToSignupConversionRate,
            'visitsByCountry' => $visitsByCountry,
            'topTrafficSources' => $topTrafficSources,
            'visitsOverTime' => array_values($visitsOverTime),
            'topLandingPages' => $topLandingPages,
            'onboardingFunnel' => $onboardingFunnel,
            'chartMonths' => $chartMonths,
            'chartInvoiced' => $chartInvoiced,
            'chartPaid' => $chartPaid,
            'totalInvoiced' => $totalInvoiced,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
        ]);
    }

    public function entreprises()
    {
        $entreprisesList = Entreprise::orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('manager.dashboard.entreprises', [
            'entreprisesList' => $entreprisesList,
        ]);
    }

    public function activeSubscriptions()
    {
        $activeStatuses = ['active', 'trialing', 'past_due', 'unpaid'];

        $activeSubscriptionsEntreprises = Entreprise::whereNotNull('type_compt')
            ->where(function ($query) {
                $query->whereNull('account_status')
                    ->orWhere('account_status', '!=', 'inactive');
            })
            ->whereHas('subscriptions', function ($subQuery) use ($activeStatuses) {
                $subQuery->whereIn('stripe_status', $activeStatuses);
            })
            ->with('subscriptions')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('manager.dashboard.active-subscriptions', [
            'activeSubscriptionsEntreprises' => $activeSubscriptionsEntreprises,
        ]);
    }

    public function users()
    {
        $usersList = User::with('entreprise')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('manager.dashboard.users', [
            'usersList' => $usersList,
        ]);
    }

    public function dailyActiveUsers()
    {
        $since = Carbon::now()->subDay();

        $usersList = User::with('entreprise')
            ->where('updated_at', '>=', $since)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('manager.dashboard.active-users', [
            'usersList' => $usersList,
            'title' => 'Daily active users (24h)',
            'subtitle' => 'Users active in the last 24 hours.',
        ]);
    }

    public function monthlyActiveUsers()
    {
        $since = Carbon::now()->subDays(30);

        $usersList = User::with('entreprise')
            ->where('updated_at', '>=', $since)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('manager.dashboard.active-users', [
            'usersList' => $usersList,
            'title' => 'Monthly active users (30d)',
            'subtitle' => 'Users active in the last 30 days.',
        ]);
    }

    public function newSignupsUsers()
    {
        $since = Carbon::now()->subDays(30);

        $usersList = User::with('entreprise')
            ->where('is_employee', 0)
            ->where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('manager.dashboard.active-users', [
            'usersList' => $usersList,
            'title' => 'New signups (30d)',
            'subtitle' => 'Newly registered users in the last 30 days.',
        ]);
    }

    public function nonClientVisits(Request $request)
    {
        $selectedCountry = $request->query('country');
        $unknownCountryToken = '__unknown__';

        $baseQuery = Visit::query()
            ->where(function ($query) {
                $query
                    ->whereNull('ip')
                    ->orWhereNotIn('ip', function ($subQuery) {
                        $subQuery
                            ->select('last_login_ip')
                            ->from('users')
                            ->whereNotNull('last_login_ip');
                    });
            })
            ->where(function ($query) {
                $query
                    ->whereNull('ip')
                    ->orWhereNotIn('ip', ['127.0.0.1', '::1']);
            });

        if ($selectedCountry !== null && $selectedCountry !== '') {
            if ($selectedCountry === $unknownCountryToken) {
                $baseQuery->where(function ($query) {
                    $query->whereNull('country')->orWhere('country', '');
                });
            } else {
                $baseQuery->where('country', $selectedCountry);
            }
        }

        $visitsList = (clone $baseQuery)
            ->orderByRaw("CASE WHEN country IS NULL OR country = '' THEN 1 ELSE 0 END")
            ->orderBy('country')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        // Add "visits_count" for each IP shown on the current page (non-client visits only).
        $ipsOnPage = $visitsList->getCollection()
            ->pluck('ip')
            ->filter()
            ->unique()
            ->values();

        $countsByIp = collect();
        if ($ipsOnPage->isNotEmpty()) {
            $countsByIp = (clone $baseQuery)
                ->whereIn('ip', $ipsOnPage)
                ->groupBy('ip')
                ->select('ip', DB::raw('count(*) as total'))
                ->pluck('total', 'ip');
        }

        $visitsList->setCollection(
            $visitsList->getCollection()->map(function ($visit) use ($countsByIp) {
                $visit->visits_count = $visit->ip ? (int) ($countsByIp[$visit->ip] ?? 0) : 0;
                return $visit;
            })
        );

        return view('manager.dashboard.visits', [
            'visitsList' => $visitsList,
            'title' => 'Website visits (non client)',
            'subtitle' => 'All non-client visits with IP, date/time and location.',
            'isUnique' => false,
            'selectedCountry' => $selectedCountry,
            'unknownCountryToken' => $unknownCountryToken,
        ]);
    }

    public function uniqueNonClientVisitors()
    {
        $visitsList = Visit::query()
            ->whereNotNull('ip')
            ->whereNotIn('ip', ['127.0.0.1', '::1'])
            ->whereNotIn('ip', function ($subQuery) {
                $subQuery
                    ->select('last_login_ip')
                    ->from('users')
                    ->whereNotNull('last_login_ip');
            })
            ->select(
                'ip',
                DB::raw('MAX(created_at) as visited_at'),
                DB::raw('MAX(country) as country'),
                DB::raw('MAX(city) as city'),
                DB::raw('COUNT(*) as visits_count')
            )
            ->groupBy('ip')
            ->orderByRaw("CASE WHEN MAX(country) IS NULL OR MAX(country) = '' THEN 1 ELSE 0 END")
            ->orderByRaw("MAX(country) ASC")
            ->orderByDesc('visited_at')
            ->paginate(25);

        return view('manager.dashboard.visits', [
            'visitsList' => $visitsList,
            'title' => 'Unique visitors (non client)',
            'subtitle' => 'One row per non-client IP with latest visit date/time and location.',
            'isUnique' => true,
        ]);
    }

    public function invoices()
    {
        $invoicesList = collect();

        $stripeEntreprises = Entreprise::whereNotNull('stripe_id')->get();

        foreach ($stripeEntreprises as $entreprise) {
            try {
                $invoices = $entreprise->invoicesIncludingPending();
            } catch (\Throwable $e) {
                continue;
            }

            foreach ($invoices as $invoice) {
                try {
                    $stripeInvoice = $invoice->asStripeInvoice();
                } catch (\Throwable $e) {
                    continue;
                }

                $amount = isset($stripeInvoice->total) ? $stripeInvoice->total / 100 : 0;

                $invoicesList->push([
                    'entreprise' => $entreprise,
                    'date' => $invoice->date(),
                    'total' => $amount,
                    'status' => $stripeInvoice->status ?? null,
                    'number' => $stripeInvoice->number ?? null,
                ]);
            }
        }

        $invoicesList = $invoicesList
            ->sortByDesc('date')
            ->values();

        return view('manager.dashboard.invoices', [
            'invoicesList' => $invoicesList,
        ]);
    }
}


