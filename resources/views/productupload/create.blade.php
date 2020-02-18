@extends('layout.mainlayout')
@section('title', 'Product Upload')

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')
@section('content')

    <main class="main-wrapper clearfix">
        <div class="row page-title clearfix">
            {{ Breadcrumbs::render('productupload.create') }}
        </div>
        @if ($message = Session::get('success'))
            <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <i class="material-icons list-icon">check_circle</i>
             <strong>Success</strong>: {{ $message }}
            </div>
          @endif
        
         @if (Session::has('failed'))
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                {!!Session::get('failed')!!}.<br><br>
            </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            @foreach ($errors->all() as $error)
            <i class="material-icons list-icon">check_circle</i>
             <strong>Error</strong>: {{ $error }}
             <br/>
            @endforeach
        </div>
        @endif

        @if ($message = Session::get('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
          <i class="material-icons list-icon">check_circle</i>
          <strong>Error</strong>: {{ $message }} 
          </div>
        @endif
                        
        <div class="widget-list">
                <div class="row">
                    <div class="col-md-12 widget-holder">
                        <div class="widget-bg">
                            <div class="widget-body clearfix">
                                <h5 class="box-title box-title-style mr-b-0">Product Upload</h5>
                                <p class="text-muted"></p>
                                <form action="{{action('ProductuploadController@store')}}" method="post" enctype="multipart/form-data">

                                    {{ csrf_field() }}
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden"  name="gold_handling" id="gold_hadling_values" value="">
                                    <input type="hidden"  name="diamond_handling" id="diamond_handling_values" value="">
                                    <input type="hidden"  name="fancy_diamond_handling" id="fancy_diamond_handling_values" value="">
                                    <input type="hidden" name="igi_charges" id="igi_charges_values" value="">
                                    <input type="hidden" name="hallmarking" id="hallmarking_charges_values" value="">
                                    <input type="hidden" name="diamond_quality" id="diamond_quality_values" value="">
                                    <input type="hidden" name="stone_carat" id="stone_carat_values" value="">
                                    <input type="hidden" name="stone_shape" id="stone_shape_values" value="">
                                    <input type="hidden" name="diamond_gold_price" id="diamond_gold_price_values" value="">

                                    <div class="form-group row costing_excelfile">
                                        <label class="col-md-6 col-form-label" for="costingexcel_info">File input</label>
                                        <div class="col-md-9">
                                            <input id="costingexcel_info" type="file" id='costingexcel_info' name="name" accept=".xls,.xlsx" required>

                                            <br><small class="text-muted">Technical information for user</small>
                                            <a href="<?=URL::to('/') . '/uploads/productupload.xlsx'?>">Example Sheet</a>
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <div class="form-group row">
                                            <div class="col-md-9 btn-list">
                                                <button class="btn btn-primary btn-rounded" id="costing_submitbtn" type="submit">Submit</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
             <div class="modal fade bs-modal-lg costing_popup" tabindex="-1" id="costing_popup" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">

                    </div>
                </div>
            </div>
    </main>

@endsection
@section('distinct_footer_script')

<script src="<?php echo url('/').'/cdnjs.cloudflare.com/ajax/libs/jquery/jquery.validate.js' ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.12.0/additional-methods.min.js"></script>


<script type="text/javascript">
$(document).ready(function(){
    $("#jobwork_status").change(function()
    {
        var id = $('#jobwork_status').find(":selected").val();
        var vendor_id = $('#vendor_id').find(":selected").val();

        if(id != '') {
            if(vendor_id != '') {
                jQuery.ajax({
                    type: "GET",
                    dataType: "json",
                    url: "<?=URL::to('/') . '/costing/getmsg'?>",
                    data: {
                    "_token": '{{ csrf_token() }}',
                    "id": id,
                    "vendor_id":vendor_id
                    },
                    success: function(data) {
                        $('.modal-content').html(data.html);
                        $('#costing_popup').modal('show');
                        if(id == "1") {
                            jQuery.ajax({
                                type: "GET",
                                dataType: "json",
                                url: "<?=URL::to('/') . '/costing/getHandligCharges'?>",
                                data: {
                                "_token": '{{ csrf_token() }}',
                                "id": id,
                                "vendor_id":vendor_id
                                },
                                success: function(data) {
                                    jQuery("#gold_handling").val(data['vendor_handling_arr']['gold_handling']);
                                    jQuery("#diamond_handling").val(data['vendor_handling_arr']['diamond_handling']);
                                    jQuery("#fancy_diamond_handling").val(data['vendor_handling_arr']['fancy_diamond_handling']);
                                    jQuery("#igi_charges").val(data['vendor_handling_arr']['igi_charges']);
                                    jQuery("#hallmarking").val(data['vendor_handling_arr']['hallmarking']);
                                }
                            });
                        }
                    }
                });
            }
            else {
                swal("Please select vendor");
                $('#jobwork_status').val('');
            }
        }

    });
});
</script>

@endsection