<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
  a {
    color: blue;
  }
  .font-weight-bolder{
    color: black !important;
  }
  .iframe-container {
    width: 100%;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    overflow: hidden;
  }
</style>
<body class="g-sidenav-show bg-gray-100">
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
      style="display: none; visibility: hidden"></iframe></noscript>
  <div class="min-height-300 bg-primary position-absolute w-100"></div>

  @if(Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Employee') || Auth::user()->hasRole('Responsable de projet et gestionnaire'))
        @include('employees.sidebar')
    @endif
    @if(Auth::user()->hasRole('Admin'))
        @include('admin.sidebar')
    @endif

  <main class="main-content position-relative border-radius-lg">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
      id="navbarBlur" data-scroll="false">
      <div class="container-fluid py-1 px-3">
        @include('dashboard_layouts.navbar')
      </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Workdays Report Preview</h4>
                    </div>
                    <div class="card-body">
                        <!-- PDF Preview -->
                        <div class="pdf-preview iframe-container">
                            <iframe src="{{ route('admin.workDaysReports.pdf', ['year' => $year, 'month' => $month, 'employees' => $employees, 'excluded_columns' => $excludedColumns]) }}" width="100%" height="600px"></iframe>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Email Options</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.workDaysReports.sendEmail') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="year" value="{{ $year }}">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="employees" value="{{ $employees }}">
                            <input type="hidden" name="excluded_columns" value="{{ $excludedColumns }}">
                            
                            <div class="form-group">
                                <label for="emails">Recipients</label>
                                <textarea id="emails" name="emails" class="form-control" rows="3" placeholder="Enter email addresses separated by commas">{{ $entreprise->workdays_emails }}</textarea>
                                <small class="form-text text-muted">You can edit or add more email addresses separated by commas.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Email Subject</label>
                                <input type="text" id="subject" name="subject" class="form-control" value="Workdays Report - {{ $monthName }} {{ $year }}">
                            </div>
                            
                            <div class="form-group">
                                <label for="body">Email Body</label>
                                <textarea id="body" name="body" class="form-control" rows="6">Objet : Envoi des informations pour l'établissement des fiches de paie
Bonjour,
Je vous transmets ci-joint les éléments nécessaires à l'établissement des fiches de paie.
Vous trouverez le Workdays Report en annexe.

N'hésitez pas à me contacter si vous avez besoin de précisions ou de documents complémentaires.
Merci d'avance pour votre retour.

Bien cordialement</textarea>
                            </div>

                            <div class="form-group">
                                <label for="attachments">Additional Attachments (optional)</label>
                                <input type="file" id="attachments" name="attachments[]" class="form-control" multiple>
                                <small class="form-text text-muted">You can select multiple files to include with the report.</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="saveEmails" name="saveEmails" value="1" checked>
                                    <label class="form-check-label" for="saveEmails">
                                        Save these email addresses for future reports
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary btn-block w-100">
                                    <i class="fa fa-envelope"></i> Send Email
                                </button>
                            </div>
                            
                            <div class="form-group mt-3">
                                <a href="{{ route('admin.workDaysReports.index') }}" class="btn btn-secondary btn-block w-100">
                                    <i class="fa fa-arrow-left"></i> Back to Workdays Reports
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </main>

  @include('dashboard_layouts.scripts')
</body>
</html> 