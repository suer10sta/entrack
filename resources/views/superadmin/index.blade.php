<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')

<body class="g-sidenav-show bg-gray-100 dt-dashboard">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>
    <div class="min-height-300 bg-primary position-absolute w-100"></div>

    @include('superadmin.sidebar')

    <main class="main-content position-relative border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
            id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">

                @include('dashboard_layouts.navbar')

            </div>

            <div class="container-fluid py-4">
                @include('superadmin.navinfo')

                <div class="row">
                    <!-- SALES OVERVIEW CARD -->
                    <div class="col-lg-7 mb-4 mb-lg-0">
                        <div class="card z-index-2 h-100 dt-chart-card">
                            <div class="card-header pb-0 pt-3 bg-transparent">
                                <h6 class="text-capitalize">{{ __('messages.Sales overview') }}</h6>
                                <p class="text-sm mb-0">
                                    <i class="fa fa-arrow-up text-success"></i>
                                    <span class="font-weight-bold">{{ __('messages.4% more') }}</span> {{ __('messages.in 2023') }}
                                </p>
                            </div>
                            <div class="card-body p-3">
                                <div class="chart">
                                    <canvas id="chart-line" class="chart-canvas" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ACTIVE ENTREPRISES CARD -->
                    <div class="col-lg-5">
                        <div class="card card-carousel overflow-hidden h-100 p-0 dt-chart-card">
                            <div id="carouselExampleCaptions" class="carousel slide h-100" data-bs-ride="carousel">

                                <div class="card-header p-3 pb-0">
                                    <h6>{{ __('messages.Active Entreprises By Duration') }}</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="chart">
                                        <canvas id="pie-chart" class="chart-canvas" height="300"></canvas>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card dt-table-card">
                            <div class="card-header p-3 pb-0">
                                <h6>{{ __('messages.Sales table') }}</h6>
                            </div>
                            <div class="table-responsive p-4 pt-1">
                                <table id="example" class="table table-flush" style="width:100%">
                                    <thead class="thead-light">
                                        <tr class="text-center">
                                            <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">{{ __('messages.Entreprise') }}</th>
                                            <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">{{ __('messages.Price') }}</th>
                                            <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">{{ __('messages.Period') }}</th>
                                            <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">{{ __('messages.Status') }}</th>
                                            <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">{{ __('messages.Start date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="text-center">
                                            <td>
                                                <img class="avatar avatar-sm rounded-circle me-2"
                                                    src="{{ asset('entreprise_images/default.jpg')}}"
                                                    alt="Entreprise Image">
                                                Entreprise 1
                                            </td>
                                            <td class="text-success">100$</td>
                                            <td>6 months</td>
                                            <td>
                                                <span class="badge badge-success badge-sm">{{ __('messages.Paid') }}</span>
                                            </td>
                                            <td>2024-01-25</td>
                                        </tr>
                                        <tr class="text-center">
                                            <td>
                                                <img class="avatar avatar-sm rounded-circle me-2"
                                                    src="{{ asset('entreprise_images/1708333067.jpg')}}"
                                                    alt="Entreprise Image">
                                                Entreprise 2
                                            </td>
                                            <td class="text-success">80$</td>
                                            <td>3months</td>
                                            <td>
                                                <span class="badge badge-success badge-sm">{{ __('messages.Paid') }}</span>
                                            </td>
                                            <td>2024-02-04</td>
                                        </tr>
                                        <tr class="text-center">
                                            <td>
                                                <img class="avatar avatar-sm rounded-circle me-2"
                                                    src="{{ asset('entreprise_images/default.jpg')}}"
                                                    alt="Entreprise Image">
                                                Entreprise 3
                                            </td>
                                            <td class="text-success">220$</td>
                                            <td>1 year</td>
                                            <td>
                                                <span class="badge badge-success badge-sm">{{ __('messages.Paid') }}</span>
                                            </td>
                                            <td>2023-05-24</td>
                                        </tr>
                                        <tr class="text-center">
                                            <td>
                                                <img class="avatar avatar-sm rounded-circle me-2"
                                                    src="{{ asset('entreprise_images/1708333067.jpg')}}"
                                                    alt="Entreprise Image">
                                                Entreprise 4
                                            </td>
                                            <td class="text-success">40$</td>
                                            <td>1 months</td>
                                            <td>
                                                <span class="badge badge-danger badge-sm">{{ __('messages.Unpaid') }}</span>
                                            </td>
                                            <td>2024-01-04</td>
                                        </tr>
                                    </tbody>

                                </table>
                            </div>

                        </div>
                    </div>
                </div>



    </main>

    

    @include('dashboard_layouts.scripts')
    <script>
    // Pie chart
    var ctx4 = document.getElementById("pie-chart").getContext("2d");

    new Chart(ctx4, {
        type: "pie",
        data: {
            labels: ["1 months", "4 months", "6 months", "1 year"],
            datasets: [{
                label: "Projects",
                weight: 9,
                cutout: 0,
                tension: 0.9,
                pointRadius: 2,
                borderWidth: 2,
                backgroundColor: ["#0B2638", "#2D7080", "#E7B84B", "#9db2bb"],
                data: [15, 20, 12, 60],
                fill: false,
            }, ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
            },
            interaction: {
                intersect: false,
                mode: "index",
            },
            scales: {
                y: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false,
                    },
                    ticks: {
                        display: false,
                    },
                },
                x: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false,
                    },
                    ticks: {
                        display: false,
                    },
                },
            },
        },
    });
    </script>


</body>

</html>
