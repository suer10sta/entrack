<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<meta name="csrf-token" content="{{ csrf_token() }}">


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


                <div class="container-fluid py-4">
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="multisteps-form mb-5">
                            <div class="row">
                                <div class="col-12 col-lg-8 mx-auto my-4">
                                    <div class="card">
                                        <div class="row">
                                            <div class="col-10 col-lg-6 mx-auto my-2">
                                                <div class="card-body">
                                                    <div class="row justify-content-between align-items-center">
                                                        @if(session('successNewBudget'))
                                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                                {{ session('successNewBudget') }}
                                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                                                            </div>
                                                        @endif
                                                        @if(session('error'))
                                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                                {{ session('erro') }}
                                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                                                            </div>
                                                        @endif
                                                        <div class="col-auto text-left pt-4">
                                                            <h3 class="text-left">Dépense</h3>
                                                        </div>
                                                        <div class="col-auto text-right">
                                                            @if($latestBudget)
                                                            <div class="form-group">
                                                                <label>Caisse:</label>
                                                                <input id="budget-input-{{ $latestBudget->id }}"
                                                                    class="multisteps-form__input form-control" type="number" 
                                                                    name="monthBudget"
                                                                    value="{{ $latestBudget->budget }}" readonly
                                                                    ondblclick="enableEditing(this, {{ $latestBudget->id }})" />
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Sold:</label>
                                                                <input id="sold-input"
                                                                    class="multisteps-form__input form-control sold"
                                                                    type="number" 
                                                                    name="sold"
                                                                    value="{{$latestBudget->budget_remaining}}"
                                                                    readonly />
                                                            </div>
                                                            @endif
                                                            <div x-data="datePicker()" class="d-flex align-items-center">
                                                                <div class="form-group mr-2">
                                                                    <label>Start Date:</label>
                                                                    <input id="start-date"
                                                                        class="multisteps-form__input form-control"
                                                                        type="date"
                                                                        name="startDate"
                                                                        x-model="startDate" />
                                                                </div>
                                                                <div class="form-group mr-2">
                                                                    <label>End Date:</label>
                                                                    <input id="end-date"
                                                                        class="multisteps-form__input form-control ms-2"
                                                                        type="date"
                                                                        name="endDate"
                                                                        x-model="endDate" />
                                                                </div>
                                                                <div class="form-group m-2 mt-4">
                                                                    <a href="#" @click.prevent="updateHref" class="btn btn-primary mt-2" id="downloadLink">Print PDF</a>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                @if(Auth::user()->hasRole('Admin'))
                                                                <form action="{{route('admin.entreprise.addBudget')}}" method="post" class="d-flex align-items-center">
                                                                @endif
                                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                                <form action="{{route('Responsable_de_projet.entreprise.addBudget')}}" method="post" class="d-flex align-items-center">
                                                                @endif
                                                                @if(Auth::user()->hasRole('Employee'))
                                                                <form action="{{route('employee.entreprise.addBudget')}}" method="post" class="d-flex align-items-center">
                                                                @endif
                                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                <form action="{{route('Responsable_gestionnaire.entreprise.addBudget')}}" method="post" class="d-flex align-items-center">
                                                                @endif
                                                                    @csrf
                                                                    <div class="form-group mr-4">
                                                                        <label>new budget:</label>
                                                                        <input 
                                                                            class="multisteps-form__input form-control entrepriseInfos" 
                                                                            type="number"  
                                                                            name="newBudget" 
                                                                            step="0.0001" />
                                                                    </div>
                                                                    <div class="form-group p-2 mr-2 mt-5">
                                                                        <button class="btn btn-dark" type="submit">add new budget</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                                <div class="row">
                                    
                                @foreach($billings_budget as $billing)
                                    <div class="col-12 col-lg-8 m-auto">
                                        <div class="card mb-4">
                                            <div class="multisteps-form__content mb-4 p-4">
                                                <div class="d-flex justify-content-between align-items-center pt-4">
                                                    <h5 class="text-left font-weight-bolder mb-2">Budget : {{$billing->budget}}€ de date : {{$billing->created_at->format('d-m-Y')}}</h5>
                                                    @if(Auth::user()->hasRole('Admin'))
                                                        <form method="post" action="{{route('admin.billing.destroy', $billing->id)}}" onsubmit="return confirm('Are you sure?');">
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                        <form method="post" action="{{route('Responsable_de_projet.billing.destroy', $billing->id)}}" onsubmit="return confirm('Are you sure?');">
                                                    @endif
                                                    @if(Auth::user()->hasRole('Employee'))
                                                        <form method="post" action="{{route('employee.billing.destroy', $billing->id)}}" onsubmit="return confirm('Are you sure?');">
                                                    @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                        <form method="post" action="{{route('Responsable_gestionnaire.billing.destroy', $billing->id)}}" onsubmit="return confirm('Are you sure?');">
                                                    @endif
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm bg-gradient-danger">Delete</button>
                                                        </form>
                                                    
                                                </div>
                                                @if(session('success'))
                                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                        {{ session('success') }}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                                                    </div>
                                                @else
                                                    @if(session('error'))
                                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                            {{ session('error') }}
                                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                                                        </div>
                                                    @endif
                                                @endif
                                                @php 
                                                    $entrepriseCharges = App\Models\entrepriseCharges::where('entreprise_id', $entreprise->id)->where('Billings_budget_id', $billing->id)->orderBy('created_at')->get();
                                                @endphp
                                                @foreach($entrepriseCharges as $entrepriseCharge)
                                                    <div id="">
                                                        <div class="row mt-3">
                                                            <div class="col-10 col-sm-4">
                                                                <label>Objet de la dépense : </label>
                                                                <input id="charge-name-{{ $entrepriseCharge->id }}"
                                                                    class="multisteps-form__input @if($entrepriseCharge->validated != 1) editable @endif form-control" type="text"
                                                                    name="chargename"
                                                                    value="{{ $entrepriseCharge->charge_name }}" readonly />                                                        
                                                            </div>
                                                            <div class="col-10 col-sm-2 mt-3 mt-sm-0">
                                                                <label>Prix de la dépense : </label>
                                                                <input id="price-{{ $entrepriseCharge->id }}"
                                                                    class="multisteps-form__input @if($entrepriseCharge->validated != 1) editable @endif form-control" type="text"
                                                                    name="price" value="{{ $entrepriseCharge->price }}€"
                                                                    readonly />                                                        
                                                            </div>
                                                            <div class="col-10 col-sm-2 mt-3 mt-sm-0">
                                                                <label>Date de la dépense : </label>
                                                                <input id="created_at-{{ $entrepriseCharge->id }}"
                                                                    class="multisteps-form__input @if($entrepriseCharge->validated != 1) editable @endif form-control" type="text"
                                                                    name="created_at" value="{{ $entrepriseCharge->created_at->format('d-m-Y') }}"
                                                                    readonly />                                                        
                                                            </div>
                                                            @if($billing->sold_out == 0)
                                                                <div class="col-10 col-sm-2 mt-3 mt-sm-0 pt-4">
                                                                    @if(Auth::user()->hasRole('Admin'))
                                                                    <form method="post"
                                                                        action="{{route('admin.Enterprise_infos.destroy', $entrepriseCharge->id)}}"
                                                                        onsubmit="return confirm('Are you sure?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                            class="btn btn-sm bg-gradient-danger m-2">Delete</button>
                                                                    </form>
                                                                    @endif
                                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                                    <form method="post"
                                                                        action="{{route('Responsable_de_projet.Enterprise_infos.destroy', $entrepriseCharge->id)}}"
                                                                        onsubmit="return confirm('Are you sure?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                            class="btn btn-sm bg-gradient-danger m-2">Delete</button>
                                                                    </form>
                                                                    @endif
                                                                    @if(Auth::user()->hasRole('Employee'))
                                                                    <form method="post"
                                                                        action="{{route('employee.Enterprise_infos.destroy', $entrepriseCharge->id)}}"
                                                                        onsubmit="return confirm('Are you sure?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                            class="btn btn-sm bg-gradient-danger m-2">Delete</button>
                                                                    </form>
                                                                    @endif
                                                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                    <form method="post"
                                                                        action="{{route('Responsable_gestionnaire.Enterprise_infos.destroy', $entrepriseCharge->id)}}"
                                                                        onsubmit="return confirm('Are you sure?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                            class="btn btn-sm bg-gradient-danger m-2">Delete</button>
                                                                    </form>
                                                                    @endif
                                                                </div>
                                                                @if($entrepriseCharge->validated != 1)
                                                                <div class="col-10 col-sm-2 mt-3 mt-sm-0 pt-4 p-0">
                                                                    @if(Auth::user()->hasRole('Admin'))
                                                                        <form method="post"
                                                                            action="{{ route('admin.entreprise.validateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]) }}">
                                                                    @endif
                                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                                    <form method="post"
                                                                            action="{{ route('Responsable_de_projet.entreprise.validateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]) }}">
                                                                    @endif
                                                                    @if(Auth::user()->hasRole('Employee'))
                                                                    <form method="post"
                                                                            action="{{ route('employee.entreprise.validateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]) }}">
                                                                    @endif
                                                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                        <form method="post"
                                                                            action="{{ route('Responsable_gestionnaire.entreprise.validateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]) }}">
                                                                    @endif
                                                                        @csrf
                                                                        <button type="submit"
                                                                            class="btn btn-sm bg-gradient-success m-2">Valider</button>
                                                                    </form>
                                                                </div>
                                                                @else
                                                                <div class="col-10 col-sm-2 mt-3 mt-sm-0 pt-4 p-0">
                                                                    @if(Auth::user()->hasRole('Admin'))
                                                                    <form method="post"
                                                                        action="{{route('admin.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id])}}">
                                                                    @endif
                                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                                    <form method="post"
                                                                        action="{{route('Responsable_de_projet.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id])}}">
                                                                    @endif 
                                                                    @if(Auth::user()->hasRole('Employee'))
                                                                    <form method="post"
                                                                        action="{{route('employee.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id])}}">
                                                                    @endif
                                                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                        <form method="post"
                                                                            action="{{route('Responsable_gestionnaire.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id])}}">
                                                                    @endif
                                                                        @csrf
                                                                        <button type="submit"
                                                                            class="btn btn-sm bg-gradient-success m-2">Annuler validation</button>
                                                                    </form>
                                                                </div>
                                                                @endif
                                                            @else 
                                                                <div class="col-10 col-sm-2 mt-3 mt-sm-0">
                                                                    <label>Status : </label>
                                                                    <input 
                                                                        class="multisteps-form__input form-control" type="text"
                                                                        name="status" value="{{ $entrepriseCharge->validated == 0 ? 'non validé' : 'validé' }}"
                                                                        readonly /> 
                                                                </div>   
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                    <div class="col-12 col-lg-8 m-auto">
                                    @if(Auth::user()->hasRole('Admin'))
                                        <form class="multisteps-form__form mb-8" method="POST" action="{{route('admin.Enterprise_infos.store')}}" enctype="multipart/form-data" autocomplete="off">
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                        <form class="multisteps-form__form mb-8" method="POST" action="{{route('Responsable_de_projet.Enterprise_infos.store')}}" enctype="multipart/form-data" autocomplete="off">
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                        <form class="multisteps-form__form mb-8" method="POST" action="{{route('employee.Enterprise_infos.store')}}" enctype="multipart/form-data" autocomplete="off">
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <form class="multisteps-form__form mb-8" method="POST" action="{{route('Responsable_gestionnaire.Enterprise_infos.store')}}" enctype="multipart/form-data" autocomplete="off">
                                    @endif
                                            @csrf    
                                            <div class="card multisteps-form__panel p-3 border-radius-xl bg-white js-active" data-animation="FadeIn">
                                                @if(session('successNewDepense'))
                                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                        {{ session('successNewDepense') }}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                                                    </div>
                                                @else
                                                    @if(session('errorNewDepense'))
                                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                            {{ session('errorNewDepense') }}
                                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                                                        </div>
                                                    @endif
                                                @endif
                                                <h5 class="font-weight-bolder mb-0">Nouvelle dépense </h5>
                                                <div class="multisteps-form__content ">
                                                    <div id="billings">
                                                        <div class="row mt-3">
                                                            <div class="col-12 col-sm-6">
                                                                <label>Objet de la dépense : </label>
                                                                <input class="multisteps-form__input form-control" type="text" placeholder="Billing Name" name="name[]" />
                                                            </div>
                                                            <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                                <label>Prix de la dépense : </label>
                                                                <input class="multisteps-form__input form-control" type="number"  name="price[]" step="0.0001"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" id="add-billing" class="btn btn-primary mt-3">Ajouter</button>
                                                </div>
                                                @if(Auth::user()->hasRole('Admin'))
                                                    <a class='btn btn-light m-0' href="{{route('admin.index')}}">
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                    <a class='btn btn-light m-0' href="{{route('Responsable_de_projet.index')}}">
                                                @endif
                                                @if(Auth::user()->hasRole('Employee'))
                                                    <a class='btn btn-light m-0' href="{{route('employee.index')}}">
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                    <a class='btn btn-light m-0' href="{{route('Responsable_gestionnaire.index')}}">
                                                @endif
                                                    Annuler
                                                </a>
                                                <button type="submit" class="btn btn-primary mt-3" style="margin-top: 20px;">Submit</button>                                    
                                            </div>
                                        </form>
                                    </div>
                                    
                                    
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

    </main>
    @include('dashboard_layouts.scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
  window.addEventListener('load', function() {
    var inputs = document.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; i++) {
      inputs[i].setAttribute('autocomplete', 'none');
    }
  });
