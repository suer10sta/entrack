<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
    textarea.entrepriseInfos {
    text-align: left; /* Ensure text aligns to the left */
    padding-left: 10px; /* Adjust padding as needed */
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
                                                <h3>Invoice Parameters</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-lg-8 m-auto">

                                        <div class="multisteps-form__form mb-8">
                                            <div class="card multisteps-form__panel p-3 border-radius-xl bg-white js-active d-flex flex-column "
                                                data-animation="FadeIn">
                                                <h5 class="font-weight-bolder mb-0">Invoice Infos</h5>
                                                <p class="mb-0 text-sm">Mandatory informations</p>

                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label>Prefix :</label>
                                                        <input
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            type="text" name="invoice_prefix"
                                                            value="{{$entreprise->invoice_prefix}}" readonly />
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-">
                                                        <!-- Use col-md-4 to have 3 inputs in each row on medium screens and above -->
                                                        <label>general condition:</label>
                                                        <textarea
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            name="general_conditions" readonly>
                                                            {{$entreprise->general_conditions}}
                                                        </textarea>
                                                    </div>
                                                </div>

                                                <br>   
                                                
                                                <h5 class="font-weight-bolder mb-0">Invoice Body Messages</h5>
                                                <div class="row">
                                                    <div class="col-md-">
                                                        <!-- Use col-md-4 to have 3 inputs in each row on medium screens and above -->
                                                        <label> Invoice Message :</label>
                                                        <textarea
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            name="invoice_body" readonly>
                                                            {{$entreprise->invoice_body}}
                                                        </textarea>
                                                    </div>
                                                </div>

                                                <br>
                                            
                                                <h5 class="font-weight-bolder mb-0">Invoice Reminder Body Messages</h5>

                                                <div class="row">
                                                    <div class="col-md-">
                                                        <!-- Use col-md-4 to have 3 inputs in each row on medium screens and above -->
                                                        <label>First Reminder Message :</label>
                                                        <textarea
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            name="first_reminder" readonly>
                                                            {{$entreprise->first_reminder}}
                                                        </textarea>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-">
                                                        <!-- Use col-md-4 to have 3 inputs in each row on medium screens and above -->
                                                        <label>Second Reminder Message :</label>
                                                        <textarea
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            name="second_reminder" readonly>
                                                            {{$entreprise->second_reminder}}
                                                        </textarea>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-">
                                                        <!-- Use col-md-4 to have 3 inputs in each row on medium screens and above -->
                                                        <label>Third Reminder Message :</label>
                                                        <textarea
                                                            class="editable entrepriseInfos multisteps-form__inputone form-control"
                                                            name="third_reminder" readonly>
                                                            {{$entreprise->third_reminder}}
                                                        </textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex justify-content-end mt-4">
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
        </nav>
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
            var updatedUrl = '';

            @if(Auth::user()->hasRole('Responsable de projet'))
                updatedUrl = '/Responsable_de_projet/Enterprise_infos/updateInfos';
            @elseif(Auth::user()->hasRole('Admin'))
                updatedUrl = '/admin/Enterprise_infos/updateInfos';
            @elseif(Auth::user()->hasRole('Employee'))
                updatedUrl = '/employee/Enterprise_infos/updateInfos';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                updatedUrl = '/Responsable_gestionnaire/Enterprise_infos/updateInfos';
            @endif 

            $.ajax({
                url: updatedUrl,
                type: 'post',
                data: {
                    content: newContent,
                    id: EntrepriseId,
                    column: columnName,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // handle success
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