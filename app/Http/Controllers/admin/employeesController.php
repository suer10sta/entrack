<?php

namespace App\Http\Controllers\admin;

use App\Models\User;
use App\Models\Tasks;
use App\Models\Teams;
use App\Models\Project;
use App\Models\Employees;
use App\Models\Entreprise;
use App\Models\team_members;
use App\Models\work_days_repports;
use App\Models\tasks_timer;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use App\Models\EnterpriseRolePermission;
use App\Models\Role as RoleModel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\WorkdaysReportMail;
use App\Mail\EmployeeSendingMail;
use Illuminate\Support\Str;
use App\Mail\EmployeeEmail;
use Illuminate\Validation\Rule;

class employeesController extends Controller
{

    public function index()
    {
        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $tasks= Tasks::where('user_id', Auth::user()->id)->get();
        $enterpriseId = Auth::user()->entreprise_id;

        $employees = employees::where('entreprise_id', $enterpriseId)->where('archived', 0)->orderBy('name', 'asc')->get();
        $entreprises = Entreprise::all();

        foreach ($employees as $employee) {
            $user_id = $employee->user_id;
            $user = User::find($user_id);

            $roles = $user->roles;
            $roleNames = $roles->pluck('name')->toArray(); 

            $permissions = [];

            foreach ($roles as $role) {
                $permissions = array_merge($permissions, $role->permissions->pluck('name')->toArray());
            }

            $employee->roles = $roleNames; 
            $employee->permissions = array_unique($permissions); 
        }

        return view('admin.employees.index', compact('employees','entreprises','tasks','teams','projects'));
    }
    public function create()
    {
        $permission_name = Permission::all()->pluck('name')->toArray();
        $roles = Role::where('name', '<>', 'super_admin')->get();
        return view('admin.employees.create', compact('roles', 'permission_name'));
    }


