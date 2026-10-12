<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\Entreprise;
use App\Models\Employees;
use App\Models\work_days_repports;
use App\Models\Role;
use App\Models\Permission;
use App\Models\EnterpriseRolePermission;
use App\Mail\AdminActivityAlertMail;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use DateTime;


class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Check Honeypot first
        if (!empty($request->input('website'))) {
            Log::info('Bot registration attempt blocked via Honeypot.');
            return redirect()->route('welcome');
        }

        // Turnstile Validation (skip in local environment)
        $turnstileSecret = config('services.turnstile.secret_key');
        if ($turnstileSecret && app()->environment('production')) {
            $turnstileResponse = $request->input('cf-turnstile-response');
            if (!$turnstileResponse) {
                return back()->withInput()->withErrors(['cf-turnstile-response' => __('messages.auth_turnstile_required')]);
            }

            $verify = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $turnstileSecret,
                'response' => $turnstileResponse,
                'remoteip' => $request->ip(),
            ]);

            if (!$verify->json('success')) {
                Log::warning('Turnstile verification failed for registration.', ['response' => $verify->json()]);
                return back()->withInput()->withErrors(['cf-turnstile-response' => __('messages.auth_turnstile_failed')]);
            }
        }

        $validated = $request->validate([
            "email" => "required|email|unique:users,email",
            "password" => ["required", "min:8", "confirmed"],
            "full_name" => "required|string|max:255",
        ], [
            'email.required' => __('messages.auth_val_email_required'),
            'email.email' => __('messages.auth_val_email_invalid'),
            'email.unique' => __('messages.auth_val_email_taken'),
            'password.required' => __('messages.auth_val_password_required'),
            'password.min' => __('messages.auth_val_password_min'),
            'password.confirmed' => __('messages.auth_val_password_confirmed'),
            'full_name.required' => __('messages.auth_val_name_required'),
        ]);

        

    // Check if the request contains the entreprise_image file
        if ($request->hasFile('entreprise_image')) {
            // Save the image to the public/images directory
            $image = $request->file('entreprise_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('entreprise_images'), $imageName);
        } else {
            // Use a default image if no image is provided
            $imageName = 'default.jpg'; // Change 'default.jpg' to your default image filename
        }

        // Create the entreprise (company details are completed later in onboarding)
        $entreprise = Entreprise::create([
            'name' => '',
            'is_activated' => true, // Set is_activated to true by default
            'duration' => now()->addMonths(6), // Set duration to 6 months by default
            'image' => $imageName,
            'type_compt' => null, // null means no plan selected yet
            // 15-day free trial period
            'trial_ends_at' => now()->addDays(15),
        ]);

        // Create the user and associate it with the entreprise
        // (timezone is collected later: onboarding or first chrono start)
        $user = new User([
            'name' => $validated['full_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->entreprise()->associate($entreprise);

        // Assign roles to the user
        $user->assignRole('Admin');
        $user->assignRole('SuperAdmin');

        $user->save();

        // Automatically assign permissions to SuperAdmin and Admin roles
        $allPermissions = Permission::all();
        $superAdminRole = Role::where('name', 'SuperAdmin')->first();
        $adminRole = Role::where('name', 'Admin')->first();

        if ($superAdminRole && $allPermissions->count() > 0) {
            // Assign all permissions to SuperAdmin role
            foreach ($allPermissions as $permission) {
                EnterpriseRolePermission::firstOrCreate([
                    'entreprise_id' => $entreprise->id,
                    'role_id' => $superAdminRole->id,
                    'permission_id' => $permission->id
                ]);
            }
        }

        if ($adminRole && $allPermissions->count() > 0) {
            // Assign all permissions except "Assign Superadmin role" to Admin role
            foreach ($allPermissions as $permission) {
                if ($permission->name !== 'Assign Superadmin role') {
                    EnterpriseRolePermission::firstOrCreate([
                        'entreprise_id' => $entreprise->id,
                        'role_id' => $adminRole->id,
                        'permission_id' => $permission->id
                    ]);
                }
            }
        }

            // Use a default image if no image is provided
            $imageName = NULL; 
        
        // Create the associated employee (same full name, no duplicate record)
        $employee = new Employees([
            'name' => $validated['full_name'],
            'age' => null,
            'cnss' => null,
            'phoneNumber' => null,
            'email' => $user->email,
            'password' => $user->password,
            'Date_Release' => null,
            'bankAccountNumber' => null,
            'salary' => 0,
            'cost_per_hour' => 0,
            'Cotisation_CNSS' => null,
            'workHours_limit' => 0,
            'Cotisation_AMO' => null,
            'entreprise_id' => $entreprise->id, 
            'image' => $imageName,
            'user_id' => $user->id,
        ]);

        $employee->save();

        // Get the current year and month
        $year = date('Y');
        $month = date('m');

        // Create a DateTime object for the first day of the month
        $date = new DateTime("$year-$month-01");

        // Get the total number of days in the month
        $daysInMonth = $date->format('t');

        // Initialize a counter for weekdays
        $weekdays = 0;

        // Loop through all days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            // Set the date to the current day
            $date->setDate($year, $month, $day);
            
            // Get the day of the week (1 for Monday, 7 for Sunday)
            $dayOfWeek = $date->format('N');
            
            // Check if the day is not Saturday (6) or Sunday (7)
            if ($dayOfWeek < 7) {
                $weekdays++;
            }
        }

        $work_days_repports = new work_days_repports([
            'work_days_per_month' => $weekdays,
            'days_worked' => $weekdays,
            'sick_days' => 0,
            'holidays' => 0,
            'days_of_unpaid_absence' => 0,
            'remaining_days_of_paid_leave' => 0,
            'number_of_days_of_paid_leave_acquired' => 1.5,
            'number_of_days_of_paid_leave_taken' => 0,
            'holiday_balance' =>  0,
            'remarque' => 0,
            'month' => date('m'),
            'year' => date('Y'),
            'employee_id' => $employee->id,
            'entreprise_id' => $entreprise->id,
        ]);

        $work_days_repports->save();


        Auth::login($user);

        // Send emails/notifications AFTER the response so a slow or failing
        // mail transport never blocks (or kills) the registration redirect.
        dispatch(function () use ($user, $entreprise) {
            // Fire Registered event (triggers SendEmailVerificationNotification)
            try {
                event(new Registered($user));
            } catch (\Throwable $e) {
                Log::error('Failed to send verification email during registration', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            // Send welcome notification to the user
            try {
                $user->notify(new WelcomeNotification($entreprise));
            } catch (\Throwable $e) {
                Log::warning('Failed to send welcome notification', ['error' => $e->getMessage()]);
            }

            // Notify admin that a new enterprise has registered
            // COMMENTED: admin alert emails to dessin@bureaumercier.com disabled
            /*
            try {
                Mail::to('dessin@bureaumercier.com')->send(new AdminActivityAlertMail(
                    'New enterprise registration',
                    'A new enterprise has registered on the platform.',
                    [
                        'entreprise_id' => $entreprise->id,
                        'entreprise_name' => $entreprise->name,
                        'admin_user_id' => $user->id,
                        'admin_email' => $user->email,
                        'timezone' => $user->timezone,
                    ]
                ));
            } catch (\Throwable $alertException) {
                Log::warning('Failed to send admin alert for new registration', ['error' => $alertException->getMessage()]);
            }
            */
        })->afterResponse();

        return redirect()->route('verification.notice')
            ->with('status', __('messages.auth_register_success'));
    }
}
