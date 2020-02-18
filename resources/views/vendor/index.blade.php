@extends('layout.mainlayout')

@section('title', 'Vendors')

@section('distinct_head')

<link href="<?= URL::to('/'); ?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('vendor.index') }}
        <!-- /.page-title-right -->
    </div>
    <!-- /.page-title -->
    <!-- =================================== -->
    <!-- Different data widgets ============ -->
    <!-- =================================== -->
    <div class="widget-list">
        <div class="row">
            <div class="col-md-12 widget-holder">
                <div class="widget-bg">
                    <div class="widget-heading clearfix">
                        <h5 class="border-b-light-1 pb-1 mt-0 mb-2 w-100">{{'Vendor Management'}}</h5>
                    </div>
                    <!-- /.widget-heading -->
                    <div class="widget-body clearfix dataTable-length-top-0">

                        @if ($message = Session::get('success'))
                        <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                            <i class="material-icons list-icon">check_circle</i>
                            <strong>Success</strong>: {{ $message }}
                        </div>
                        @endif
                        {!! Form::open(array('route' => 'vendor.store','method'=>'POST')) !!}
                        <table class="vendorlist table table-center table-striped" id="vendorTable">
                            <thead>
                                <tr class="bg-primary">
                                    <th>No</th>
                                    <th>Vendor Name</th>
                                    <th>Vendor Email</th>
                                    <th>DM Code</th>
                                    <th>Action</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 0; ?>
                                @foreach($vendor as $vendors)
                                <tr>

                                    <td>
                                        {{++$i}}
                                    </td>
                                    <td>
                                        {{$vendors->name}}
                                    </td>
                                    <td>
                                        {{$vendors->email}}
                                    </td>
                                    <td>
                                        {{$vendors->vendor_dmcode}}
                                    </td>
                                    <td>
                                        <a title="View" href="{{ route('vendor.view',['vendor_id'=>$vendors->id]) }}" class="color-content table-action-style"><i class="list-icon material-icons md-18">remove_red_eye</i></a>
                                        <a title="Manage Rates" href="{{route('managecharges.index',['vendor_id'=>$vendors->id,'name'=>$vendors->name])}}" class="color-content table-action-style"><i class="list-icon material-icons md-18">perm_data_setting</i></a>
                                        <a title="Metal Rates" href="{{route('metalrates.index',['vendor_id'=>$vendors->id,'name'=>$vendors->name])}}" id="{{$vendors->id}}" class="color-content table-action-style"><i class="list-icon material-icons md-18">build</i></a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Vendor Name</th>
                                    <th>Vendor Email</th>
                                    <th>Vendor DMCode</th>

                                    <th>Action</th>

                                </tr>
                            </tfoot>
                        </table>

                    </div>
                    <!-- /.widget-body -->
                </div>
                <!-- /.widget-bg -->
            </div>
            <!-- /.widget-holder -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->

<div class="row" style="display: none;">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Users Management</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-success" href="{{ route('users.create') }}"> Create New User</a>
        </div>
    </div>
</div>

@endsection

@section('distinct_footer_script')

<script src="<?= URL::to('/'); ?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.print.min.js"></script>
<script type="text/javascript">
    var vendorlist = $('#vendorTable').DataTable({

        "language": {
            "infoEmpty": "No matched records found",
            "zeroRecords": "No matched records found",
            "emptyTable": "No data available in table",
            //"sProcessing": "<div id='loader'></div>"
        },
        "deferLoading": <?= $totalcount ?>,
        "processing": true,
        "serverSide": true,
        "serverMethod": "post",
        "columnDefs": [{
            "orderable": false,
            "targets": [0, 4]
        }],
        "ajax": {
            "url": "{{action('VendorController@vendoresponse')}}",
            "data": function(data, callback) {
                data._token = "{{ csrf_token() }}";
            }
        }
    });
    $('.vendorlist').wrap('<div class="vendorlist-main"></div>');
</script>
@endsection