<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4"
  id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
      aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand m-0"
      href=" {{route('employee.index')}} " >
      <span class="ms-1 font-weight-bold text-uppercase fs-8">{{App\Models\Employees::where('user_id', Auth::user()->id)->first()->name}}</span>
    </a>
  </div>
  <hr class="horizontal dark mt-0" />
  <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <li class="nav-item">
        @if(Auth::user()->hasRole('Responsable de projet'))
        <a class="nav-link active" href="{{route('Responsable_de_projet.index')}}">
        @elseif(Auth::user()->hasRole('Employee'))
        <a class="nav-link active" href="{{route('employee.index')}}">
        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
        <a class="nav-link active" href="{{route('Responsable_gestionnaire.index')}}">
        @endif

          <span class="sidenav-mini-icon"> U </span>
          <i class="fa-solid fa-house" style="color: #B197FC;"></i>
          <span class="sidenav-normal "> Dashboard <b class="nav-link-ms-1"></b></span>
        </a>

      </li>
        
      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="collapse" aria-expanded="false" href="#employeeCollapse">
          <span class="sidenav-mini-icon"> U </span>
          <i class="fas fa-grip-lines" style="color: #61b71a;"></i>
          <span class="sidenav-normal"> Reporting <b class="nav-link-text ms-1"></b></span>
        </a>
        <div class="collapse" id="employeeCollapse">
          <ul class="nav nav-sm flex-column">
            @can('View client')
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.clients.index')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.clients.index')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.clients.index')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> Clients Reports </span>
              </a>
            </li>
            @endcan
            @can('View employee')
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.employees.index')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.employees.index')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.employees.index')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> Employees Reports </span>
              </a>
            </li>
            @endcan
            @can('View project')
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.projects.index')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.projects.index')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.projects.index')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> Projects Reports </span>
              </a>
            </li>
            @endcan
            @can('View task')
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.tasks.index')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.tasks.index')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.tasks.index')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> Tasks Reports </span>
              </a>
            </li>
            @endcan
            @can('View team')
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.teams.index')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.teams.index')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.teams.index')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> Teams Reports </span>
              </a>
            </li>
            @endcan
            @can('View pointage')
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.tasks.pointage')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.tasks.pointage')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.tasks.pointage')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> Pointage Reports </span>
              </a>
            </li>
            @endcan
            @can('Manage workDaysReports')
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.workDaysReports.index')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.workDaysReports.index')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.workDaysReports.index')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> Work Days Reports </span>
              </a>
            </li>
            @endcan
          </ul>
        </div>
      </li>
      

      @can('Create invoice')
      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="collapse" aria-expanded="false" href="#invoiceCollapse">
          <span class="sidenav-mini-icon"> P </span>
          <i class="fa-solid fa-file-invoice" style="color: #ff0000;"></i>
          <span class="sidenav-normal"> Invoices <b class="nav-link-text ms-1"></b></span>
        </a>
        <div class="collapse" id="invoiceCollapse">
          <ul class="nav nav-sm flex-column">
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.invoices.fullinvoice')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.invoices.fullinvoice')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.invoices.fullinvoice')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  Generate   </span>
              </a>
            </li>
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.invoices.invoicehistory')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.invoices.invoicehistory')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.invoices.invoicehistory')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  History   </span>
              </a>
            </li>
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.invoices.parameters')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.invoices.parameters')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.invoices.parameters')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  Parameters   </span>
              </a>
            </li>
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.invoice.creditNoteHistory')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.invoice.creditNoteHistory')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.invoice.creditNoteHistory')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  Credit Note History   </span>
              </a>
            </li>
          </ul>
        </div>
      </li>
      @endcan
      @can('Create devis')
      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="collapse" aria-expanded="false" href="#devisCollapse">
          <span class="sidenav-mini-icon"> P </span>
          <i class="fa-solid fa-envelopes-bulk"></i>
          <span class="sidenav-normal"> Devis <b class="nav-link-text ms-1"></b></span>
        </a>
        <div class="collapse" id="devisCollapse">
          <ul class="nav nav-sm flex-column">
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.devis.generateDevis')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.devis.generateDevis')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.devis.generateDevis')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  Generate   </span>
              </a>
            </li>
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
                <a class="nav-link" href="{{route('Responsable_de_projet.devis.devishistory')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.devis.devishistory')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.devis.devishistory')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  History   </span>
              </a>
            </li>
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.devis.parameters')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.devis.parameters')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.devis.parameters')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  Parameters   </span>
              </a>
            </li>
          </ul>
        </div>
      </li>
      @endcan
      @can('Create expense note')
      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="collapse" aria-expanded="false" href="#notedefraisCollapse">
          <span class="sidenav-mini-icon"> P </span>
          <i class="fa-solid fa-book" style="color: #0e7500;"></i>
          <span class="sidenav-normal">  Expense Note  <b class="nav-link-text ms-1"></b></span>
        </a>
        <div class="collapse" id="notedefraisCollapse">
          <ul class="nav nav-sm flex-column">
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.notedefrais.generateNoteDeFrais')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.notedefrais.generateNoteDeFrais')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.notedefrais.generateNoteDeFrais')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  Generate   </span>
              </a>
            </li>
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.notedefrais.notedefraishistory')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.notedefrais.notedefraishistory')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.notedefrais.notedefraishistory')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  History   </span>
              </a>
            </li>
            <li class="nav-item">
              @if(Auth::user()->hasRole('Responsable de projet'))
              <a class="nav-link" href="{{route('Responsable_de_projet.notedefrais.parameters')}}">
                @elseif(Auth::user()->hasRole('Employee'))
                <a class="nav-link" href="{{route('employee.notedefrais.parameters')}}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                <a class="nav-link" href="{{route('Responsable_gestionnaire.notedefrais.parameters')}}">
                @endif
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  Parameters   </span>
              </a>
            </li>
          </ul>
        </div>
      </li>
      @endcan
      @can('Billings')
      <li class="nav-item">
        @if(Auth::user()->hasRole('Responsable de projet'))
          <a class="nav-link active" aria-expanded="false"  href="{{route('Responsable_de_projet.Enterprise_infos.create')}}">
        @elseif(Auth::user()->hasRole('Employee'))
        <a class="nav-link active" aria-expanded="false"  href="{{route('employee.Enterprise_infos.create')}}">
        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
        <a class="nav-link active" aria-expanded="false"  href="{{route('Responsable_gestionnaire.Enterprise_infos.create')}}">
        @endif
          <span class="sidenav-mini-icon text-sm"> R </span>
          <i class="fa-solid fa-note-sticky" style="color: #20c6fe;"></i>          
          <span class="sidenav-normal"> Billings <b class="nav-link-text ms-1"></b></span>
        </a>
      </li>
      @endif


    </ul>

  </div>

</aside>