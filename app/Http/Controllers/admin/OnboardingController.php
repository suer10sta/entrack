<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Teams;
use App\Models\User;
use App\Models\employees;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    /**
     * A workspace needs onboarding until it is explicitly completed or
     * skipped. Existing companies were backfilled at migration time, so only
     * genuinely new workspaces ever qualify. (Deliberately NOT based on data
     * counts: registration itself creates system rows such as the owner's
     * employee record, which would otherwise suppress onboarding forever.)
     */
    public static function needsOnboarding($entreprise): bool
    {
        if (!$entreprise) {
            return false;
        }
        return empty($entreprise->onboarding_completed_at);
    }

    protected function guardFresh()
    {
        $entreprise = Auth::user()->entreprise ?? null;
        if (!self::needsOnboarding($entreprise)) {
            return redirect()->route('admin.index');
        }
        return null;
    }

    protected function markComplete(): void
    {
        $entreprise = Auth::user()->entreprise;
        if ($entreprise && empty($entreprise->onboarding_completed_at)) {
            $entreprise->onboarding_completed_at = now();
            $entreprise->save();
        }
    }

    public function welcome()
    {
        if ($redirect = $this->guardFresh()) {
            return $redirect;
        }
        return view('admin.onboarding.welcome', ['user' => Auth::user()]);
    }

    /** "Discover the dashboard yourself": persist + go to dashboard. */
    public function discover()
    {
        $this->markComplete();
        return redirect()->route('admin.index');
    }

    public function step1()
    {
        if ($redirect = $this->guardFresh()) {
            return $redirect;
        }
        return view('admin.onboarding.step1', ['entreprise' => Auth::user()->entreprise]);
    }

    public function step2()
    {
        if ($redirect = $this->guardFresh()) {
            return $redirect;
        }
        // Same people/teams pickers as the Create Task page.
        $employee_users = collect();
        $employees = employees::where('entreprise_id', Auth::user()->entreprise_id)
            ->where('archived', 0)
            ->orderBy('name', 'asc')
            ->get();
        foreach ($employees as $employee) {
            $employee_users = $employee_users->concat(
                User::where('id', $employee->user_id)->where('is_activate', 1)->get()
            );
        }
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        return view('admin.onboarding.step2', compact('employee_users', 'teams'));
    }

    /** Final "Finish": persist + show the completion screen. */
    public function complete()
    {
        $this->markComplete();
        return redirect()->route('admin.onboarding.done');
    }

    public function done()
    {
        $entreprise = Auth::user()->entreprise ?? null;
        if ($entreprise && empty($entreprise->onboarding_completed_at)) {
            return redirect()->route('admin.onboarding.welcome');
        }
        return view('admin.onboarding.done', ['user' => Auth::user()]);
    }
}