    public function store(Request $request)
    {
        // Get the authenticated admin's enterprise_id
        $enterpriseId = Auth::user()->entreprise_id;

        $entreprise = Auth::user()->entreprise;
        // 2. VÉRIFIER LA LIMITE AVANT DE CONTINUER
        if ($entreprise && !$entreprise->canAddMoreUsers()) {
            // 3. Si la limite est atteinte, arrêter et renvoyer une erreur
            return back()->with('error', 'Vous avez atteint la limite de ' . $entreprise->getUserLimit() . ' utilisateurs pour votre plan. Veuillez passer au plan Pro pour en ajouter davantage.');
        }

        $validated = $request->validate([
            "name" => "required",
            "email" => "required|unique:employees,email,NULL,id,archived,0",
            "password" => "required",
            "phoneNumber" => "nullable",
            "age" => "nullable",
            "CNSS" => "nullable",
            "bankAccountNumber" => "nullable",
            "salary" => "nullable",
            "cost_per_hour" => "nullable",
            "Cotisation_CNSS" => "nullable",
            "Cotisation_AMO" => "nullable",
            "Employee_Image" => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            "Date_Release" => "nullable",
            "workHours_limit" => "nullable", 
            "timezone" => "required",
            "contact_email" => "nullable",
        ]);

        $validated_user_email = $request->validate([
            "email" => [
                "required",
                Rule::unique('users', 'email')->where(function ($query) {
                    $query->whereNotIn('id', function ($sub) {
                        $sub->select('user_id')
                            ->from('employees')
                            ->where('archived', 1);
                    });
                }),
            ],
        ]);
        // Delete old users linked to archived employees with the same email
        $oldUsers = User::where('email', $validated_user_email['email'])->get();
        foreach ($oldUsers as $oldUser) {
            $archivedEmployee = Employees::where('user_id', $oldUser->id)->where('archived', 1)->first();
            if ($archivedEmployee) {
                $oldUser->delete();
            }
        }
        // Create the user and associate it with the enterprise
        $users = new User([
            'name' => $validated['name'],
            'email' => $validated_user_email['email'],
            'password' => $validated['password'],
            'entreprise_id' => $enterpriseId, // Associate with the admin's enterprise_id
            'timezone' => $validated['timezone'],
            'is_employee' => 1,
        ]);
        $users->save();

        // Check if the request contains the employee_image file
        if ($request->hasFile('Employee_Image')) {
            // Generate image name
            $image = $request->file('Employee_Image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            
            // Store the image in DO Spaces
            Storage::disk('public')->put(
                'employee_images/' . $imageName, 
                file_get_contents($image),
                'public'
            );
        } else {
            // Use a default image if no image is provided
            $imageName = NULL;
        }

        
        $workhourslimit = $request->workHours_limit;
        // Create the associated employee
        $employee = new Employees([
            'name' => $validated['name'],
            'age' => $validated['age'],
            'cnss' => $validated['CNSS'],
            'phoneNumber' => $validated['phoneNumber'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'Date_Release' => $validated['Date_Release'],
            'bankAccountNumber' => $validated['bankAccountNumber'],
            'salary' => $validated['salary'],
            'cost_per_hour' => $validated['cost_per_hour'],
            'Cotisation_CNSS' => $validated['Cotisation_CNSS'],
            'workHours_limit' => $workhourslimit,
            'Cotisation_AMO' => $validated['Cotisation_AMO'],
            'entreprise_id' => $enterpriseId, // Associate with the admin's enterprise_id
            'image' => $imageName,
            'contact_email' => $validated['contact_email'],
        ]);

        $users->syncRoles([]);

        $roles = $request->input('roles', []);
        foreach ($roles as $role) {
            $users->assignRole($role);
        }

        // Automatically assign permissions based on roles
        $this->assignRolePermissions($roles, $enterpriseId);

        // Associate the user_id with the newly created user
        $employee->user_id = $users->id;
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
            'days_worked' => 0,
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
            'entreprise_id' => $enterpriseId,
        ]);

        $work_days_repports->save();

        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.employees.index')->with('message', 'Employee Added Successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.employees.index')->with('message', 'Employee Added Successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.employees.index')->with('message', 'Employee Added Successfully');
        }
    }

    public function edit(Employees $employee)
    {
        $user_id = $employee->user_id;
        $user = User::find($user_id);
        $role_name = Role::where('name', '<>', 'super_admin')->pluck('name')->toArray();
        return view('admin.employees.edit', compact('employee', 'role_name', 'user'));
    }

    public function update(Request $request, Employees $employee)
    {
        
        $user = $employee->user;

        $roles = $request->input('roles', []);
        $timezone = $request->input('timezone');

        $user->syncRoles($roles);
        
        // Automatically assign permissions based on roles
        $this->assignRolePermissions($roles, $user->entreprise_id);
        
        // Update timezone if provided
        if ($timezone) {
            $user->timezone = $timezone;
            $user->save();
        }

        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.employees.index')->with('message', 'Employee Updated Successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.employees.index')->with('message', 'Employee Updated Successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.employees.index')->with('message', 'Employee Updated Successfully');
        }
    }

    public function updateEmployee(Request $request)
    {
        $Employee = Employees::find($request->id);
        $work_days_repports = work_days_repports::where('employee_id', $Employee->id)->where('month', date('m'))->where('year', date('Y'))->first();

        // If the request is to update the timezone, update the linked user's timezone
        if ($request->column === 'timezone') {
            $user = $Employee->user;
            if ($user) {
                $user->timezone = $request->content;
                $user->save();
            }
            return response()->json(['success'=>'Employee timezone updated successfully.']);
        }

        if($request->column == 'salary')
        {
            $Employee->salary = $request->content;
        }
        if($request->column == 'cost_per_hour')
        {
            $Employee->cost_per_hour = $request->content;
            $employee_tasks_timers = tasks_timer::where('employee_id', $Employee->id)->whereNotNull('end_date')->get();
            foreach($employee_tasks_timers as $task_timer){
                $task_timer->cost = $request->content * ($task_timer->duration / 60);
                $task_timer->save();
            }
        }
        if($request->column == 'workHours_limit')          
        {
            $Employee->workHours_limit = $request->content;
        }
        if($request->column == 'email')
        {
            // Check if email already exists for other users
            $emailExists = User::where('email', $request->content)
                ->where('id', '!=', $Employee->user_id)
                ->exists();

            if ($emailExists) {
                return response()->json([
                    'success' => false,
                    'error' => 'This email is already taken.'
                ], 422);
            }

            $user = $Employee->user;
            $user->email = $request->content;
            $user->save();
        }
        if($request->column == 'name')
        {
            $user = $Employee->user;
            $user->name = $request->content;
            $user->save();
        }
        if($request->column == 'password')
        {
            $user = $Employee->user;
            $user->password = Hash::make($request->content);
            $user->save();
        }

        $Employee->{$request->column} = $request->content;
        $Employee->save();
        

        return response()->json(['success'=>'Employee updated successfully.']);
    }

    public function show(Employees $employee)
{
    $user_id = $employee->user_id;
    $user = User::find($user_id);
    $role_name = Role::where('name', '<>', 'super_admin')->pluck('name')->toArray();

    // [FIX 1] Use firstOrNew. 
    // If the report exists, it loads it. If not, it creates a new empty instance so we can save data to it.
    // NOTE: entreprise_id must always be set, otherwise the row becomes invisible
    // in workDaysRepports (filtered on entreprise_id) and blocks nextMonthRepports (which skips on existence).
    $work_days_repports = work_days_repports::firstOrNew([
        'employee_id' => $employee->id,
        'month' => date('m'),
        'year' => date('Y')
    ]);

    if (!$work_days_repports->exists) {
        $work_days_repports->entreprise_id = $employee->entreprise_id;
    } elseif (empty($work_days_repports->entreprise_id)) {
        // Auto-repair legacy rows created with entreprise_id = 0/NULL
        $work_days_repports->entreprise_id = $employee->entreprise_id;
    }

    $currentYear = strval(Carbon::now()->year);
    $currentMonth = str_pad(Carbon::now()->month, 2, '0', STR_PAD_LEFT);

    $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth();
    $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth();

    $totalMinutesForEmployee = tasks_timer::where('employee_id', $employee->id)
        ->whereBetween('start_date', [$startDate, $endDate])
        ->whereNotNull('end_date')
        ->sum('duration');

    // convert minutes to hours
    $totalHours = number_format($totalMinutesForEmployee / 60, 2);

    // First calculate days
    $totalDays = $totalHours / 8.5;
    
    // Round up any decimal value to the next whole number
    $daysWorked = ceil($totalDays);

    // Calculate the difference to add to hours_accumulate
    $diff = $totalDays - $daysWorked;

    // Convert the difference from days to hours (assuming 1 day = 8.5 hours)
    $hoursToAdd = $diff * 8.5;

    // Format hoursToAdd to two decimal places
    $hoursToAdd = number_format($hoursToAdd, 2, '.', '');

    // Add the hours to hours_accumulate only if daysWorked is not equal to totalDays
    if ($daysWorked !== $totalDays && $daysWorked < $totalDays) {
        // [FIX 2] Safety check for previous month.
        // The original code would crash here if this is the employee's very first month.
        $previous_report = work_days_repports::where('employee_id', $employee->id)
            ->where('month', date('m', strtotime('-1 month')))
            ->where('year', date('Y', strtotime('-1 month')))
            ->first();

        // If previous report exists, use its hours. If not, default to 0.
        $previous_month_hours_accumulate = $previous_report ? $previous_report->hours_accumulate : 0;

        $work_days_repports->hours_accumulate = $previous_month_hours_accumulate + $hoursToAdd;
    }

    // Now this line is safe because $work_days_repports is guaranteed to be an object
    $work_days_repports->days_worked = $daysWorked;
    $work_days_repports->save();
    
    return view('admin.employees.view_employee', compact('employee', 'role_name', 'user', 'work_days_repports', 'totalHours'));
}

    public function destroy(Employees $employee)
    {
        $user_id = $employee->user_id;

        // Find and delete the associated Entreprise
        if (!is_null($user_id)) {
            $user = User::find($user_id);

            if (!is_null($user)) {
                $user->delete();
            }
        }

        $team_member= team_members::where('employee_id', $employee->id)->get();
        foreach ($team_member as $team_members) {
            $team_members->delete();
        }
        $employee->delete();

        return back()->with('message', 'Employee Deleted Successfully');
    }

    public function assign_roles(Request $request, Employees $employee)
    {

        $user_id = $employee->user_id;
        $user = User::find($user_id);

        $roles = $request->input('roles', []);

        // Revoke all roles for the user
        $user->syncRoles([]);

        // Assign the selected roles
        $user->assignRole($roles);

        // Automatically assign permissions based on roles
        $this->assignRolePermissions($roles, $user->entreprise_id);

        return back()->with('message', 'Roles assigned successfully');

    }

    public function desactivate(Employees $employee)
    {
        $employee_id = $employee->user_id;
        $user = User::find($employee_id);

        $user->is_activate = false;
        $user->save();

        return redirect()->back()->with('message', 'employee deactivated successfully');
    }

    public function activate(Employees $employee)
    {
        $employee_id = $employee->user_id;
        $user = User::find($employee_id);

        $user->is_activate = true;
        $user->save();

        return redirect()->back()->with('message', 'employee activated successfully');
    }

    public function downloadEmployeesRepports()
    {
        $employees = Employees::where('entreprise_id', auth()->user()->entreprise_id)->where('archived', 0)->orderBy('name', 'asc')->get();
        $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->orderBy('name', 'asc')->first();
        $pdf = PDF::loadView('admin.employees.downloadRepports', [
            'employees' => $employees,
            'entreprise' => $entreprise,
        ]); 
        $pdf->setPaper('a4', 'landscape'); // Set the paper to A4 and orientation to landscape

        return $pdf->stream('employeesRepports.pdf');
    }

    
    public function updateWorkdaysRepport(Request $request)
    {
        $work_days_repports = work_days_repports::find($request->id);
        if($request->column == 'days_worked')
        {
            $work_days_repports->{$request->column} = $request->content;
            $work_days_repports->days_worked = $request->content;
        }
        elseif($request->column == 'number_of_days_of_paid_leave_taken')
        {
            $work_days_repports->{$request->column} = $request->content;
            $work_days_repports->holiday_balance = $work_days_repports->remaining_days_of_paid_leave + $work_days_repports->number_of_days_of_paid_leave_acquired - $work_days_repports->number_of_days_of_paid_leave_taken;
        }
        elseif($request->column == 'remaining_days_of_paid_leave')
        {
            $work_days_repports->{$request->column} = $request->content;
            $work_days_repports->holiday_balance = $work_days_repports->remaining_days_of_paid_leave + $work_days_repports->number_of_days_of_paid_leave_acquired - $work_days_repports->number_of_days_of_paid_leave_taken;
        }
        elseif($request->column == 'days_to_recover')
        {
            $work_days_repports->{$request->column} = $request->content;
            //$work_days_repports->days_worked_gross = $work_days_repports->days_worked_gross + $request->content;
        }
        else{
            $work_days_repports->{$request->column} = $request->content;
            $work_days_repports->days_worked = $work_days_repports->work_days_per_month -  $work_days_repports->days_of_unpaid_absence;
        }
        
        $work_days_repports->save();   

        return response()->json(['success'=>'work days repports updated successfully.']);
    }

    
    public function workDaysReportsIndex()
    {
        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $tasks= Tasks::where('user_id', Auth::user()->id)->get();
        // Get the authenticated user's enterprise_id
        $enterpriseId = Auth::user()->entreprise_id;

        // Retrieve only employees with the same enterprise_id
        $employees = employees::where('entreprise_id', $enterpriseId)->where('archived', 0)->orderBy('name', 'asc')->get();
        $entreprises = Entreprise::all();

        $currentYear = strval(Carbon::now()->year);
        $currentMonth = str_pad(Carbon::now()->month, 2, '0', STR_PAD_LEFT);

        $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth();
        //in this collection there is employee_id so to get his name we need to do a join with the employees table
        $workDaysRepports = work_days_repports::where('work_days_repports.entreprise_id', $enterpriseId)
        ->where('year', $currentYear)
        ->where('month', $currentMonth)
        ->join('employees', 'work_days_repports.employee_id', '=', 'employees.id')
        ->orderBy('employees.name', 'asc')
        ->get(['work_days_repports.*', 'employees.name']);

        foreach ($workDaysRepports as $report) {
            $totalMinutesForEmployee = tasks_timer::where('employee_id', $report->employee_id)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->whereNotNull('end_date')
            ->sum('duration');

            //convers minutes to hours
            $totalHours = $totalMinutesForEmployee / 60;

            // First calculate days
            $totalDays = $totalHours / 8.5;
            
            // Round up any decimal value to the next whole number
            $daysWorked = ceil($totalDays);

            // Calculate the difference to add to hours_accumulate
            $diff = $totalDays - $daysWorked;

            // Convert the difference from days to hours (assuming 1 day = 8 hours)
            $hoursToAdd = $diff * 8.5;

            // Format hoursToAdd to two decimal places
            $hoursToAdd = number_format($hoursToAdd, 2, '.', '');

            // Add the hours to hours_accumulate only if daysWorked is not equal to totalDays
            if ($daysWorked !== $totalDays && $daysWorked < $totalDays) {
                $previous_month_hours_accumulate = work_days_repports::where('employee_id', $report->employee_id)->where('month', date('m', strtotime('-1 month')))->where('year', date('Y', strtotime('-1 month')))->first()->hours_accumulate;
                $report->hours_accumulate = $previous_month_hours_accumulate + $hoursToAdd;
            }
            
            $report->days_worked = $daysWorked;
            $report->save();
        }


        return view('admin.workDaysRepports.index', compact('employees','entreprises','tasks','teams','projects', 'workDaysRepports', 'currentMonth', 'currentYear', 'startDate', 'endDate'));
    }

    public function filterReports(Request $request)
{
    $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)->where(function($query) {
        $query->whereNull('AcceptedDevisProject')
              ->orWhere('AcceptedDevisProject', '=', 1);
    })
    ->where('archived', 0)
    ->get();
    $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
    $tasks= Tasks::where('user_id', Auth::user()->id)->get();
    // Get the authenticated user's enterprise_id
    $enterpriseId = Auth::user()->entreprise_id;

    // Retrieve only employees with the same enterprise_id
    $employees = employees::where('entreprise_id', $enterpriseId)->where('archived', 0)->orderBy('name', 'asc')->get();
    $entreprises = Entreprise::all();


    $currentYear = strval($request->input('year'));
    $currentMonth = str_pad($request->input('month'), 2, '0', STR_PAD_LEFT);
    $enterpriseId = Auth::user()->entreprise_id;

    $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth();
    $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth();

    $workDaysRepports = work_days_repports::where('work_days_repports.entreprise_id', $enterpriseId)
    ->where('year', $currentYear)
    ->where('month', $currentMonth)
    ->join('employees', 'work_days_repports.employee_id', '=', 'employees.id')
    ->orderBy('employees.name', 'asc')
    ->get(['work_days_repports.*', 'employees.name']);

    foreach ($workDaysRepports as $report) {
        $totalMinutesForEmployee = tasks_timer::where('employee_id', $report->employee_id)
        ->whereBetween('start_date', [$startDate, $endDate])
        ->whereNotNull('end_date')
        ->sum('duration');

        //convers minutes to hours
        $totalHours = $totalMinutesForEmployee / 60;

        // First calculate days
        $totalDays = $totalHours / 8.5;
        
        // Round up any decimal value to the next whole number
        $daysWorked = ceil($totalDays);

        // Calculate the difference to add to hours_accumulate
        $diff = $totalDays - $daysWorked;

        // Convert the difference from days to hours (assuming 1 day = 8 hours)
        $hoursToAdd = $diff * 8.5;

        // Format hoursToAdd to two decimal places
        $hoursToAdd = number_format($hoursToAdd, 2, '.', '');

        // Add the hours to hours_accumulate only if daysWorked is not equal to totalDays
        //if ($daysWorked !== $totalDays && $daysWorked < $totalDays) {
        //    $previous_month_hours_accumulate = work_days_repports::where('employee_id', $report->employee_id)->where('month', date('m', strtotime('-1 month')))->where('year', date('Y', strtotime('-1 month')))->first()->hours_accumulate;
        //    $report->hours_accumulate = $previous_month_hours_accumulate + $hoursToAdd;
        //}
        
        $report->days_worked = $daysWorked;
        $report->save();
    }

    return view('admin.workDaysRepports.index', compact('employees','entreprises','tasks','teams','projects', 'workDaysRepports', 'currentMonth', 'currentYear', 'startDate', 'endDate'));


}

