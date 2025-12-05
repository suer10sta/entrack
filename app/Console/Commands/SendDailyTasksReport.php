<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tasks;
use App\Models\tasks_timer;
use App\Models\Project;
use App\Models\Employees;
use App\Models\Client;
use App\Models\Entreprise;
use App\Models\devis_history;
use App\Models\invoices_history;
use PDF;
use Mail;
use Carbon\Carbon;

class SendDailyTasksReport extends Command
{
    protected $signature = 'reports:send-daily-tasks';
    protected $description = 'Generate and send all daily reports';


   

    public function handle()
    {
        $this->info('Starting to generate all reports...');

        // Get all enterprises id=43 
        $enterprise = Entreprise::where('id', 43)->first();

        $RECIPIENT_EMAIL = $enterprise->emails_backup;

        //if ($enterprise->isEmpty()) {
        //    $this->warn('No enterprises found.');
        //    return;
        //}

        //foreach ($enterprises as $enterprise) {
            $this->info("Processing enterprise: {$enterprise->name}");

            try {
                $attachments = [];

                // Generate Tasks Report
                $tasks = Tasks::where('entreprise_id', $enterprise->id)->orderBy('project_id', 'asc')->get();
                    $tasksPdf = PDF::loadView('admin.tasks.downloadRepports', [
                        'tasks' => $tasks,
                        'entreprise' => $enterprise,
                    ]);
                    $tasksFilename = 'tasks_report_' . Carbon::now()->format('d-m-Y') . '.pdf';
                    $tasksPdfPath = storage_path('app/public/' . $tasksFilename);
                    $tasksPdf->save($tasksPdfPath);
                    $attachments[] = [
                        'path' => $tasksPdfPath,
                        'name' => $tasksFilename
                    ];

                // Generate Projects Report
                $projects = Project::where('entreprise_id', $enterprise->id)->where(function($query) {
                    $query->whereNull('AcceptedDevisProject')
                          ->orWhere('AcceptedDevisProject', '=', 1);
                })
                ->orderBy('client_id', 'asc')
                ->where('archived', 0)
                ->get();
                    $projectsPdf = PDF::loadView('admin.projects.downloadRepports', [
                        'projects' => $projects,
                        'entreprise' => $enterprise,
                    ]);
                    $projectsFilename = 'projects_report_' . Carbon::now()->format('d-m-Y') . '.pdf';
                    $projectsPdfPath = storage_path('app/public/' . $projectsFilename);
                    $projectsPdf->save($projectsPdfPath);
                    $attachments[] = [
                        'path' => $projectsPdfPath,
                        'name' => $projectsFilename
                    ];
                

                // Generate Employees Report
                $employees = Employees::where('entreprise_id', $enterprise->id)->orderBy('name', 'asc')->get();
               
                    $employeesPdf = PDF::loadView('admin.employees.downloadRepports', [
                        'employees' => $employees,
                        'entreprise' => $enterprise,
                    ]);
                    $employeesPdf->setPaper('a4', 'landscape');
                    $employeesFilename = 'employees_report_' . Carbon::now()->format('d-m-Y') . '.pdf';
                    $employeesPdfPath = storage_path('app/public/' . $employeesFilename);
                    $employeesPdf->save($employeesPdfPath);
                    $attachments[] = [
                        'path' => $employeesPdfPath,
                        'name' => $employeesFilename
                    ];
                

                // Generate Clients Report
                $clients = Client::where('entreprise_id', $enterprise->id)->where(function($query) {
                    $query->whereNull('AcceptedDevisClient')
                          ->orWhere('AcceptedDevisClient', '=', 1);
                })
                ->orderBy('client_number', 'asc')
                ->get();
                
                    $clientsPdf = PDF::loadView('admin.clients.downloadRepports', [
                        'clients' => $clients,
                        'entreprise' => $enterprise,
                    ]);
                    $clientsPdf->setPaper('a4', 'landscape');
                    $clientsFilename = 'clients_report_' . Carbon::now()->format('d-m-Y') . '.pdf';
                    $clientsPdfPath = storage_path('app/public/' . $clientsFilename);
                    $clientsPdf->save($clientsPdfPath);
                    $attachments[] = [
                        'path' => $clientsPdfPath,
                        'name' => $clientsFilename
                    ];
                

                // Generate Pointage Report
                $endDate = Carbon::now()->endOfDay()->format('Y-m-d H:i:s');
                $startDate = Carbon::now()->startOfMonth()->startOfDay()->format('Y-m-d H:i:s');
                $tasks_timer = tasks_timer::where('entreprise_id', $enterprise->id)
                    ->where('start_date', '>=', $startDate)
                    ->where('end_date', '<=', $endDate)
                    ->orderBy('start_date', 'asc')
                    ->get(); 
                
                    $pointagePdf = PDF::loadView('admin.tasks.downloadPointageRepports', [
                        'tasks_timer' => $tasks_timer,
                        'entreprise' => $enterprise, 
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                    ]);
                    $pointageFilename = 'pointage_report_' . Carbon::now()->format('d-m-Y') . '.pdf';
                    $pointagePdfPath = storage_path('app/public/' . $pointageFilename);
                    $pointagePdf->save($pointagePdfPath);
                    $attachments[] = [
                        'path' => $pointagePdfPath,
                        'name' => $pointageFilename
                    ];


                // Generate Devis Report
                $devis = devis_history::where('entreprise_id', $enterprise->id)->get();
                    $devisPdf = PDF::loadView('admin.devis.downloadRepports', [
                        'devis' => $devis,
                        'entreprise' => $enterprise,
                    ]);
                    
                    $devisFilename = 'devis_report_' . Carbon::now()->format('d-m-Y') . '.pdf';
                    $devisPdfPath = storage_path('app/public/' . $devisFilename);
                    $devisPdf->save($devisPdfPath);
                    $attachments[] = [
                        'path' => $devisPdfPath,
                        'name' => $devisFilename
                    ];

                // Generate Invoices Report
                $invoices = invoices_history::where('entreprise_id', $enterprise->id)->get();
                $totalPaid = invoices_history::where('entreprise_id', $enterprise->id)->where('Payment_status', 1)->sum('total_ttc');
                $totalUnpaid = invoices_history::where('entreprise_id', $enterprise->id)->where('Payment_status', 0)->sum('total_ttc');
                $total = $totalPaid + $totalUnpaid;
                $totaltva = invoices_history::where('entreprise_id', $enterprise->id)->sum('total_tva');
                    $invoicesPdf = PDF::loadView('admin.invoices.downloadRepports', [
                        'invoices' => $invoices,
                        'entreprise' => $enterprise,
                        'totalPaid' => $totalPaid,
                        'totalUnpaid' => $totalUnpaid,
                        'total' => $total,
                        'totaltva' => $totaltva,
                    ]);
                    $invoicesFilename = 'invoices_report_' . Carbon::now()->format('d-m-Y') . '.pdf';
                    $invoicesPdfPath = storage_path('app/public/' . $invoicesFilename);
                    $invoicesPdf->save($invoicesPdfPath);
                    $attachments[] = [
                        'path' => $invoicesPdfPath,
                        'name' => $invoicesFilename
                    ];
                


                
                

                // Send email with all attachments
                if (!empty($attachments)) {
                    // Split the emails string into an array
                    $recipientEmails = array_map('trim', explode(',', $RECIPIENT_EMAIL));
                    
                    Mail::send('admin.emails.daily-tasks-report', 
                        ['enterprise' => $enterprise], 
                        function($message) use ($attachments, $enterprise, $recipientEmails) {
                            $message->to($recipientEmails[0]); // First email as primary recipient
                            
                            // If there are additional emails, add them as CC
                            if (count($recipientEmails) > 1) {
                                $message->cc(array_slice($recipientEmails, 1));
                            }
                            
                            $message->subject('Daily Reports - ' . $enterprise->name . ' - ' . Carbon::now()->format('Y-m-d'));
                            
                            // Attach all PDFs
                            foreach ($attachments as $attachment) {
                                $message->attach($attachment['path'], [
                                    'as' => $attachment['name'],
                                    'mime' => 'application/pdf',
                                ]);
                            }
                        });

                    $this->info("Reports sent successfully for enterprise: {$RECIPIENT_EMAIL}");
                } else {
                    $this->info("No reports to send for enterprise: {$RECIPIENT_EMAIL}");
                }

                // Cleanup - Delete all temporary PDF files
                foreach ($attachments as $attachment) {
                    if (file_exists($attachment['path'])) {
                        unlink($attachment['path']);
                    }
                }

            } catch (\Exception $e) {
                $this->error("Error processing enterprise {$enterprise->name}: " . $e->getMessage());
            }
        //}

        $this->info('All daily reports process completed!');
    }
}
