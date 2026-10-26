<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@include('dashboard_layouts.head')
@include('partials.edit-layout-styles')

<body class="g-sidenav-show bg-gray-100">

    <div class="min-height-300 bg-primary position-absolute w-100"></div>

    @if(Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Employee') || Auth::user()->hasRole('Responsable de projet et gestionnaire'))
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


                <div class="dt-edit-container container-fluid py-4">
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="multisteps-form mb-5">
                                <div class="row">
                                    <div class="dt-edit-column col-12 col-lg-8 mx-auto my-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="multisteps-form__progress">
                                                    <button class="multisteps-form__progress-btn js-active"
                                                        type="button" title="{{ __('messages.client Info') }}">
                                                        <span>{{ __('messages.Personal Infos') }}</span>
                                                    </button>
                                                    <button class="multisteps-form__progress-btn js-active"
                                                        type="button" title="{{ __('messages.client Info') }}">
                                                        <span>{{ __('messages.Address Infos') }}</span>
                                                    </button>
                                                    <button class="multisteps-form__progress-btn js-active"
                                                        type="button" title="{{ __('messages.client Info') }}">
                                                        <span>{{ __('messages.Billing Infos') }}</span>
                                                    </button>
                                                    <button class="multisteps-form__progress-btn js-active"
                                                        type="button" title="{{ __('messages.client Info') }}">
                                                        <span>{{ __('messages.Contacts label') }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="dt-edit-column col-12 col-lg-8 m-auto">
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                        <form class="multisteps-form__form mb-8" method="POST" action="{{ route('Responsable_de_projet.clients.update', $client) }}">
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                        <form class="multisteps-form__form mb-8" method="POST" action="{{ route('employee.clients.update', $client) }}">
                                    @endif
                                    @if(Auth::user()->hasRole('Admin'))
                                        <form class="multisteps-form__form mb-8" method="POST" action="{{ route('admin.clients.update', $client) }}">
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <form class="multisteps-form__form mb-8" method="POST" action="{{ route('Responsable_gestionnaire.clients.update', $client) }}">
                                    @endif
                                            @csrf
                                            @method('PUT')

                                            <div class="card multisteps-form__panel p-3 border-radius-xl bg-white js-active"
                                                data-animation="FadeIn">
                                                <h5 class="font-weight-bolder mb-0">{{ __('messages.Personal Infos Section') }}</h5>
                                                <p class="mb-0 text-sm">{{ __('messages.Mandatory informations') }}</p>
                                                <div class="multisteps-form__content ">
                                                    @php
                                                        $countriesList = json_decode(file_get_contents(base_path('countries.json')), true) ?? [];
                                                    @endphp
                                                    <div class="row mt-3">
                                                        <div class="col-12 col-sm-6">
                                                            <label>{{ __('messages.Name field') }} </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="text" value="{{$client->name}}" name="name" />
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <label>{{ __('messages.Client Number field') }} </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="number" value="{{$client->client_number}}" name="client_number" />
                                                        </div>
                                                        <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                            <label>{{ __('messages.Email :') }} </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="text" value="{{$client->email}}" name="email"/>
                                                        </div>
                                                        <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                            <label>{{ __('messages.Phone :') }} </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="number" value="{{$client->phone}}" name="phone"/>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="button-row d-flex mt-4">
                                                    <button id='next' class="btn bg-gradient-primary ms-auto mb-0 js-btn-next"
                                                         title="{{ __('messages.next') }}">
                                                        {{ __('messages.Next') }}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card multisteps-form__panel p-3 border-radius-xl bg-white js-active"
                                                data-animation="FadeIn">
                                                <h5 class="font-weight-bolder mb-0">{{ __('messages.Address Infos') }}</h5>
                                                <p class="mb-0 text-sm">{{ __('messages.Mandatory informations') }}</p>
                                                <div class="multisteps-form__content ">
                                                    <div class="row mt-3">
                                                        <div class="col-12 col-sm-6">
                                                            <label>{{ __('messages.Address :') }} </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="text" value="{{$client->address}}" name="address" />
                                                        </div>
                                                        <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                            <label>{{ __('messages.City field') }} </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="text" value="{{$client->city}}" name="city"/>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-3">
                                                        <div class="col-12 col-sm-6">
                                                            <label>{{ __('messages.Zip Code field') }}</label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="number" value="{{$client->zip}}" name="zip"/>
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <label>{{ __('messages.Country field') }} </label>
                                                            <select class="multisteps-form__input form-control"
                                                                name="country">
                                                                @foreach($countriesList as $country)
                                                                    <option value="{{ $country['name'] }}"
                                                                        @if($client->country === $country['name']) selected @endif>
                                                                        {{ __('countries.' . $country['code']) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="button-row d-flex mt-4">
                                                    <button id='next' class="btn bg-gradient-primary ms-auto mb-0 js-btn-next"
                                                         title="{{ __('messages.next') }}">
                                                        {{ __('messages.Next') }}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card multisteps-form__panel p-3 border-radius-xl bg-white js-active"
                                                data-animation="FadeIn">
                                                <h5 class="font-weight-bolder mb-0">{{ __('messages.Billing Infos') }}</h5>
                                                <p class="mb-0 text-sm">{{ __('messages.Mandatory informations') }}</p>
                                                <div class="multisteps-form__content ">
                                                    <div class="row mt-3">
                                                        <div class="col-12 col-sm-6">
                                                            <label>{{ __('messages.TVA number :') }} </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="number" value="{{$client->TVA}}" name="TVA" />
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <label>{{ __('messages.Due Date :') }} </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="text" value="{{$client->due_date}}" name="due_date" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="button-row d-flex mt-4">
                                                    <button id='next' class="btn bg-gradient-primary ms-auto mb-0 js-btn-next"
                                                         title="{{ __('messages.next') }}">
                                                        {{ __('messages.Next') }}
                                                    </button>
                                                </div>
                                            </div>
                                            @php
                                                $contacts=App\Models\client_contacts::where('client_id',$client->id)->get();
                                                $i=1;
                                            @endphp
                                            <div class="card multisteps-form__panel p-3 border-radius-xl bg-white js-active"
                                                data-animation="FadeIn">
                                                <div id="accordion">
                                                    @foreach ($contacts as $contact)
                                                        <div class="card ">
                                                                <h5 class="">
                                                                    <button class="btn btn-link" style='color:black;' type="button" data-toggle="collapse" data-target="#collapse{{$i}}" aria-expanded="true" aria-controls="collapse{{$i}}">
                                                                        {{ ucfirst($contact->name) }} Contact  Infos
                                                                    </button>
                                                                    <button  class="btn btn-link ml-auto arrow-button" type="button" data-toggle="collapse" data-target="#collapse{{$i}}" aria-expanded="true" aria-controls="collapse{{$i}}">
                                                                        <i class="fas fa-chevron-down arrow"></i>
                                                                    </button>
                                                                </h5>

                                                            <div id="collapse{{$i}}" class="collapse" aria-labelledby="heading{{$i}}" data-parent="#accordion">
                                                                <div class="card-body">
                                                                    <div class="row mt-3">
                                                                        <div class="col-12 col-sm-6">
                                                                            <label>{{ __('messages.Full Name :') }} </label>
                                                                            <input class="multisteps-form__input form-control" type="text" value="{{$contact->name}}" name="contact_name[]"   />
                                                                        </div>
                                                                        <div class="col-12 col-sm-6">
                                                                            <label>{{ __('messages.Functionality :') }} </label>
                                                                            <input class="multisteps-form__input form-control" type="text" value="{{$contact->functionality}}" name="contact_function[]"   />
                                                                        </div>
                                                                        <div class="col-12 col-sm-6">
                                                                            <label>{{ __('messages.Mobile Fix :') }} </label>
                                                                            <input class="multisteps-form__input form-control" type="number" value="{{$contact->mobile_fix}}" name="contact_mobile_fix[]"   />
                                                                        </div>
                                                                        <div class="col-12 col-sm-6">
                                                                            <label>{{ __('messages.Email :') }} </label>
                                                                            <input class="multisteps-form__input form-control" type="text" value="{{$contact->email}}" name="contact_email[]"   />
                                                                        </div>
                                                                        <div class="col-12 col-sm-6">
                                                                            <label>{{ __('messages.Address :') }} </label>
                                                                            <input class="multisteps-form__input form-control" type="text" value="{{$contact->address}}" name="contact_address[]"  />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <br>
                                                        </div>
                                                        @php
                                                            $i++;
                                                        @endphp
                                                    @endforeach
                                                </div>
                                                <br>
                                                <h5>{{ __('messages.Add New Contacts :') }}</h5>
                                                <div class="multisteps-form__content ">
                                                    <div id="contact_infos">
                                                        <div class="row mt-3">
                                                            <div class="col-12 col-sm-6">
                                                                <label>{{ __('messages.Full Name :') }} </label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="text" placeholder="{{ __('messages.Full Name') }}" name="contact_name[]" />
                                                            </div>
                                                            <div class="col-12 col-sm-6">
                                                                <label>{{ __('messages.Functionality :') }} </label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="text" placeholder="{{ __('messages.Functionality') }}" name="contact_function[]" />
                                                            </div>
                                                            <div class="col-12 col-sm-6">
                                                                <label>{{ __('messages.Mobile Fix :') }} </label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="number" placeholder="{{ __('messages.Mobile Fix') }}" name="contact_mobile_fix[]" />
                                                            </div>
                                                            <div class="col-12 col-sm-6">
                                                                <label>{{ __('messages.Email :') }} </label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="text" placeholder="*****@gmail.com" name="contact_email[]" />
                                                            </div>
                                                            <div class="col-12 col-sm-6">
                                                                <label>{{ __('messages.Address :') }} </label>
                                                                <input class="multisteps-form__input form-control"
                                                                    type="text" placeholder="{{ __('messages.Address') }}" name="contact_address[]" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <br>
                                                <button id="addContact" class="btn btn-primary">{{ __('messages.Add') }}</button>
                                                <div class="button-row d-flex mt-4">
                                                <a class='btn btn-light m-0' href="{{route('admin.index')}}">
                                        {{ __('messages.Cancel') }}
                                    </a>
                                                    <button class="btn bg-gradient-dark ms-auto mb-0 "
                                                        type="submit" title="{{ __('messages.create') }}">
                                                        {{ __('messages.Save') }}
                                                    </button>
                                                </div>
                                            </div>
                                                


                                        </form>

                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>

            </div>

    </main>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

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
        $(document).ready(function() {
            $('.arrow-button').click(function() {
                var arrow = $(this).find('.arrow');
                if (arrow.hasClass('fa-chevron-down')) {
                    arrow.removeClass('fa-chevron-down');
                    arrow.addClass('fa-chevron-up');
                } else {
                    arrow.removeClass('fa-chevron-up');
                    arrow.addClass('fa-chevron-down');
                }
            });
        });
    </script>
    <script>
$(document).ready(function(){
    $("#addContact").click(function(e){
        e.preventDefault();
        var additionalContacts = $("#contact_infos");
        var newCard = additionalContacts.children().first().clone();
        newCard.find('input').val('');
        if (additionalContacts.children().length > 0) {
            var deleteButton = $(('<button>' + @js(__('messages.Delete')) + '</button>'));
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
    @include('dashboard_layouts.scripts')

</body>

</html>
