<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@include('dashboard_layouts.head')
@include('partials.edit-layout-styles')

<body class="g-sidenav-show bg-gray-100">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>
    <div class="min-height-300 bg-primary position-absolute w-100"></div>

    @if(Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Employee'))
        @include('employees.sidebar')
    @endif
    @if(Auth::user()->hasRole('Admin'))
        @include('admin.sidebar')
    @endif

    <main class="dt-edit-legacy main-content position-relative border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
            id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">

                @include('dashboard_layouts.navbar')

            </div>

            <div class="dt-edit-container dt-edit-container--compact container-fluid py-4">

                <div class="row">
                    <div class="dt-edit-column col-lg-9 col-12 mx-auto">
                        <div class="card card-body mt-4">
                            <h6 class="mb-0">{{ __('messages.Update to do task') }}</h6>
                            <p class="text-sm mb-0">{{ __('messages.Update task') }}</p>
                            <hr class="horizontal dark my-3" />
                            @if(Auth::user()->hasRole('Admin'))
                            <form method="POST" action="{{route('admin.todoliste.update', $todo->id)}}" autocomplete="off">
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            <form method="POST" action="{{route('Responsable_de_projet.todoliste.update', $todo->id)}}" autocomplete="off">
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                            <form method="POST" action="{{route('employee.todoliste.update', $todo->id)}}" autocomplete="off">
                            @endif
                                @csrf
                                @method('')
                                <div class="row">
                                    <div class="col-12 col-sm-6">
                                        <label for="projectName" class="form-label">{{ __('messages.Name') }}</label>
                                        <input type="text" class="form-control" id="projectName" value='{{$todo->title}}' name='title' />
                                    </div>
                                </div>
                                <label class="mt-4">{{ __('messages.project Description') }}</label>
                                <input type="text" class="form-control" id="projectDescription" name='description' value='{{$todo->description}}'/>
                                <br>



                                <div class="row">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label">{{ __('messages.Start Date') }}</label>
                                        <input class="form-control datetimepicker" type="date"
                                            placeholder="{{ __('messages.Please select start date') }}" data-input name='start' value='{{$todo->start}}'/>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label">{{ __('messages.End Date') }}</label>
                                        <input class="form-control datetimepicker" type="date"
                                            placeholder="{{ __('messages.Please select end date') }}" data-input name='end' value='{{$todo->end}}'/>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    @if(Auth::user()->hasRole('Admin'))
                                    <a class='btn btn-light m-0' href="{{route('admin.index')}}">
                                        {{ __('messages.Cancel') }}
                                    </a>
                                    <button type="submit" name="button" class="btn bg-gradient-primary m-0 ms-2">
                                        {{ __('messages.Update task') }}
                                    </button>
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                    <a class='btn btn-light m-0' href="{{route('Responsable_de_projet.index')}}">
                                        {{ __('messages.Cancel') }}
                                    </a>
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                    <a class='btn btn-light m-0' href="{{route('employee.index')}}">
                                        {{ __('messages.Cancel') }}
                                    </a>
                                    @endif
                                </div>

                            </form>

                        </div>
                    </div>
                </div>



    </main>

   

    @include('dashboard_layouts.scripts')

    <script>
  window.addEventListener('load', function() {
    var inputs = document.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; i++) {
      inputs[i].setAttribute('autocomplete', 'none');
    }
  });
</script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropdownButton = document.getElementById('ClientDropdown');
        const selectedProjectInput = document.getElementById('selectedClient');

        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function (event) {
                event.preventDefault();

                const selectedClient = event.target.getAttribute('data-value');

                // Update the button text
                dropdownButton.textContent = selectedClient;

                // Update the hidden input value
                selectedProjectInput.value = selectedClient;
            });
        });
    });
</script>


</body>

</html>
