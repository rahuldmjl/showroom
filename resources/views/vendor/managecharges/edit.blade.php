@extends('layout.mainlayout')

@section('title', 'Edit Managecharges')
@section('distinct_head')
 <?php $metal = Config::get('constants.enum.metal_type');?>
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection
@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
     <div class="row page-title clearfix">
        {{ Breadcrumbs::render('managecharges.edit',[$vendor_charges->id]) }}
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
                        <h5 class="box-title box-title-style mr-b-0">Edit charges</h5>
                        <p class="text-muted">You can modify charges details here in this form</p>

                        @if (count($errors) > 0)
                          <div class="alert alert-danger">
                            <strong>Whoops!</strong> There were some problems with your input.<br><br>
                            <ul>
                               @foreach ($errors as $error)
                                 <li>{{ $error }}</li>
                               @endforeach
                            </ul>
                          </div>
                        @endif

                        {!! Form::model($vendor_charges, ['method' => 'PATCH','route' => ['managecharges.update', $vendor_charges->id,'vendor_id'=>$vendor_id,'name'=>$name],'id'=>'edit_managecharges_form'] ) !!}

                                <input type="hidden" class="form-control" id="l30" name="vendor_id" value="{{$vendor_id}}">
                                <input type="hidden" name="name" class="form-control" value="{{$name}}">
                                <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label for="l30">From (MM) *</label>
                                {!! Form::number('from_mm', null, array('class' => 'form-control','id'=>'from_mm')) !!}
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label for="l30">To (MM) *</label>
                                {!! Form::number('to_mm', null, array('class' => 'form-control')) !!}
                                    </div>
                                </div>
                                 <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <strong>Type:</strong>

                                {!! Form::select('type',$metal, null,array('class' => 'form-control ')) !!}
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label for="l30" class="mb-0">Labour Charge</label>
                                {!! Form::text('labour_charge', null, array('class' => 'form-control','placeholder' => 'Labour Charge')) !!}
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <strong>Product Type:</strong>
                            <select name="product_type" class="form-control">

                                @foreach ($product as $key => $value)
                                    <option value="{{ $value->vendor_product_id }}">{{ $value->name}}</option>
                                @endforeach
                            </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <strong>Diamond Type:</strong>

                            <select name="diamond_type" class="form-control">

                                @foreach ($diamond as $key => $value)
                                    <option value="{{ $value->vendor_diamond_id }}">{{ $value->name}}</option>
                                @endforeach
                            </select>
                            </div>
                        </div>
                            <div class="form-actions btn-list col-12">
                                <button class="btn btn-primary" type="submit">Submit</button>
                                <button class="btn btn-outline-default" onclick="goBack()" type="reset">Cancel</button>
                            </div>
                    </div>
                    <!-- /.widget-body -->
                    {!! Form::close() !!}
                </div>
                <!-- /.widget-bg -->
            </div>
            <!-- /.widget-holder -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.widget-list -->
</main>
<!-- /.main-wrapper -->

@endsection
@section('distinct_footer_script')

<script type="text/javascript">
   $.ajaxSetup({

        headers: {

            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        }

    });
    $(document).ready( function() {
       $("#edit_managecharges_form").validate({
       ignore: ":hidden",
        rules: {
            from_mm: {
                required:true,
                min:0,
                max:51

            },
            to_mm: {
                required: true,
                min:0,
                max:51,
                greaterThan: "#from_mm"

            },
            type: {
                required: true
            },
            labour_charge: {
                required: true
            },
            product_type: {
                required: true
            },
            diamond_type: {
                required: true
            }
          },
          messages :{
            to_mm:{
                required:"This Field Is required.",
                min:"Please enter a value greater than or equal to 0.",
                max:"Please enter a value less than or equal to 51.",
                greaterThan:"Please enter a greater then From (MM)*"

            }
          }


      });
});

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
@endsection