public function downloadworkDaysRepports(Request $request)
{
    $query = work_days_repports::where('work_days_repports.entreprise_id', auth()->user()->entreprise_id);

    if ($request->has(['year', 'month'])) {
        $year = strval($request->year);
        $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
        $query->where('work_days_repports.year', $year)
              ->where('work_days_repports.month', $month);
    } else {
        $year = date('Y');
        $month = date('m');
    }

    // Filter by selected employees if provided
    if ($request->has('employees')) {
        $selectedEmployees = explode(',', $request->employees);
        $query->whereIn('employee_id', $selectedEmployees);
    }

    $workDaysRepports = $query->join('employees', 'work_days_repports.employee_id', '=', 'employees.id')
        ->orderBy('employees.name', 'asc')
        ->get(['work_days_repports.*', 'employees.name']);

   // Calculate start and end dates for the month
   $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
   $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

   // Update each work days report with task duration
   foreach ($workDaysRepports as $report) {
       $totalMinutesForEmployee = tasks_timer::where('employee_id', $report->employee_id)
       ->whereBetween('start_date', [$startDate, $endDate])
       ->whereNotNull('end_date')
       ->sum('duration');

       //convers minutes to hours
       $totalHours = $totalMinutesForEmployee / 60;

       // First calculate days
        $totalDays = $totalHours / 8.5;
        
        // Round up any decimal value to the next whole number
        $daysWorked = ceil($totalDays);

        // Calculate the difference to add to hours_accumulate
        $diff = $totalDays - $daysWorked;

        // Convert the difference from days to hours (assuming 1 day = 8 hours)
        $hoursToAdd = $diff * 8.5;

         // Format hoursToAdd to two decimal places
         $hoursToAdd = number_format($hoursToAdd, 2, '.', '');

         // Add the hours to hours_accumulate only if daysWorked is not equal to totalDays
         //if ($daysWorked !== $totalDays && $daysWorked < $totalDays) {
         //    $previous_month_hours_accumulate = work_days_repports::where('employee_id', $report->employee_id)->where('month', date('m', strtotime('-1 month')))->where('year', date('Y', strtotime('-1 month')))->first()->hours_accumulate;
         //    $report->hours_accumulate = $previous_month_hours_accumulate + $hoursToAdd;
         //}      

       $report->days_worked = $daysWorked;
       $report->save();
   }

    $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();
    
    $monthNumber = $month;
    $dateObj = DateTime::createFromFormat('!m', $monthNumber);
    $monthName = $dateObj->format('F');

    // Get excluded columns from the request
    $excludedColumns = $request->has('excluded_columns') ? explode(',', $request->excluded_columns) : [];

    // Check if preview is requested
    if ($request->has('preview') && $request->preview == 1) {
        return $this->previewWorkdaysReport($workDaysRepports, $entreprise, $monthName, $year, $month, $startDate, $endDate, $excludedColumns, $request->employees ?? '');
    }

    // Generate the PDF with the excluded columns
    $pdf = PDF::loadView('admin.workDaysRepports.downloadRepports', [
        'workDaysRepports' => $workDaysRepports,
        'entreprise' => $entreprise,
        'month' => $monthName,
        'year' => $year,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'excludedColumns' => $excludedColumns, // Pass excluded columns to the view
    ]);
    
    $pdf->setPaper('a4', 'landscape');
    return $pdf->stream('workDaysRepports.pdf');
}

