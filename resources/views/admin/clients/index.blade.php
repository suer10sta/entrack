<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
  a {
    color: blue;
}
.font-weight-bolder{
    color : black !important;
}
th {
    font-size: 14px !important; 
}
td, td * {
    color: #332f2f !important;
}
td a {
    color: blue !important;
}
</style>

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

      </div>

      <div class="container-fluid py-4">

        <div class="row mt-3">
          <div class="col-12">
            <div class="card">
              <div class="card-header p-3 pb-0">
                <div class="d-flex justify-content-between">
                    <h6>clients Table</h6>
                    <div>
                      
                      @if(Auth::user()->hasRole('Responsable de projet'))
                        @can('Add client')
                          <a type="button" class="btn btn-primary" href="{{route('Responsable_de_projet.clients.create')}}">Add Client</a> 
                        @endcan
                        <a type="button" class="btn btn-primary" href="{{route('Responsable_de_projet.Clientrepports.downloadRepports')}}">Client Reports PDF</a>
                      @endif
                      @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        @can('Add client')
                          <a type="button" class="btn btn-primary" href="{{route('Responsable_gestionnaire.clients.create')}}">Add Client</a> 
                        @endcan
                        <a type="button" class="btn btn-primary" href="{{route('Responsable_gestionnaire.Clientrepports.downloadRepports')}}">Client Reports PDF</a>
                      @endif
                      @if(Auth::user()->hasRole('Admin'))
                        @can('Add client')
                          <a type="button" class="btn btn-primary" href="{{route('admin.clients.create')}}">Add Client</a> 
                        @endcan
                        <a type="button" class="btn btn-primary" href="{{route('admin.Clientrepports.downloadRepports')}}">Client Reports PDF</a>
                      @endif
                    </div>
                </div>
                  @if(session('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                    </div>
                  @endif
              </div>
              <div class="table-responsive p-4 pt-1">
                <table id="example" class="table table-flush" style="width:100%">
                  <thead class="thead-light">
                    <tr class="text-center">
                      <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Client N</th>
                      <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Name</th>
                      <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Email</th>
                      <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Phone</th>
                      <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Billing Address</th>
                      <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> TVA</th>
                      <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Due Date</th>
                      <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Address</th>
                      <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> City</th>
                      <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Zip</th>
                      <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Country</th>
                      @can('Delete client')
                      <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"> Action</th>
                      @endcan
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($clients as $client)
                    <tr class="text-center">
                      <td class="editable" data-id="{{$client->id}}" data-column="client_number">

                        {{$client->client_number}}
                      </td>
                      <td class="editable" data-id="{{$client->id}}" data-column="name">
                        @if(Auth::user()->hasRole('Responsable de projet'))
                          @if(Auth::user()->can('View client'))
                            <a href="{{route('Responsable_de_projet.clients.show', $client->id)}}">
                              <b style="color: blue !important;">{{$client->name}}</b>
                            </a>
                            @else
                            <b>{{$client->name}}</b>
                          @endif
                        @endif
                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                          @if(Auth::user()->can('View client'))
                            <a href="{{route('Responsable_gestionnaire.clients.show', $client->id)}}">
                              <b style="color: blue !important;">{{$client->name}}</b>
                            </a>
                            @else
                            <b>{{$client->name}}</b>
                          @endif
                        @endif
                        @if(Auth::user()->hasRole('Admin'))
                          @if(Auth::user()->can('View client'))
                            <a href="{{route('admin.clients.show', $client->id)}}">
                              <b style="color: blue !important;">{{$client->name}}</b>
                            </a>
                            @else
                            <b>{{$client->name}}</b>
                          @endif
                        @endif
                      </td>

                      <td class="editable" data-id="{{$client->id}}" data-column="email">

                        {{$client->email}}
                      </td>

                      <td class="editable" data-id="{{$client->id}}" data-column="phone">

                        {{$client->phone}}
                      </td>

                      <td class="editable" data-id="{{$client->id}}" data-column="Billing_address">

                        {{$client->Billing_address}}
                      </td>

                      <td class="editable" data-id="{{$client->id}}" data-column="TVA">

                        {{$client->TVA}}
                      </td>

                      <td class="editable" data-id="{{$client->id}}" data-column="due_date">

                        {{$client->due_date}}
                      </td>

                      <td class="editable" data-id="{{$client->id}}" data-column="address">

                        {{$client->address}}
                      </td>

                      <td class="editable" data-id="{{$client->id}}" data-column="city">

                        {{$client->city}}
                      </td>

                      <td class="editable" data-id="{{$client->id}}" data-column="zip">

                        {{$client->zip}}
                      </td>

                      <td class="editable" data-id="{{$client->id}}" data-column="country">

                        {{$client->country}}
                      </td>

                      @php
                        $client_has_invoice = \App\Models\invoices_history::where('client_id', $client->id)->get();
                      @endphp
                      @can('Delete client')
                      <td>
                        @if( count($client_has_invoice) > 0)
                          <div class="delete-warning">You can't delete this client <br> Because he has invoices</div>
                          @else

                          @if(Auth::user()->hasRole('Responsable de projet'))
                            <form action="{{route('Responsable_de_projet.clients.destroy',$client->id) }}" method="post"
                              onsubmit="return confirm('Are you sure?');" class="d-inline">
                          @endif
                          @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            <form action="{{route('Responsable_gestionnaire.clients.destroy',$client->id) }}" method="post"
                              onsubmit="return confirm('Are you sure?');" class="d-inline">
                          @endif
                          @if(Auth::user()->hasRole('Admin'))
                            <form action="{{route('admin.clients.destroy',$client->id) }}" method="post"
                            onsubmit="return confirm('Are you sure?');" class="d-inline">
                          @endif
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" style="color: white !important;">Delete</button>
                          </form>
                        @endif
                      </td>
                      @endcan
                      
                    </tr>
                    @endforeach
                  </tbody>

                </table>
              </div>

            </div>
          </div>
        </div>



  </main>

<style>
  .delete-warning {
    color: #D8000C; /* Red color for the warning text */
    background-color: #FFD2D2; /* Light red background */
    padding: 5px; /* Padding around the text */
    margin: 8px 0; /* Margin to separate it from other elements */
    border-radius: 5px; /* Rounded corners */
    border: 1px solid #D8000C; /* Red border */
    font-weight: bold; /* Make the text bold */
}
</style>
  @include('dashboard_layouts.scripts')
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script>
  $(document).ready(function(){
    $(".editable").dblclick(function(){
        var originalContent = $(this).text().trim();
        var width = $(this).width();
        $(this).html("<input type='text' value='" + originalContent + "' style='width: 100%; min-width: " + width + "px;' />");
        var input = $(this).children().first();
        input.focus();

        // Place cursor at the beginning of the input
        input[0].setSelectionRange(0, 0);

        input.blur(function(){
            var newContent = $(this).val();
            var clientId = $(this).parent().data('id');
            var columnName = $(this).parent().data('column');
            $(this).parent().text(newContent);
            $(this).parent().removeClass("cellEditing");

            // Make AJAX call to update the database here
            $.ajax({
                url: 'clients/updateClient', // your update route
                type: 'post',
                data: {
                    content: newContent,
                    id: clientId,
                    column: columnName,
                    _token: '{{ csrf_token() }}' // add CSRF token for Laravel
                },
                success: function(response) {
                    // Refresh the page after the update is successful
                    location.reload();
                }
            });
        });
    });
});
  </script>


</body>

</html>