<?php

namespace App\Http\Controllers\superadmin;

use App\Models\User;
use App\Models\Entreprise;
use App\Models\Employees;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class userController extends Controller
{
    public function index()
    {
        $users = User::with('entreprise')
                ->where('is_employee', 0)
                ->get(); 

        $entreprises=Entreprise::all();
        foreach ($users as $user) {
            // Access the associated Entreprise name
            $entrepriseName = optional($user->entreprise)->name;
            $user->entreprise_name = $entrepriseName; // Add entreprise_name to the user object
        }

        return view('superadmin.users.index', compact('users', 'entreprises'));
    }
    public function create()
    {
        return view('superadmin.users.create');
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => "required",
            "email" => "required|email|unique:users,email",
            "password" => "required",
            "entreprise_name" => "required",
            'entreprise_image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', 
        ]);

        $hashedPassword = $validated['password'];

    // Check if the request contains the entreprise_image file
        if ($request->hasFile('entreprise_image')) {
            // Save the image to the public/images directory
            $image = $request->file('entreprise_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(asset('entreprise_images'), $imageName);
        } else {
            // Use a default image if no image is provided
            $imageName = 'default.jpg'; // Change 'default.jpg' to your default image filename
        }

        // Create the entreprise
        $entreprise = Entreprise::create([
            'name' => $validated['entreprise_name'],
            'is_activated' => true, // Set is_activated to true by default
            'duration' => now()->addMonths(6), // Set duration to 6 months by default
            'image' => $imageName, // Save the image filename to the database
        ]);

        // Create the user and associate it with the entreprise
        $user = new User([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $hashedPassword,
        ]);

        $user->entreprise()->associate($entreprise);
        $user->save();

        $validated = $request->validate([
            "phoneNumber" => "nullable",
            "CNSS" => "nullable",
            "bankAccountNumber" => "nullable",
            "salary" => "nullable",
            "cost_per_hour" => "nullable",
            "Cotisation_CNSS" => "nullable",
            "Cotisation_AMO" => "nullable",
            "workHours_limit" => "nullable", 
        ]);
        
            // Use a default image if no image is provided
            $imageName = NULL; 
        

        
        $workhourslimit = $request->workHours_limit;
        // Create the associated employee
        $employee = new Employees([
            'name' => $entreprise->name,
            'age' => null,
            'cnss' => $validated['CNSS'],
            'phoneNumber' => $validated['phoneNumber'],
            'email' => $user->email,
            'password' => $user->password,
            'Date_Release' => null,
            'bankAccountNumber' => $validated['bankAccountNumber'],
            'salary' => $validated['salary'],
            'cost_per_hour' => $validated['cost_per_hour'],
            'Cotisation_CNSS' => $validated['Cotisation_CNSS'],
            'workHours_limit' => $workhourslimit,
            'Cotisation_AMO' => $validated['Cotisation_AMO'],
            'entreprise_id' => $entreprise->id, 
            'image' => $imageName,
            'user_id' => $user->id,
        ]);

        $employee->save();

        // Get the current year and month
        $year = date('Y');
        $month = date('m');
        


        // Convert to DateTime object
        $monthplusone = DateTime::createFromFormat('Y-m', "$year-$month");

        // Add one month
        $monthplusone->modify('+1 month');

        // Get the new month in 'MM' format
        $nextMonth = $monthplusone->format('m');
        // Check if the next month is '01' and adjust the year accordingly
        if ($nextMonth === '01') {
            $nextYear = $year + 1;
        } else {
            $nextYear = $year;
        }

        // Create a DateTime object for the first day of the month
        $date = new DateTime("$nextYear-$nextMonth-01");

        // Get the total number of days in the month
        $daysInMonth = $date->format('t');

        // Initialize a counter for weekdays
        $weekdays = 0;

        // Loop through all days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            // Set the date to the current day
            $date->setDate($nextYear, $nextMonth, $day);
            
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
            'remaining_days_of_paid_leave' =>0,
            'number_of_days_of_paid_leave_acquired' => 1.5,
            'number_of_days_of_paid_leave_taken' => 0,
            'holiday_balance' => 0,
            'remarque' => '',
            'month' => $nextMonth,
            'year' => $nextYear,
            'employee_id' => $employee->id,
            'entreprise_id' => $enterpriseId,
        ]);

        $work_days_repports->save();
        

        return redirect()->route('superadmin.users.index')->with('message', 'User Added Successfully');
    }


    public function edit(User $user)
    {
        $role_name = Role::pluck('name')->toArray();
        return view('superadmin.users.edit', compact("role_name", 'user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate(["name" => "required", "email" => "required"]);
        $user->update($validated);
        return to_route('superadmin.users.index')->with('message', 'User Updated Successfully');
    }

    public function assign_roles(Request $request, User $user)
    {
        $user->syncRoles([]);

        $roles = $request->input('roles', []);
        foreach ($roles as $role) {
            $user->assignRole($role);
        }

        return view('superadmin.users.index')->with('message', 'Roles assigned successfully');
    }

    public function destroy(User $user)
    {
        $entrepriseId = $user->entreprise_id;

        // Find and delete the associated Entreprise
        if (!is_null($entrepriseId)) {
            $entreprise = Entreprise::find($entrepriseId);

            if (!is_null($entreprise)) {
                $entreprise->delete();
            }
        }

        $user->delete();

        return view('superadmin.users.index')->with('message', 'User and associated Entreprise Deleted Successfully');
    
    }
}
