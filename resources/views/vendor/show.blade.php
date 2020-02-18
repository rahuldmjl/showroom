@extends('layout.mainlayout')

@section('title', 'Vendors')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('vendor.show',[$vendors->id]) }}
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
                      <h5>{{'Vendor Management'}}</h5>
                  </div>
                  <!-- /.widget-heading -->
                  <div class="widget-body clearfix">

                      @if ($message = Session::get('success'))
                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <i class="material-icons list-icon">check_circle</i>
                        <strong>Success</strong>: {{ $message }}
                      </div>
                      @endif
                       {!! Form::open(array('route' => 'vendor.store','method'=>'POST')) !!}
                       <table class="vendorlist table table-striped table-responsive" >
                          <thead>
                              <tr>
                                  <th>No</th>
                                  <th>Vendor Name</th>
                                  <th>Vendor Email</th>
                                  <th>Vendor DMCode</th>
                              </tr>
                          </thead>
                          <tbody>
                            
                            <tr>
                                
                                  <td>
                                      {{$vendors->id}}
                                    </td>
                        <td >
                          {{$vendors->name}}
                        </td>
                        <td >
                          {{$vendors->email}}
                        </td>
                        <td >
                          {{$vendors->vendor_dmcode}}
                        </td>
                        
                      </tr>
                          </tbody>
                          <tfoot>
                              <tr>
                                  <th>No</th>
                                  <th>Vendor Name</th>
                                  <th>Vendor Email</th>
                                  <th>Vendor DMCode</th>
                                
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

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>

@endsection
