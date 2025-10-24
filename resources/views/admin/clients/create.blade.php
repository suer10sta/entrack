<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')

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
                                                <h4 class="font-weight-bolder mb-0">Add New Client</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-lg-12 m-auto">
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                        <form class=" mb-8" method="POST" action="{{route('Responsable_de_projet.clients.store')}}" enctype="multipart/form-data" autocomplete="none">
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                        <form class=" mb-8" method="POST" action="{{route('employee.clients.store')}}" enctype="multipart/form-data" autocomplete="none">
                                    @endif
                                    @if(Auth::user()->hasRole('Admin'))
                                        <form class=" mb-8" method="POST" action="{{route('admin.clients.store')}}" enctype="multipart/form-data" autocomplete="none">
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <form class=" mb-8" method="POST" action="{{route('Responsable_gestionnaire.clients.store')}}" enctype="multipart/form-data" autocomplete="none">
                                    @endif
                                            @csrf   
                                            <div class="row">
                                                <div class="col-12 col-md-4">   
                                                    <div class="card  p-3 border-radius-xl bg-white js-active"
                                                        data-animation="FadeIn">
                                                        <h5 class="font-weight-bolder mb-0">Personal Infos</h5>
                                                        <p class="mb-0 text-sm">Mandatory informations</p>
                                                        <div class="multisteps-form__content ">
                                                            <div class="row mt-3">
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Name : <span class="text-danger">*</span> </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="Full Name" name="name" />
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Client Number : <span class="text-danger">*</span></label>
                                                                    @error('client_number')
                                                                        <div class="alert alert-danger mt-1">{{ $message }}</div>
                                                                    @enderror
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="Client Number" name="client_number" />
                                                                </div>
                                                                <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                                    <label>Email : </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="*****@gmail.com" name="email"/>
                                                                </div>
                                                                <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                                    <label>Phone : </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="number" placeholder="Phone Number" name="phone"/>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4">   
                                                    <div class="card  p-3 border-radius-xl bg-white js-active"
                                                        data-animation="FadeIn">
                                                        <h5 class="font-weight-bolder mb-0">Address Infos</h5>
                                                        <p class="mb-0 text-sm">Mandatory informations</p>
                                                        <div class="multisteps-form__content ">
                                                            <div class="row mt-3">
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Street : <span class="text-danger">*</span></label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="Your Full Address Here" name="address" />
                                                                </div>
                                                                <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                                    <label>City : <span class="text-danger">*</span></label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="City" name="city"/>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Zip Code : <span class="text-danger">*</span></label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="number" placeholder="Zip Code " name="zip"/>
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                <label>Country : <span class="text-danger">*</span></label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="Contry" name="country"/>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4"> 
                                                <div class="card  p-3 border-radius-xl bg-white js-active"
                                                    data-animation="FadeIn">
                                                    <h5 class="font-weight-bolder mb-0">Billing Infos</h5>
                                                    <p class="mb-0 text-sm">Mandatory informations</p>
                                                    <div class="multisteps-form__content ">
                                                        <div class="row mt-3">
                                                            <div class="col-12 col-sm-6">
                                                                <label>TVA number : </label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="text" placeholder="TVA number" name="TVA" />
                                                            </div>
                                                            <div class="col-12 col-sm-6">
                                                                <label>Due Date (days): <span class="text-danger">*</span></label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="text" placeholder="Due Date by Days" name="due_date" />
                                                            </div>
                                                        </div>
                                                        <div class="row mt-3">
                                                            <div class="col-12 col sm-6">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" value="" id="billingSameAsClient">
                                                                    <label class="form-check-label" for="billingSameAsClient">
                                                                        Billing address is the same as client address
                                                                    </label>
                                                                </div>
                                                            </div>        
                                                        </div>
                                                        <div class="row mt-3">
                                                            <div class="col-12 col-sm-6">
                                                                <label>Street : </label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="text" placeholder="Billing Street" name="billing_street" />
                                                            </div>
                                                            <div class="col-12 col-sm-6">
                                                                <label>City : </label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="text" placeholder="Billing City" name="billing_city" />
                                                            </div>
                                                        </div>
                                                        <div class="row mt-3">
                                                            <div class="col-12 col-sm-6">
                                                                <label>Zip Code : </label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="text" placeholder="Billing Zip Code" name="billing_zip" />
                                                            </div>
                                                            <div class="col-12 col-sm-6">
                                                                <label>Country : </label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="text" placeholder="Billing Country" name="billing_country" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>  
                                                    
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="card  p-3 border-radius-xl bg-white js-active"
                                                    data-animation="FadeIn">
                                                    <h5 class="font-weight-bolder mb-0">Comptabilité Infos</h5>
                                                    <p class="mb-0 text-sm">Mandatory informations</p>
                                                    <div class="multisteps-form__content ">
                                                        <div class="row mt-3">
                                                            <div class="col-12 col-sm-6">
                                                                <label>Nom de Responsable : <span class="text-danger">**</span></label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="text" placeholder="Nom de Responsable" name="comp_name" />
                                                            </div>
                                                            <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                                <label>Email : <span class="text-danger">**</span></label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="text" placeholder="*****@gmail.com" name="comp_email"/>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-3">
                                                            <div class="col-12 col-sm-6">
                                                                <label>Phone Number :</label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="number" placeholder="Phone Number  " name="comp_phone"/>
                                                            </div>
                                                            <div class="col-12 col-sm-6">
                                                            <label>Address : </label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="text" placeholder="Address" name="comp_address"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="card  p-3 border-radius-xl bg-white js-active"
                                                    data-animation="FadeIn">
                                                    <h5 class="font-weight-bolder mb-0">Contacts Infos</h5>
                                                    <p class="mb-0 text-sm">Mandatory informations</p>
                                                    <div class="multisteps-form__content ">
                                                        <div id="contact_infos">
                                                            <div class="row mt-3">
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Full Name : <span class="text-danger">**</span></label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="Full Name" name="contact_name[]" />
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Function : </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="Functionality" name="contact_function[]" />
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Phone Number : </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="number" placeholder="Phone Number " name="contact_mobile_fix[]" />
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                    <label>Email : <span class="text-danger">**</span></label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="*****@gmail.com" name="contact_email[]" />
                                                                </div>
                                                                <div class="col-12 col-sm-12">
                                                                    <label>Address : </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="Address" name="contact_address[]" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <br>
                                                    <button id="addContact" class="btn btn-primary">Add</button>
                                                    <div class="button-row d-flex mt-4">
                                                    <a class='btn btn-light m-0' href="{{route('admin.index')}}">
                                                        Cancel
                                                    </a>
                                                        <button class="btn bg-gradient-dark ms-auto mb-0 "
                                                            type="submit" title="create">
                                                            Create
                                                        </button>
                                                    </div>
                                                </div>
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
        $(document).ready(function () {
            $(".multisteps-form__progress-btn").click(function () {
                var index = $(this).index();
                $(".multisteps-form__panel").removeClass("js-active");
                $(".multisteps-form__panel").eq(index).addClass("js-active");
                $(".multisteps-form__progress-btn").removeClass("js-active");
                $(this).addClass("js-active");
            });

            $(".js-btn-next").click(function(e){
                e.preventDefault();
                var currentCard = $(this).closest('.multisteps-form__panel');
                var nextCard = currentCard.next('.multisteps-form__panel');
                if (nextCard.length > 0) {
                    currentCard.removeClass("js-active");
                    nextCard.addClass("js-active");
                    var nextIndex = $(".multisteps-form__panel").index(nextCard);
                    $(".multisteps-form__progress-btn").removeClass("js-active");
                    $(".multisteps-form__progress-btn").eq(nextIndex).addClass("js-active");
                } 
            });
        });

    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function(){
    $("#addContact").click(function(e){
        e.preventDefault();
        var additionalContacts = $("#contact_infos");
        var newCard = additionalContacts.children().first().clone();
        newCard.find('input').val('');
        if (additionalContacts.children().length > 0) {
            var deleteButton = $('<button>Delete</button>');
            deleteButton.attr('type', 'button');
            deleteButton.addClass('btn btn-danger mt-3 remove-contact');
            deleteButton.click(function() {
                newCard.remove();
            });
            newCard.append(deleteButton);
        }
        additionalContacts.append(newCard);
    });
});
$(document).ready(function(){
    $("#addContact").click(function(e){
        e.preventDefault();
        var newCard = '<div class="card multisteps-form__panel p-3 border-radius-xl bg-white js-active" data-animation="FadeIn">...</div>';
        $("#additionalContacts").append(newCard);
    });
});

</script>

</body>

</html>