/**
 * Preview the workdays report before sending via email
 */
public function previewWorkdaysReport($workDaysRepports, $entreprise, $monthName, $year, $month, $startDate, $endDate, $excludedColumns, $selectedEmployees)
{
    return view('admin.workDaysRepports.preview', [
        'workDaysRepports' => $workDaysRepports,
        'entreprise' => $entreprise,
        'monthName' => $monthName,
        'year' => $year,
        'month' => $month,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'excludedColumns' => is_array($excludedColumns) ? implode(',', $excludedColumns) : $excludedColumns,
        'employees' => $selectedEmployees
    ]);
}

/**
 * Generate PDF for iframe preview
 */
public function generatePdfForPreview(Request $request)
{
    $query = work_days_repports::where('work_days_repports.entreprise_id', auth()->user()->entreprise_id);

    if ($request->has(['year', 'month'])) {
        $year = strval($request->year);
        $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
        $query->where('work_days_repports.year', $year)
              ->where('work_days_repports.month', $month);
    } else {
        $year = date('Y');
        $month = date('m');
    }

    // Filter by selected employees if provided
    if ($request->has('employees') && !empty($request->employees)) {
        $selectedEmployees = explode(',', $request->employees);
        $query->whereIn('employee_id', $selectedEmployees);
    }

    $workDaysRepports = $query->join('employees', 'work_days_repports.employee_id', '=', 'employees.id')
        ->orderBy('employees.name', 'asc')
        ->get(['work_days_repports.*', 'employees.name']);

    $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();
    
    $monthNumber = $month;
    $dateObj = DateTime::createFromFormat('!m', $monthNumber);
    $monthName = $dateObj->format('F');

    // Get excluded columns from the request
    $excludedColumns = $request->has('excluded_columns') ? explode(',', $request->excluded_columns) : [];

    // Generate the PDF with the excluded columns
    $pdf = PDF::loadView('admin.workDaysRepports.downloadRepports', [
        'workDaysRepports' => $workDaysRepports,
        'entreprise' => $entreprise,
        'month' => $monthName,
        'year' => $year,
        'startDate' => Carbon::createFromDate($year, $month, 1)->startOfMonth(),
        'endDate' => Carbon::createFromDate($year, $month, 1)->endOfMonth(),
        'excludedColumns' => $excludedColumns,
    ]);
    
    $pdf->setPaper('a4', 'landscape');
    return $pdf->stream('workDaysRepports.pdf');
}

