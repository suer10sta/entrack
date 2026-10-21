
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@include('dashboard_layouts.head')

<body class="g-sidenav-show bg-gray-100">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>

    <div class="min-height-300 bg-primary position-absolute w-100"></div>

    @include('superadmin.sidebar')

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
                                            <div class="card-body">
                                                <div class="multisteps-form__progress">
                                                    <button class="multisteps-form__progress-btn js-active"
                                                        type="button" title="{{ __('messages.User Info') }}">
                                                        <span>{{ __('messages.User Info') }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-lg-8 m-auto">
                                        <form class="multisteps-form__form mb-8" method="POST" action="{{route('superadmin.users.store')}}" enctype="multipart/form-data">
                                            @csrf    
                                            <div class="card  p-3 border-radius-xl bg-white js-active"
                                                data-animation="FadeIn">
                                                <h5 class="font-weight-bolder mb-0">{{ __('messages.User Infos') }}</h5>
                                                <p class="mb-0 text-sm">{{ __('messages.Mandatory informations') }}</p>
                                                <div class="multisteps-form__content ">
                                                    <div class="row mt-3">
                                                        <div class="col-12 col-sm-6">
                                                            <label>{{ __('messages.Name field') }} </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="text" placeholder="{{ __('messages.Full Name') }}" name="name" />
                                                        </div>
                                                        <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                            <label>{{ __('messages.Email :') }} </label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="text" placeholder="*****@gmail.com" name="email"/>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-3">
                                                        <div class="col-12 col-sm-6">
                                                            <label>{{ __('messages.Company Name :') }}</label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="text" placeholder="{{ __('messages.Company Name') }}" name="entreprise_name"/>
                                                        </div>
                                                        <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                            <label>{{ __('messages.Company Image :') }} </label>
                                                            <input type="file" id="entreprise_image" name="entreprise_image" accept="image/*"
                                                            class="multisteps-form__input form-control">
                                                        </div>
                                                    </div>
                                                    <div class="row mt-3">
                                                        <div class="col-12 col-sm-6">
                                                            <label>{{ __('messages.Password') }}</label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="password" placeholder="******" name="password"/>
                                                        </div>
                                                        <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                            <label>{{ __('messages.Repeat Password') }}</label>
                                                            <input class="multisteps-form__input form-control"
                                                                type="password" placeholder="******" name="password"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
<br>
                                            <div class="card  p-3 border-radius-xl bg-white js-active"
                                                data-animation="FadeIn">
                                                <h5 class="font-weight-bolder mb-0">{{ __('messages.Proffesional Infos') }}</h5>
                                                <p class="mb-0 text-sm">{{ __('messages.Mandatory informations') }}</p>
                                                <div class="multisteps-form__content ">
                                                <div class="row mt-3">
                                                                <div class="col-12 col-sm-6">
                                                                    <label>{{ __('messages.Cnss :') }} </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="{{ __('messages.Cnss Number') }}" name="CNSS" />
                                                                </div>
                                                                <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                                    <label>{{ __('messages.bankAccountNumber :') }} </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="{{ __('messages.bank Account Number') }}" name="bankAccountNumber"/>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                            <div class="col-12 col-sm-6">
                                                                <label>{{ __('messages.Cost Per Hour :') }} <span class="text-danger">*</span></label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="{{ __('messages.Cost Per Hour') }}" name="cost_per_hour"/>
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                <label>{{ __('messages.Salary :') }} </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="text" placeholder="{{ __('messages.salary') }}" name="salary"/>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-12 col-sm-6">
                                                                    <label>{{ __('messages.Cotisation CNSS :') }}</label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="number" placeholder="{{ __('messages.Cotisation CNSS') }}" name="Cotisation_CNSS"/>
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                <label>{{ __('messages.Cotisation AMO :') }} </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="number" placeholder="{{ __('messages.Cotisation AMO') }}" name="Cotisation_AMO"/>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-12 col-sm-6">
                                                                    <label>{{ __('messages.work Hours limit :') }} </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="number" placeholder="{{ __('messages.workHours_limit') }}" name="workHours_limit"/>
                                                                </div>
                                                                <div class="col-12 col">
                                                                    <label>{{ __('messages.Phone number:') }} </label>
                                                                    <input class="multisteps-form__input form-control"
                                                                        type="number" placeholder="{{ __('messages.Phone Number') }}" name="phoneNumber"/>
                                                                </div>
                                                            </div>
                                                    <div class="button-row d-flex mt-4">
                                                        <button class="btn bg-gradient-dark ms-auto mb-0 js-btn-next"
                                                            type="submit" title="{{ __('messages.create') }}">
                                                            {{ __('messages.Create') }}
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

</body>

</html>
