<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
  a {
    color: blue;
}
</style>
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
                                            <div class="card-body text-center">
                                                <h3>Enterprise Infos</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-lg-8 m-auto">
                                        <div class="multisteps-form__form mb-8">

                                            <div class="card multisteps-form__panel p-3 border-radius-xl bg-white js-active d-flex flex-column "
                                                data-animation="FadeIn">
                                                <div style="display: flex; align-items: center;">
                                                    <div>
                                                        <h5 class="font-weight-bolder mb-0">Enterprise Infos</h5>
                                                        <p class="mb-0 text-sm">Mandatory informations</p>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <!-- Use col-md-4 to have 3 inputs in each row on medium screens and above -->
                                                        <label>Name:</label>
                                                        <input
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            type="text" name="name" value="{{$entreprise->name}}"
                                                            readonly />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Phone Number:</label>
                                                        <input
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            type="text" name="phone" value="{{$entreprise->phone}}"
                                                            readonly />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Contact Email:</label>
                                                        <input
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            type="text" name="email" value="{{$entreprise->email}}"
                                                            readonly />
                                                    </div>

                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label>Address:</label>
                                                        <input
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            type="text" name="Address" value="{{$entreprise->Address}}"
                                                            readonly />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Tva Number:</label>
                                                        <input
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            type="text" name="tva" value="{{$entreprise->tva}}"
                                                            readonly />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Bank Name:</label>
                                                        <input
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            type="text" name="bank_name"
                                                            value="{{$entreprise->bank_name}}" readonly />
                                                    </div>

                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label>Iban:</label>
                                                        <input
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            type="text" name="iban" value="{{$entreprise->iban}}"
                                                            readonly />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Swift/Bic:</label>
                                                        <input
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            type="text" name="bic"
                                                            value="{{$entreprise->swift}}/{{$entreprise->bic}}"
                                                            readonly />
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label>Created at:</label>
                                                        <input
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            type="text" name="chargename"
                                                            value="{{ \Carbon\Carbon::parse($entreprise->created_at)->format('d-m-Y') }}"
                                                            readonly />
                                                    </div>
                                                </div>
                                                <form action="{{ route('admin.entreprise.updateImage') }}" method="POST"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="row align-items-center">
                                                        <div class="col-md-4">
                                                            <label for="">Logo:</label>
                                                            <input type="file" name="entreprise_image"
                                                                class="form-control d-inline-block"
                                                                style="width: auto;">
                                                        </div>
                                                        <div class="col-md-8">
                                                            <label for=""> </label>
                                                            <button type="submit"
                                                                class="btn btn-primary mt-2 ">Update
                                                                Image</button>
                                                        </div>
                                                    </div>
                                                </form>

                                                <br>

                                                <h5 class="font-weight-bolder mb-0">Emails Backup</h5>
                                                <p class="mb-0 text-sm">Mandatory informations</p>

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label>Emails Backup (Enter each email separated by a comma):</label>
                                                        <textarea
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            name="emails_backup"
                                                            rows="3"
                                                            style="resize: vertical;"
                                                            readonly
                                                        >{{ $entreprise->emails_backup ?? 'Double Click to Configure it' }}</textarea>
                                                    </div>

                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label>WorkDaysRepport Receivers Emails (Enter each email separated by a comma):</label>
                                                        <textarea
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            name="workdays_emails"
                                                            rows="3"
                                                            style="resize: vertical;"
                                                            readonly
                                                        >{{ $entreprise->workdays_emails ?? 'Double Click to Configure it' }}</textarea>
                                                    </div>

                                                </div>

                                                <br>

                                                <h5 class="font-weight-bolder mb-0">SMTP Configuration</h5>
                                                <p class="mb-0 text-sm">Mandatory informations</p>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <!-- Use col-md-4 to have 3 inputs in each row on medium screens and above -->
                                                        <label>Mail Host:</label>
                                                        <input
                                                            class="editableSmtp multisteps-form__inputone form-control"
                                                            type="text" name="mail_host"
                                                            value="{{ $smtp->mail_host ?? 'Double Click to Configure it' }}"
                                                            readonly />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>Mail Port:</label>
                                                        <input
                                                            class="editableSmtp multisteps-form__inputone form-control"
                                                            type="text" name="mail_port"
                                                            value="{{$smtp->mail_port ?? 'Double Click to Configure it'}}"
                                                            readonly />
                                                    </div>

                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label>Mail Password:</label>
                                                        <input
                                                            class="editableSmtp multisteps-form__inputone form-control"
                                                            type="text" name="mail_password"
                                                            value="{{$smtp->mail_password ?? 'Double Click to Configure it'}}"
                                                            readonly />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>Mail Username:</label>
                                                        <input
                                                            class="editableSmtp multisteps-form__inputone form-control"
                                                            type="text" name="mail_username"
                                                            value="{{$smtp->mail_username ?? 'Double Click to Configure it'}}"
                                                            readonly />
                                                    </div>


                                                </div>

                                                <br>
                                                <!--
                                                <h5 class="font-weight-bolder">Billing Infos :</h5>
                                                <div class="multisteps-form__content">
                                                    <div class="row mt-4">
                                                        @foreach($entrepriseCharges as $entrepriseCharge)
                                                        <div class="col-12 col-sm-4">
                                                            <label>Charge Name : </label>
                                                            <input id="charge-name-{{ $entrepriseCharge->id }}"
                                                                class="multisteps-form__input form-control" type="text"
                                                                name="chargename"
                                                                value="{{ $entrepriseCharge->charge_name }}" readonly />
                                                        </div>
                                                        <div class="col-12 col-sm-4 mt-3 mt-sm-0">
                                                            <label>Price : </label>
                                                            <input id="price-{{ $entrepriseCharge->id }}"
                                                                class="multisteps-form__input form-control" type="text"
                                                                name="price" value="{{ $entrepriseCharge->price }}$"
                                                                readonly />
                                                        </div>
                                                        <div class="col-10 col-sm-4 p-4 d-flex">
                                                            <form method="post"
                                                                action="{{route('admin.Enterprise_infos.destroy', $entrepriseCharge->id)}}"
                                                                onsubmit="return confirm('Are you sure?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn btn-sm bg-gradient-danger m-2">delete</button>
                                                            </form>

                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                -->
                                                <div class="row">
                                                    <div class="button-row d-flex mt-4 col-12">
                                                        @if(Auth::user()->hasRole('Admin'))
                                                            <a class='btn btn-light m-0' href="{{route('admin.index')}}">
                                                                Back
                                                            </a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                                            <a class='btn btn-light m-0' href="{{route('Responsable_de_projet.index')}}">
                                                                Back
                                                            </a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Employee'))
                                                            <a class='btn btn-light m-0' href="{{route('employee.index')}}">
                                                                Back
                                                            </a>
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                            <a class='btn btn-light m-0' href="{{route('Responsable_gestionnaire.index')}}">
                                                                Back
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>


                                            
                                        </div>
                                    </div>
                                </div>
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
    $(document).ready(function() {
        $(".entrepriseInfos").dblclick(function() {
            $(this).prop('readonly', false);
        });

        $(".entrepriseInfos").blur(function() {
            $(this).prop('readonly', true);
            var newContent = $(this).val();
            var EntrepriseId = "{{ Auth::user()->entreprise_id }}";
            var columnName = $(this).attr('name');
            
            let updateUrl = '';
            @if(Auth::user()->hasRole('Admin'))
                updateUrl = '/admin/Enterprise_infos/updateInfos';
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                updateUrl = '/Responsable_de_projet/Enterprise_infos/updateInfos';
            @elseif(Auth::user()->hasRole('Employee'))
                updateUrl = '/employee/Enterprise_infos/updateInfos';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                updateUrl = '/Responsable_gestionnaire/Enterprise_infos/updateInfos';
            @endif

            $.ajax({
                url: updateUrl,
                type: 'post',
                data: {
                    content: newContent,
                    id: EntrepriseId,
                    column: columnName,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    location.reload();
                },
                error: function(response) {
                    // handle error
                }
            });
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $(".editableSmtp").dblclick(function() {
            $(this).prop('readonly', false);
        });

        $(".editableSmtp").blur(function() {
            $(this).prop('readonly', true);
            var newContent = $(this).val();
            var EntrepriseId = "{{ Auth::user()->entreprise_id }}";
            var columnName = $(this).attr('name');
            
            let updateUrl = '';
            @if(Auth::user()->hasRole('Admin'))
                updateUrl = '/admin/Enterprise_SMTPinfos/updateSMTPInfos';
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                updateUrl = '/Responsable_de_projet/Enterprise_SMTPinfos/updateSMTPInfos';
            @elseif(Auth::user()->hasRole('Employee'))
                updateUrl = '/employee/Enterprise_SMTPinfos/updateSMTPInfos';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                updateUrl = '/Responsable_gestionnaire/Enterprise_SMTPinfos/updateSMTPInfos';
            @endif

            $.ajax({
                url: updateUrl,
                type: 'post',
                data: {
                    content: newContent,
                    id: EntrepriseId,
                    column: columnName,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    location.reload();
                },
                error: function(response) {
                    // handle error
                }
            });
        });
    });
    </script>

    

</body>

</html>