</script>

    <script>
    window.onload = function() {

        document.getElementById('add-billing').addEventListener('click', function() {
            var billings = document.getElementById('billings');
            var newBilling = billings.firstElementChild.cloneNode(true);
            newBilling.querySelector('input[name="name[]"]').value = '';
            newBilling.querySelector('input[name="price[]"]').value = '';

            if (billings.children.length > 0) {
                var deleteButton = document.createElement('button');
                deleteButton.textContent = 'Delete';
                deleteButton.type = 'button';
                deleteButton.className = 'btn btn-danger mt-3 remove-billing';
                deleteButton.addEventListener('click', function() {
                    billings.removeChild(newBilling);
                });
                newBilling.appendChild(deleteButton);
            }

            billings.appendChild(newBilling);
        });
    };
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const editableInputs = document.querySelectorAll('.editable');

    editableInputs.forEach(input => {
        input.addEventListener('dblclick', function() {
            this.readOnly = false;
            this.focus();
        });

        input.addEventListener('blur', function() {
            this.readOnly = true;
            updateField(this);
        });

        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.blur();
            }
        });
    });

    function updateField(input) {
        const id = input.id.split('-')[1];
        const column = input.id.split('-')[0];
        const content = input.value;
        @if(Auth::user()->hasRole('Admin'))
        fetch('{{ route("admin.Enterprise_Billings.updateBillings") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                id: id,
                column: column,
                content: content
            })
        })
        @endif
        @if(Auth::user()->hasRole('Responsable de projet'))
        fetch('{{ route("Responsable_de_projet.Enterprise_Billings.updateBillings") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                id: id,
                column: column,
                content: content
            })
        })
        @endif
        @if(Auth::user()->hasRole('Employee'))
        fetch('{{ route("employee.Enterprise_Billings.updateBillings") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                id: id,
                column: column,
                content: content
            })
        })
        @endif
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Update successful');
                location.reload();
                // You can add some visual feedback here, like changing the input's background color briefly
            } else {
                console.error('Update failed');
                // Handle the error, maybe revert the input to its original value
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
});
    </script>