/**
 * Send workdays report via email
 */
public function sendWorkdaysReportEmail(Request $request)
{
    $request->validate([
        'year' => 'required',
        'month' => 'required',
        'emails' => 'required',
        'subject' => 'required',
        'body' => 'required',
        'attachments.*' => 'nullable|file|max:20480',
    ]);

    $year = $request->year;
    $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
    $emails = array_values(array_filter(array_map('trim', explode(',', $request->emails)), function ($e) {
        return filter_var($e, FILTER_VALIDATE_EMAIL);
    }));
    $subject = $request->subject;
    $body = $request->body;
    $selectedEmployees = $request->employees;
    $excludedColumns = $request->excluded_columns;
    
    // Collect optional extra attachments
    $extraAttachments = $request->file('attachments');
    if (empty($extraAttachments)) {
        $extraAttachments = [];
    } elseif (!is_array($extraAttachments)) {
        $extraAttachments = [$extraAttachments];
    }
    
    // Save emails for future use if requested
    if ($request->has('saveEmails') && $request->saveEmails == 1) {
        $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();
        $entreprise->workdays_emails = $request->emails;
        $entreprise->save();
    }

    // Generate the PDF file
    $query = work_days_repports::where('work_days_repports.entreprise_id', auth()->user()->entreprise_id);
    $query->where('work_days_repports.year', $year)
          ->where('work_days_repports.month', $month);

    // Filter by selected employees if provided
    if (!empty($selectedEmployees)) {
        $query->whereIn('employee_id', explode(',', $selectedEmployees));
    }

    $workDaysRepports = $query->join('employees', 'work_days_repports.employee_id', '=', 'employees.id')
        ->orderBy('employees.name', 'asc')
        ->get(['work_days_repports.*', 'employees.name']);

    $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();
    
    $dateObj = DateTime::createFromFormat('!m', $month);
    $monthName = $dateObj->format('F');

    // Generate the PDF with the excluded columns
    $pdf = PDF::loadView('admin.workDaysRepports.downloadRepports', [
        'workDaysRepports' => $workDaysRepports,
        'entreprise' => $entreprise,
        'month' => $monthName,
        'year' => $year,
        'startDate' => Carbon::createFromDate($year, $month, 1)->startOfMonth(),
        'endDate' => Carbon::createFromDate($year, $month, 1)->endOfMonth(),
        'excludedColumns' => !empty($excludedColumns) ? explode(',', $excludedColumns) : [],
    ]);
    
    $pdf->setPaper('a4', 'landscape');
    
    // Save PDF to temporary file
    $filename = 'workdays_report_' . $month . '_' . $year . '_' . Str::random(8) . '.pdf';
    $tempPath = storage_path('app/public/temp/' . $filename);
    
    // Create directory if it doesn't exist
    if (!File::exists(storage_path('app/public/temp'))) {
        File::makeDirectory(storage_path('app/public/temp'), 0755, true);
    }
    
    $pdf->save($tempPath);
    
    // Send one email with CC so all recipients are visible to each other
    if (count($emails) === 0) {
        // Clean up temp file before returning
        File::delete($tempPath);
        return redirect()->back()->with('error', 'No valid recipient emails provided.');
    }

    $primaryRecipient = $emails[0];
    $ccRecipients = array_slice($emails, 1);

    Mail::to($primaryRecipient)
        ->cc($ccRecipients)
        ->send(new WorkdaysReportMail($subject, $body, $tempPath, $month, $year, $extraAttachments));
    
    // Delete temporary file after sending
    File::delete($tempPath);
    
    return redirect()->route('admin.workDaysReports.index')
        ->with('message', 'Workdays report has been sent successfully to ' . implode(', ', $emails));
}

