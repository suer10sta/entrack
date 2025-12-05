<div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="card mb-4">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">
                                                    @can('View projects')
                                                        <a href="{{ route('Responsable_de_projet.projects.index') }}">Active projects</a>
                                                    @else
                                                        Active projects
                                                    @endif
                                                    </p>
                                                    <h5 class="font-weight-bolder">{{count($projects)}}</h5>
                                                   
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div
                                                    class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                                    <i class="fa-solid fa-briefcase" ></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="card mb-4">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">
                                                    @can('View tasks')
                                                        <a href="{{ route('Responsable_de_projet.tasks.index') }}">Tasks</a>
                                                    @else
                                                        Tasks                                                    
                                                    @endif
                                                    </p>
                                                    <h5 class="font-weight-bolder">{{count($tasks)}}</h5>
                                                    
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div
                                                    class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                                    <i class="fa-solid fa-list-check"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="card mb-4">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">
                                                        <a href="{{route('employee.teams.index')}}">Teams</a>
                                                    </p>
                                                    <h5 class="font-weight-bolder">{{count($teams)}}</h5>
                                                    
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div
                                                    class="icon icon-shape bg-gradient-warning  text-center rounded-circle">
                                                    <i class="fa-solid fa-users"></i>           
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