<script src="//unpkg.com/alpinejs" defer></script>
<script>
    function datePicker() {
        return {
            startDate: '',
            endDate: '',
            updateHref() {
                let baseUrl = '';
                @if(Auth::user()->hasRole('Responsable de projet'))
                    baseUrl = "{{route('Responsable_de_projet.entrepriseBillings.downloadRepports')}}";
                @endif
                @if(Auth::user()->hasRole('Admin'))
                    baseUrl = "{{route('admin.entrepriseBillings.downloadRepports')}}";
                @endif
                @if(Auth::user()->hasRole('Employee'))
                    baseUrl = "{{route('employee.entrepriseBillings.downloadRepports')}}";
                @endif
                let urlWithParams = baseUrl;
                if (this.startDate && this.endDate) {
                    urlWithParams += `?startDate=${this.startDate}&endDate=${this.endDate}`;
                }
                window.location.href = urlWithParams; // Redirect to the updated URL 
            }
        }
    }
</script>

<script>
    function enableEditing(input, budgetId) {
        input.removeAttribute('readonly');
        input.focus();
        input.addEventListener('blur', function() {
            updateBudget(input, budgetId);
        });
        input.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                updateBudget(input, budgetId);
            }
        });
    }

    function updateBudget(input, budgetId) {
        input.setAttribute('readonly', true);
        const budget = input.value;

        @if(Auth::user()->hasRole('Admin'))
        fetch(`/admin/budget/update/${budgetId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ budget: budget })
        })
        @endif
        @if(Auth::user()->hasRole('Responsable de projet'))
        fetch(`/Responsable_de_projet/budget/update/${budgetId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ budget: budget })
        })
        @endif
        @if(Auth::user()->hasRole('Employee'))
        fetch(`/employee/budget/update/${budgetId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ budget: budget })
        })
        @endif
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Please delete all charges before updating the budget');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the budget. Please try again.');
            location.reload();
        });
    }
</script>

  

  

</body>

</html>