public function nextMonthRepports(Request $request)
{
    $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)->where(function($query) {
        $query->whereNull('AcceptedDevisProject')
              ->orWhere('AcceptedDevisProject', '=', 1);
    })
    ->where('archived', 0)
    ->get();
    $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
    $tasks= Tasks::where('user_id', Auth::user()->id)->get();

    $entreprises = Entreprise::all();

    $currentYear = strval(Carbon::now()->year);
    $currentMonth = str_pad(Carbon::now()->month, 2, '0', STR_PAD_LEFT);

    $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth();
    $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth();

    // Get the authenticated user's enterprise_id
    $enterpriseId = Auth::user()->entreprise_id;
    $employees = employees::where('entreprise_id', $enterpriseId)->where('archived', 0)->orderBy('name', 'asc')->get();

    $year = $request->input('currentYear', $currentYear);
    $month = str_pad($request->input('currentMonth', $currentMonth), 2, '0', STR_PAD_LEFT);

    // Calculate next month and year
    if ($month == 12) {
        $nextMonth = '01';
        $nextYear = $year + 1;
    } else {
        $nextMonth = str_pad($month + 1, 2, '0', STR_PAD_LEFT);
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
        if ($dayOfWeek < 6) {
            $weekdays++;
        }
    }

    foreach ($employees as $employee) {
        $existingReport = work_days_repports::where('employee_id', $employee->id)
            ->where('year', $nextYear)
            ->where('month', $nextMonth)
            ->first();

        if ($existingReport) {
            continue;
        }

        $previousWorkdaysRepport = work_days_repports::where('employee_id', $employee->id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        $previousBalance = $previousWorkdaysRepport ? $previousWorkdaysRepport->holiday_balance : 0;

        $work_days_repports = new work_days_repports([
            'work_days_per_month' => $weekdays,
            'days_worked' => 0,
            'sick_days' => 0,
            'holidays' => 0,
            'days_of_unpaid_absence' => 0,
            'remaining_days_of_paid_leave' => $previousBalance,
            'number_of_days_of_paid_leave_acquired' => 1.5,
            'number_of_days_of_paid_leave_taken' => 0,
            'holiday_balance' => $previousBalance + 1.5,
            'remarque' => '',
            'month' => $nextMonth,
            'year' => $nextYear,
            'employee_id' => $employee->id,
            'entreprise_id' => $enterpriseId,
        ]);

        $work_days_repports->save();
    }

    $workDaysRepports = work_days_repports::where('work_days_repports.entreprise_id', $enterpriseId)
    ->where('year', $nextYear)
    ->where('month', $nextMonth)
    ->join('employees', 'work_days_repports.employee_id', '=', 'employees.id')
    ->orderBy('employees.name', 'asc')
    ->get(['work_days_repports.*', 'employees.name']);
    
    $currentMonth = $nextMonth;
    $currentYear = $nextYear;
    
    return view('admin.workDaysRepports.index', compact('employees','entreprises','tasks','teams','projects', 'workDaysRepports', 'currentMonth', 'currentYear', 'startDate', 'endDate'));
}

