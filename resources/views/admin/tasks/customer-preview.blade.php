<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
.main-content-wrapper {
    margin-left: 260px; /* Adjust if your sidebar is a different width */
    padding-top: 30px;
    padding-bottom: 30px;
    background: #f8f9fa;
    min-height: 100vh;
}
.row.flex-nowrap {
    flex-wrap: nowrap !important;
}
.card {
    margin-bottom: 20px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    background: #fff;
}
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 15px;
}
.card-header h4 {
    margin: 0;
    color: #333;
}
.card-body {
    padding: 20px;
}
.form-group {
    margin-bottom: 20px;
}
.form-control {
    border-radius: 4px;
    border: 1px solid #ced4da;
    padding: 8px 12px;
}
.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}
.btn {
    padding: 8px 16px;
    border-radius: 4px;
    margin-right: 10px;
}
.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}
.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}
.btn:hover {
    opacity: 0.9;
}
.pdf-preview-iframe {
    width: 100%;
    height: 80vh;
    min-height: 600px;
    border: none;
    background: #222;
    display: block;
}
@media (max-width: 991.98px) {
    .main-content-wrapper {
        margin-left: 0;
        padding: 10px;
    }
    .row.flex-nowrap {
        flex-wrap: wrap !important;
    }
    .col-md-6 {
        max-width: 100%;
        flex: 0 0 100%;
    }
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

    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
      id="navbarBlur" data-scroll="false">
      <div class="container-fluid py-1 px-3">
        @include('dashboard_layouts.navbar')
      </div>
    </nav>

    <div class="container-fluid main-content-wrapper">
        <div class="row flex-nowrap">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>PDF Preview</h4>
                    </div>
                    <div class="card-body">
                        <iframe src="{{ $pdfUrl }}" class="pdf-preview-iframe"></iframe>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Email Options</h4>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        <form action="{{ route('admin.CustomerPointagerepports.send') }}" method="POST"> 
                            @csrf
                            <input type="hidden" name="start_date" value="{{ $startDate }}">
                            <input type="hidden" name="end_date" value="{{ $endDate }}">
                            <input type="hidden" name="project_id" value="{{ $projectId }}">
                            <input type="hidden" name="employee_id" value="{{ $employeeId }}">
                            <input type="hidden" name="task_ids" value="{{ json_encode($taskIds) }}">
                            <input type="hidden" name="client_id" value="{{ $clientId }}">
                            <input type="hidden" name="redirect_to" value="{{ route('admin.index') }}">

                            <div class="form-group">
                                <label for="recipients">Recipients (comma-separated emails)</label>
                                <textarea class="form-control" id="recipients" name="recipients" rows="3" required>{{ implode(',', $projectContacts) }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="subject">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" value="Worksheet - {{ $projectName }} - {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}" required>
                            </div>

                            <div class="form-group">
                                <label for="body">Message</label>
                                <textarea class="form-control" id="body" name="body" rows="5" required>Bonjour,
Je vous transmets ci-joint le worksheet pour validation.

N'hésitez pas à me contacter si vous avez besoin de précisions ou de documents complémentaires.
Merci d'avance pour votre retour. 

Bien à vous</textarea>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Send Email</button>
                                <a href="{{ route('admin.tasks.pointage') }}" class="btn btn-secondary">Back to Tasks</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
  @include('dashboard_layouts.scripts')
</body>
</html> 