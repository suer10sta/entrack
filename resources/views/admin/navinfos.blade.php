<div class="row">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-12">
                <div class="card mb-4">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">
                                        <a href="{{route('admin.clients.index')}}">Clients</a>
                                    </p>
                                    <h5 class="font-weight-bolder"><a href="{{route('admin.clients.index')}}">{{count($clients)}}</a></h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning  text-center rounded-circle">
                                    <i class="fa-solid fa-users"></i>    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-12">
                <div class="card mb-4">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">
                                        <a href="{{route('admin.employees.index')}}">Employees</a> 
                                    </p>
                                    <h5 class="font-weight-bolder"><a href="{{route('admin.employees.index')}}">{{count($employees)}}</a></h5>
                                </div>
                            </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                <i class="fa-regular fa-user" ></i>                                     
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
