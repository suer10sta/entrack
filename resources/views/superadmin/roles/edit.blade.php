
<!DOCTYPE html>
<html lang="en">

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
                                                        type="button" title="User Info">
                                                        <span>Role Info</span>
                                                    </button>

                                                    <button class="multisteps-form__progress-btn" type="button"
                                                        title="Address">
                                                        Assign Permissions
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-lg-8 m-auto">
                                        <form class="multisteps-form__form mb-8" method="POST"
                                            action="{{route('superadmin.roles.update',$role)}}"">
                                            @csrf    
                                            @method('PUT')
                                        <div class=" card multisteps-form__panel p-3 border-radius-xl bg-white
                                            js-active" data-animation="FadeIn">
                                            <h5 class="font-weight-bolder mb-0">Role Infos</h5>
                                            <p class="mb-0 text-sm">Mandatory informations</p>
                                            <div class="multisteps-form__content ">
                                                <div class="row mt-3">
                                                    <div class="col-12 col-sm-6">
                                                        <label>Name : </label>
                                                        <input class="multisteps-form__input form-control" type="text"
                                                            name="name" value="{{$role->name}}" />
                                                    </div>
                                                </div>
                                                <div class="button-row d-flex mt-4">
                                                    <button class="btn bg-gradient-dark ms-auto mb-0 js-btn-next"
                                                        type="submit" title="create">
                                                        Save
                                                    </button>
                                                </div>
                                            </div>
                                    </div>
                                    </form>
                                    <form class="multisteps-form__form mb-8" method="POST"
                                        action="{{route('superadmin.roles.assign_permission',$role->id)}}">
                                    @csrf
                                    <div class=" card multisteps-form__panel p-3 border-radius-xl bg-white"
                                        data-animation="FadeIn">
                                        <h5 class="font-weight-bolder">Assign Roles :</h5>
                                        <div class="multisteps-form__content">
                                            <div class="row mt-3">
                                                @foreach($permissions as $per)
                                                @if($role->hasPermissionTo($per))
                                                <div class="form-check">
                                                    <input id="vue-checkbox" name="permissions[]" type="checkbox" value="{{ $per->name }}" checked>
                                                    <label for="vue-checkbox" class="form-check-label"">
                                                        @php
                                                            $permissionRawName = trim($per->name);
                                                            $permissionCandidates = array_values(array_unique([
                                                                $permissionRawName,
                                                                strtolower($permissionRawName),
                                                                strtoupper($permissionRawName),
                                                                str_replace(['_', '-'], ' ', $permissionRawName),
                                                                ucwords(strtolower(str_replace(['_', '-'], ' ', $permissionRawName))),
                                                                str_replace(' ', '_', $permissionRawName),
                                                                strtolower(str_replace(' ', '_', $permissionRawName)),
                                                                strtoupper(str_replace(' ', '_', $permissionRawName)),
                                                            ]));
                                                            $permissionLabel = $permissionRawName;
                                                            foreach ($permissionCandidates as $candidate) {
                                                                $candidateKey = 'messages.' . $candidate;
                                                                $translated = __($candidateKey);
                                                                if ($translated !== $candidateKey) {
                                                                    $permissionLabel = $translated;
                                                                    break;
                                                                }
                                                            }
                                                        @endphp
                                                        {{ $permissionLabel }}
                                                    </label>
                                                    
                                                </div>
                                                @else
                                                <div class="form-check">
                                                    <input id="vue-checkbox" name="permissions[]" type="checkbox" value="{{ $per->name }}" >
                                                    <label for="vue-checkbox" class="form-check-label"">
                                                        @php
                                                            $permissionRawName = trim($per->name);
                                                            $permissionCandidates = array_values(array_unique([
                                                                $permissionRawName,
                                                                strtolower($permissionRawName),
                                                                strtoupper($permissionRawName),
                                                                str_replace(['_', '-'], ' ', $permissionRawName),
                                                                ucwords(strtolower(str_replace(['_', '-'], ' ', $permissionRawName))),
                                                                str_replace(' ', '_', $permissionRawName),
                                                                strtolower(str_replace(' ', '_', $permissionRawName)),
                                                                strtoupper(str_replace(' ', '_', $permissionRawName)),
                                                            ]));
                                                            $permissionLabel = $permissionRawName;
                                                            foreach ($permissionCandidates as $candidate) {
                                                                $candidateKey = 'messages.' . $candidate;
                                                                $translated = __($candidateKey);
                                                                if ($translated !== $candidateKey) {
                                                                    $permissionLabel = $translated;
                                                                    break;
                                                                }
                                                            }
                                                        @endphp
                                                        {{ $permissionLabel }}
                                                    </label>
                                                </div>
                                                @endif
                                                @endforeach
                                            </div>
                                            <div class="row">
                                                <div class="button-row d-flex mt-4 col-12">
                                                    <button class="btn bg-gradient-dark ms-auto mb-0 js-btn-next"
                                                        type="submit">
                                                        Assign
                                                    </button>
                                                </div>
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

            </div>

    </main>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            $(".multisteps-form__progress-btn").click(function () {
                var index = $(this).index();
                $(".multisteps-form__panel").removeClass("js-active");
                $(".multisteps-form__panel").eq(index).addClass("js-active");
                $(".multisteps-form__progress-btn").removeClass("js-active");
                $(this).addClass("js-active");
            });
        });
    </script>
    @include('dashboard_layouts.scripts')

</body>

</html>