public function archiveEmployee($id){
    $employee = employees::find($id);
    $employee->archived = 1;
    $employee->save();
    if(Auth::user()->hasRole('Admin')){
        return redirect()->route('admin.employees.index')->with('message', 'Employee archived successfully.');
    }
    if(Auth::user()->hasRole('Responsable de projet')){
        return redirect()->route('Responsable_de_projet.employees.index')->with('message', 'Employee archived successfully.');
    }
    if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
        return redirect()->route('Responsable_gestionnaire.employees.index')->with('message', 'Employee archived successfully.');
    }
}

public function unarchiveEmployee($id){
    $employee = employees::find($id);
    $employee->archived = 0;
    $employee->save();
    if(Auth::user()->hasRole('Admin')){
        return redirect()->route('admin.employees.index')->with('message', 'Employee unarchived successfully.');
    }
    if(Auth::user()->hasRole('Responsable de projet')){
        return redirect()->route('Responsable_de_projet.employees.index')->with('message', 'Employee unarchived successfully.');
    }
    if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
        return redirect()->route('Responsable_gestionnaire.employees.index')->with('message', 'Employee unarchived successfully.');
    }
}

public function employeeArchiveIndex(){
    $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)->where(function($query) {
        $query->whereNull('AcceptedDevisProject')
              ->orWhere('AcceptedDevisProject', '=', 1);
    })
    ->where('archived', 0)
    ->get();
    $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
    $tasks= Tasks::where('user_id', Auth::user()->id)->get();
    // Get the authenticated user's enterprise_id
    $enterpriseId = Auth::user()->entreprise_id;

    // Retrieve only employees with the same enterprise_id
    $employees = employees::where('entreprise_id', $enterpriseId)->where('archived', 1)->orderBy('name', 'asc')->get();
    $entreprises = Entreprise::all();

    foreach ($employees as $employee) {
        $user_id = $employee->user_id;
        $user = $user_id ? User::find($user_id) : null;

        if (!$user) {
            $employee->roles = [];
            $employee->permissions = [];
            continue;
        }

        $roles = $user->roles;
        $roleNames = $roles->pluck('name')->toArray(); // Extract role names

        $permissions = [];

        foreach ($roles as $role) {
            // Accumulate permissions for each role
            $permissions = array_merge($permissions, $role->permissions->pluck('name')->toArray());
        }

        $employee->roles = $roleNames; // Add roles to the user object
        $employee->permissions = array_unique($permissions); // Remove duplicates and assign unique permissions
    }

    return view('admin.employees.archive', compact('employees','entreprises','tasks','teams','projects'));
}

