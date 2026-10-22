<table class="table align-items-center mb-0" id="invoiceHitory">
    <thead>
        <tr>
            <th class="text-center text-uppercase text-x font-weight-bolder opacity-10">N</th>
            <th class="text-center text-uppercase text-x font-weight-bolder opacity-10">{{ __('messages.Project Name') }}</th>
            <th class="text-center text-uppercase text-x font-weight-bolder opacity-10">{{ __('messages.Client Name') }}</th>
            <th class="text-center text-uppercase text-x font-weight-bolder opacity-10">{{ __('messages.Invoice Type') }}</th>
            <th class="text-center text-uppercase text-x font-weight-bolder opacity-10">{{ __('messages.Created at') }}</th>
            <th class="text-center text-uppercase text-x font-weight-bolder opacity-10">{{ __('messages.Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoices as $invoice)
        @if ($invoice->Payment_status == NULL)
        <tr>
            <td class="p-0">
                <p class="align-middle text-center text-sm font-weight-bold mb-0">
                    @php
                        $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                        $today = \Carbon\Carbon::now();
                        $isOverdue = $dueDate->isPast() && !$invoice->Payment_status;
                        $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;
                    @endphp
                    
                    <a href="{{ route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]) }}" 
                       target="_blank" 
                       style="{{ $isOverdue ? 'color: red;' : '' }}">
                        <b>{{ \App\Support\InvoiceNumbering::reference($entreprise_infos, $invoice, true) }}</b>
                        @if($isOverdue)
                            <span class="text-danger"><br>({{ $daysOverdue }} days overdue)</span>
                        @endif
                    </a>
                </p>
            </td>
            <td class="p-0">
                <p class="align-middle text-center text-sm font-weight-bold mb-0">
                    <a href="{{route('admin.projects.show',$invoice->project_id)}}"><b>{{strtoupper($invoice->project_name)}}</b> </a>
                </p>
            </td>
            <td class="p-0">
                <p class="align-middle text-center text-sm font-weight-bold mb-0">
                    @php
                    $client = \App\Models\Client::where('id', $invoice->client_id)->first();
                    @endphp
                    <a href="{{route('admin.clients.show', $client->id)}}">
                        {{$client->name}}
                    </a>
                </p>
            </td>
            <td class="p-0">
                <p class="text-center text-sm font-weight-bold mb-0">
                    {{$invoice->type}}
                </p>
            </td>
            <td class="p-0">
                <p class="text-center text-sm font-weight-bold mb-0">
                    {{ $invoice->created_at->format('d-m-Y H:i') }}
                </p>
            </td>
            <td class="p-0">
                <div class="card-footer d-flex justify-content-end p-2">
                    @if(\App\Models\invoices_history::where('id', $invoice->id)->first()->Payment_status==1)
                    <form action="{{route('admin.invoice.deactivate', $invoice->id)}}" method="post" onsubmit="return confirm(@js(__('messages.Are you sure?')));">
                        @csrf
                        <button type="submit" style="height: 30px; width: 55px; text-align: center; line-height: 0px;" class="btn btn-sm btn-success p-2 m-2">{{ __('messages.Paid') }}</button>
                    </form>
                    @else
                    <form action="{{route('admin.invoice.activate', $invoice->id)}}" method="post" onsubmit="return confirm(@js(__('messages.Are you sure?')));">
                        @csrf
                        <button type="submit" style="height: 30px; width: 55px; text-align: center; line-height: 0px;" class="btn btn-sm btn-danger p-2 m-2">{{ __('messages.Unpaid') }}</button>
                    </form>
                    @endif

                    @if ($invoice->sentInvoice == 0)
                    <button type="button" class="btn btn-sm btn-dark p-2 m-2" style="height: 30px; width: 55px; text-align: center; line-height: 0px;" data-bs-toggle="modal" data-bs-target="#sendInvoiceModal" data-invoice-id="{{ $invoice->id }}">
                        {{ __('messages.Send') }}
                    </button>
                    @else
                    <button type="button" class="btn btn-sm btn-dark p-2 m-2" style="height: 30px; width: 55px; text-align: center; line-height: 0px;" data-bs-toggle="modal" data-bs-target="#sendInvoiceModal" data-invoice-id="{{ $invoice->id }}">
                        {{ __('messages.Resend') }}
                    </button>
                    @endif

                    @can('Send invoice')
                    <div class="dropdown m-2">
                        <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ __('messages.Send Reminder') }}
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.invoice.sendManualremind', ['id' => $invoice->id, 'reminder' => 1]) }}">{{ __('messages.Send Reminder 1') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.invoice.sendManualremind', ['id' => $invoice->id, 'reminder' => 2]) }}">{{ __('messages.Send Reminder 2') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.invoice.sendManualremind', ['id' => $invoice->id, 'reminder' => 3]) }}">{{ __('messages.Send Reminder 3') }}</a></li>
                        </ul>
                    </div>
                    @endcan

                    @if($invoice->id === $invoices->max('id') || $invoice->sentInvoice == 0)
                        @if($invoice->type == 'Partial')
                            <a href="{{ route('admin.invoices.editPartialInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">{{ __('messages.Edit') }}</a>
                        @elseif($invoice->type == 'Percentage')
                            <a href="{{ route('admin.invoices.editPercentageInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">{{ __('messages.Edit') }}</a>
                        @elseif($invoice->type == 'Period')
                            <a href="{{ route('admin.invoices.editPeriodInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">{{ __('messages.Edit') }}</a>
                        @elseif($invoice->type == 'Detailed')
                            <a href="{{ route('admin.invoices.editDetailedInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">{{ __('messages.Edit') }}</a>
                        @else
                            <a href="{{ route('admin.invoices.editFullInvoice', $invoice->id) }}" class="btn btn-primary btn-xs m-2" style="height: 30px;">{{ __('messages.Edit') }}</a>
                        @endif
                    @endif

                    @if($invoice->sentInvoice == 0)
                        @if($invoice->id === $invoices->max('id'))
                        <form action="{{route('admin.invoice.destroy',$invoice->id)}}" method="post" onsubmit="return confirm(@js(__('messages.Are you sure?')));" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="height: 30px;" class="btn btn-sm btn-danger p-2 m-2">X</button>
                        </form>
                        @endif
                    @endif
                </div>
            </td>
        </tr>
        @endif
        @endforeach
    </tbody>
</table>

<div class="d-flex justify-content-center mt-4">
    {{ $invoices->appends(request()->except('page'))->links() }}
</div> 
