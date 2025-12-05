<table class="table align-items-center mb-0">
                                        <!-- Table header -->
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">
                                                    Project Name</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">
                                                    Client Name</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10 ps-2">
                                                    Start Date</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">
                                                    End Date</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">
                                                    Invoice Type</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">
                                                    Created at</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">
                                                    Actions</th>
                                            </tr>
                                        </thead>
                                        <!-- Table body -->
                                        <tbody>
                                            <!-- Loop through invoices -->
                                            @foreach($invoices as $invoice)
                                            <tr>
                                                <!-- Project Name -->
                                                <td>
                                                    <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                                        <a href="{{$invoice->link}}">{{$invoice->project_name}} </a>
                                                    </p>
                                                </td>
                                                <!-- Client Name -->
                                                <td>
                                                    <p class="align-middle text-center text-sm font-weight-bold mb-0">
                                                        {{$invoice->client_name}}
                                                    </p>
                                                </td>
                                                <!-- Start Date -->
                                                <td>
                                                    <p class="text-center text-sm font-weight-bold mb-0">
                                                        {{$invoice->start_date}}
                                                    </p>
                                                </td>
                                                <!-- End Date -->
                                                <td>
                                                    <p class="text-center text-sm font-weight-bold mb-0">
                                                        {{$invoice->end_date}}
                                                    </p>
                                                </td>
                                                <!-- Invoice Type -->
                                                <td>
                                                    <p class="text-center text-sm font-weight-bold mb-0">
                                                        {{$invoice->type}}
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-center text-sm font-weight-bold mb-0">
                                                        {{$invoice->created_at}}
                                                    </p>
                                                </td>
                                                <!-- Actions -->
                                                <td>
                                                    <div class="card-footer d-flex justify-content-end">
                                                        <!-- Payment Status -->
                                                        @if(\App\Models\invoices_history::where('id',
                                                        $invoice->id)->first()->Payment_status==1)
                                                        <form
                                                            action="{{route('admin.invoice.deactivate', $invoice->id)}}"
                                                            method="post" onsubmit="return confirm('Are you sure?');">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-sm btn-success p-2">Paid</button>
                                                        </form>
                                                        @else
                                                        <form action="{{route('admin.invoice.activate', $invoice->id)}}"
                                                            method="post" onsubmit="return confirm('Are you sure?');">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-sm  btn-danger p-2">Unpaid</button>
                                                        </form>
                                                        @endif
                                                        <!-- View Button -->
                                                        <a class="btn btn-sm btn-primary p-2"
                                                            href="{{$invoice->link}}?number={{$invoice->number}}">View</a>
                                                        <!-- Delete Button -->
                                                        <form action="{{route('admin.invoice.destroy',$invoice->id)}}"
                                                            method="post" onsubmit="return confirm('Are you sure?');"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-sm  btn-danger p-2">Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