public function sendEmail(Request $request)
{
    $request->validate([
        'subject' => 'required|string|max:255',
        'body' => 'required|string',
        'attachments.*' => 'nullable|file|max:10240', // Max 10MB per file
    ]);

    $employee = employees::findOrFail($request->employee_id);
    
    // Normalize attachments to always be an array
    $employeeAttachments = $request->file('attachments');
    \Log::info('Controller - Attachments type:', ['type' => gettype($employeeAttachments)]);
    \Log::info('Controller - Attachments value:', ['value' => $employeeAttachments]);
    if (empty($employeeAttachments)) {
        $employeeAttachments = [];
    } elseif (!is_array($employeeAttachments)) {
        $employeeAttachments = [$employeeAttachments];
    }
    \Log::info('Controller - Attachments after normalization:', ['value' => $employeeAttachments]);

    try {
        Mail::to($employee->contact_email)
            ->send(new EmployeeEmail(
                $request->subject,
                $request->body,
                $employeeAttachments
            ));

        return response()->json(['message' => 'Email sent successfully']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to send email: ' . $e->getMessage()], 500);
    }
}

/**
 * Automatically assign permissions to roles based on role type
 */
private function assignRolePermissions(array $roleNames, $entrepriseId)
{
    foreach ($roleNames as $roleName) {
        $role = RoleModel::where('name', $roleName)->first();
        
        if (!$role) {
            continue;
        }

        $permissionsToAssign = [];

        // Define permissions for each role
        switch ($roleName) {
            case 'Employee':
            case 'employee':
                $permissionsToAssign = ['Planning', 'Pointage', 'Pointage reports'];
                break;
            
            case 'Responsable de projet':
                $permissionsToAssign = [
                    'Manage workDaysReports',
                    'Planning',
                    'Pointage',
                    'Add task',
                    'Update task',
                    'View task',
                    'View project'
                ];
                break;
            
            case 'Responsable de projet et gestionnaire':
                $permissionsToAssign = [
                    'Add task',
                    'Billings',
                    'Manage workDaysReports',
                    'Planning',
                    'Pointage',
                    'Update task',
                    'View project',
                    'View task'
                ];
                break;
        }

        // Assign permissions to the role for this enterprise
        if (!empty($permissionsToAssign)) {
            foreach ($permissionsToAssign as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                
                if ($permission) {
                    EnterpriseRolePermission::firstOrCreate([
                        'entreprise_id' => $entrepriseId,
                        'role_id' => $role->id,
                        'permission_id' => $permission->id
                    ]);
                }
            }
        }
    }
}

}
