@extends('layout.mainlayout')

@section('title', 'Add Payment Header')
@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
@endsection
@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('paymenttype.create') }}
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
                    <div class="widget-body clearfix">
                        <h5 class="box-title box-title-style mr-b-0">Create Payment Header </h5>
                        <p class="text-muted">You can add Payment Header by filling this form</p>
                         @if (count($errors) > 0)
                          <div class="alert alert-danger">
                            <strong>Whoops!</strong> There were some problems with your input.<br><br>
                            <ul>
                               @foreach ($errors->all() as $error)
                                 <li>{{ $error }}</li>
                               @endforeach
                            </ul>
                          </div>
                        @endif
                        {!! Form::open(array('route' => 'paymenttype.store','method'=>'POST','id'=>'payment_type_form')) !!}
                         <div class="row">
                                <div class="col-md-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="l30">Payment Header</label>
                                        <!-- <input class="form-control" id="l30" placeholder="Email Address" type="text"> -->
                                        {!! Form::text('name', null, array('placeholder' => 'Payment Header Name','class' => 'form-control')) !!}
                                    </div>
                                </div>
                                <div class="col-md-12 col-lg-6">
                                  <div class="form-group">
                                    <label for="l30">Parent</label>
                                      <select name="parent_id" class="form-control">
                                    <option value="0">Select Parent Header</option>
                                      @foreach ($paymenttype as $key => $value)
                                      @if($value->parent_id==0)
                                      <option value="{{ $value->id }}">{{ $value->name}}</option>
                                      @endif
                                      @endforeach
                                      </select>
                                  </div>
                                </div>
                                 <div class="col-12 form-actions btn-list">
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                    <button class="btn btn-outline-default" onclick="goBack()" type="reset">Cancel</button>
                                 </div>
                        {!! Form::close() !!}
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
@endsection
@section('distinct_footer_script')

<script type="text/javascript">
   $.ajaxSetup({

        headers: {

            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        }

    });
    $(document).ready( function() {
       $("#payment_type_form").validate({
        ignore: ":hidden",
        rules: {
            name: {
                required:true,
                
            },
            parent_id: {
                required: true
            },
          },
          
      });
});

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
@endsection