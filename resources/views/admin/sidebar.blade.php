<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4"
  id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
      aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand m-0"
      href=" {{route('admin.index')}} " >
      <img src="{{ Storage::disk('public')->url(Auth::user()->entreprise->image) }}" alt="Entreprise Image" class="img-fluid">
    </a>
  </div>
  <hr class="horizontal dark mt-0" />
  <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link active" href="{{route('admin.index')}}">
          <span class="sidenav-mini-icon"> U </span>
          <i class="fa-solid fa-house" style="color: #B197FC;"></i>
          <span class="sidenav-normal "> {{ __('messages.Dashboard') }} <b class="nav-link-ms-1"></b></span>
        </a>

      </li>
      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="collapse" aria-expanded="false" href="#employeeCollapse">
          <span class="sidenav-mini-icon"> U </span>
          <i class="fas fa-grip-lines" style="color: #61b71a;"></i>
          <span class="sidenav-normal"> {{ __('messages.Reporting') }} <b class="nav-link-text ms-1"></b></span>
        </a>
        <div class="collapse" id="employeeCollapse">
          <ul class="nav nav-sm flex-column">
            @can('View client')
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.clients.index')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> {{ __('messages.Clients Reports') }} </span>
              </a>
            </li>
            @endcan
            @can('View employee')
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.employees.index')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> {{ __('messages.Employees Reports') }} </span>
              </a>
            </li>
            @endcan
            @can('View teams')
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.teams.index')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> {{ __('messages.Teams Reports') }} </span>
              </a>
            </li>
            @endcan
            @can('View project')
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.projects.index')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> {{ __('messages.Projects Reports') }} </span>
              </a>
            </li>
            @endcan
            @can('View task')
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.tasks.index')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> {{ __('messages.Tasks Reports') }} </span>
              </a>
            </li>
            @endcan
            @can('View team')
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.teams.index')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> {{ __('messages.Teams Reports') }} </span>
              </a>
            </li>
            @endcan
            @can('View pointage')
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.tasks.pointage')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> {{ __('messages.Pointage Reports') }} </span>
              </a>
            </li>
            @endcan
            @can('Manage workDaysReports')
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.workDaysReports.index')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal "><i class="fa-solid fa-chevron-right"></i> {{ __('messages.Work Days Reports') }} </span>
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
          <span class="sidenav-normal"> {{ __('messages.Invoices header') }} <b class="nav-link-text ms-1"></b></span>
        </a>
        <div class="collapse" id="invoiceCollapse">
          <ul class="nav nav-sm flex-column">
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.invoices.fullinvoice')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  {{ __('messages.Generate') }}   </span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.invoices.generate.Detailed')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  {{ __('messages.Generate Detailed') }}   </span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.invoices.invoicehistory')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  {{ __('messages.History') }}   </span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.invoices.parameters')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  {{ __('messages.Parameters') }}   </span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.invoice.creditNoteHistory')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  {{ __('messages.Credit Note History') }}   </span>
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
          <span class="sidenav-normal"> {{ __('messages.Devis type') }} <b class="nav-link-text ms-1"></b></span>
        </a>
        <div class="collapse" id="devisCollapse">
          <ul class="nav nav-sm flex-column">
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.devis.generateDevis')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  {{ __('messages.Generate') }}   </span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.devis.devishistory')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  {{ __('messages.History') }}   </span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.devis.parameters')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  {{ __('messages.Parameters') }}   </span>
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
          <span class="sidenav-normal">  {{ __('messages.Expense Note') }}  <b class="nav-link-text ms-1"></b></span>
        </a>
        <div class="collapse" id="notedefraisCollapse">
          <ul class="nav nav-sm flex-column">
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.notedefrais.generateNoteDeFrais')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  {{ __('messages.Generate') }}   </span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.notedefrais.notedefraishistory')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  {{ __('messages.History') }}   </span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.notedefrais.parameters')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i>  {{ __('messages.Parameters') }}   </span>
              </a>
            </li>
          </ul>
        </div>
      </li>
      @endcan

      @php
          $currentUser = Auth::user();
      @endphp
      @if($currentUser && (int) ($currentUser->entreprise_id ?? 0) === 43)
      <li class="nav-item">
        <a class="nav-link active" href="{{ route('manager.direct-dashboard') }}">
          <span class="sidenav-mini-icon text-sm"> M </span>
          <i class="fa-solid fa-chart-line" style="color: #0d6efd;"></i>
          <span class="sidenav-normal"> {{ __('messages.Manager Dashboard') }} <b class="nav-link-text ms-1"></b></span>
        </a>
      </li>
      @endif

      <li class="nav-item">
        <a class="nav-link active" aria-expanded="false" href="{{route('admin.roles.index')}}">
          <span class="sidenav-mini-icon text-sm"> R </span>
          <i class="fa-solid fa-users-gear" style="color: #ee00ff;"></i>
          <span class="sidenav-normal"> {{ __('messages.Role Management Menu') }} <b class="nav-link-text ms-1"></b></span>
        </a>
      </li>

      
      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="collapse" aria-expanded="false" href="#entrepriseCollapse">
          <span class="sidenav-mini-icon"> P </span>
          <i class="fa-solid fa-building" style="color: #63E6BE;"></i>          
          <span class="sidenav-normal"> {{ __('messages.Enterprise Infos') }} <b class="nav-link-text ms-1"></b></span>
        </a>
        <div class="collapse" id="entrepriseCollapse">
          <ul class="nav nav-sm flex-column">
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.Enterprise_infos.index')}}">
                <span class="sidenav-mini-icon text-sm"> R </span>
                <span class="sidenav-normal"><i class="fa-solid fa-chevron-right"></i> {{ __('messages.Reports') }} </span>
              </a>
            </li>

          </ul>
        </div>
      </li>
      @can('Billings')
      <li class="nav-item">
        <a class="nav-link active" aria-expanded="false"  href="{{route('admin.Enterprise_infos.create')}}">
          <span class="sidenav-mini-icon text-sm"> R </span>
          <i class="fa-solid fa-note-sticky" style="color: #20c6fe;"></i>          
          <span class="sidenav-normal"> {{ __('messages.Billings') }} <b class="nav-link-text ms-1"></b></span>
        </a>
      </li>
      @endcan

      {{-- Help / Support: enterprise-optimized support interface --}}
      <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.support.create') }}">
          <span class="sidenav-mini-icon text-sm"> ? </span>
          <i class="fa-regular fa-circle-question" style="color: #0d6efd;"></i>
          <span class="sidenav-normal"> Help &amp; Support <b class="nav-link-text ms-1"></b></span>
        </a>
      </li>


    </ul>

  </div>

</aside>
