@extends('layouts.app', ['title' => __('Site Setting')])

@section('content')

    {{-- @include('users.partials.header', [
        'title' => __('All User Settings') . ' ' . auth()->user()->name,
        'description' => __(
            'This is your settings page. You can update the setting that may affect your app\'s business logic.'
        ),
        'class' => 'col-lg-7',
    ]) --}}
    <div class="header bg-primary pb-6 pt-5 pt-lg-6">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-center py-4">
                    <div class="col-lg-12 col-12">
                        <h6 class="h2 text-white d-inline-block mb-0">Ledgers</h6>
                        <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                            <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                                <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#">Ledgers</a></li>
                                <li class="breadcrumb-item active" aria-current="page">All ledgers</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt--6">
        <div class="row">

            <div class="col">
                <div class="card bg-secondary shadow">
                    <div class="card-header bg-white border-0">
                            <h3 class="mb-0">{{ __('All Ledgers') }}</h3>
                    </div>
                    <div class="card-body pt--3">

                        @if(Session::has('success'))
                            <div id="success-alert" class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ Session::get('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(Session::has('error'))
                            <div id="error-alert" class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ Session::get('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="table-responsive" id="all-users-table-styling">
                            <div>
                                <table class="table align-items-center data-table" >
                                    <thead class="thead-light">
                                        <tr>
                                            <th scope="col">No</th>
                                            <th scope="col">User Id</th>
                                            <th scope="col">Payment Id</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Type</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Transaction Date & Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" style="text-align:right">Total Amount:</th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>    
                        </div>

                        <!-- For Datatables -->
                        <script type="text/javascript">
                            $(function () {
                                var table = $('.data-table').DataTable({
                                    processing: true,
                                    serverSide: true,
                                    ajax: "{{ route('allledgers') }}",
                                    columns: [
                                        {data: 'id', name: 'id'},
                                        {data: 'user_by', name: 'user_by'},
                                        {data: 'payment_id', name: 'payment_id'},
                                        {data: 'title', name: 'title'},
                                        {data: 'type', name: 'type'},
                                        {data: 'amount', name: 'amount'},
                                        {
                                            data: 'created_at', 
                                            name: 'created_at', 
                                            orderable: true, 
                                            searchable: true,
                                            render: function(data) {
                                                return moment(data).format('Do MMM YYYY h:mm A');
                                            }
                                        },
                                    ],
                                    footerCallback: function (row, data, start, end, display) {
                                        var api = this.api(), data;

                                        // Remove the formatting to get integer data for summation
                                        var intVal = function (i) {
                                            return typeof i === 'string' ?
                                                i.replace(/[\$,]/g, '') * 1 :
                                                typeof i === 'number' ?
                                                i : 0;
                                        };

                                        // Total over all pages
                                        total = api
                                            .column(5)
                                            .data()
                                            .reduce(function (a, b) {
                                                return intVal(a) + intVal(b);
                                            }, 0);

                                        // Total over this page
                                        pageTotal = api
                                            .column(5, { page: 'current' })
                                            .data()
                                            .reduce(function (a, b) {
                                                return intVal(a) + intVal(b);
                                            }, 0);

                                        // Update footer
                                        $(api.column(5).footer()).html(
                                            pageTotal + ' ( ' + total + ' total)'
                                        );
                                    }
                                });
                                // Function to hide the alerts after 5 seconds
                                setTimeout(function(){
                                    $('#success-alert').fadeOut();
                                    $('#error-alert').fadeOut();
                                }, 5000); // 5 seconds (5000 milliseconds)
                            });
                        </script>
                        

                        

                    </div>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
@endsection
