@extends('layout.mainlayout')

@section('title', 'Create Raw Diamond')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/css/autocatch.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('diamondraw.create') }}
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
                        <h5 class="box-title box-title-style mr-b-0">Add Raw
                        Diamond</h5>
                        <p class="text-muted">You can add raw diamond by filling this form</p>

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
                        {!! Form::open(array('route' => 'diamondraw.store','method'=>'POST', 'files'=>'true','id'=>'diamondrawform')) !!}
                        <div class="row">
                             {{ Form::hidden('created_by',Auth::user()->id, array('class' => 'form-group')) }}
                             <div class="col-lg-6 col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="l30">Packet Name</label>
                                    {!! Form::text('packet_name',$new, array('placeholder' => 'Packet Name','class' => 'required form-control','readonly'=>'true')) !!}
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <div class="vendorhid">
                                    <div class="form-group">
                                        <label for="l30">Vendor Name</label>
                                        {!! Form::text('vendor_name' ,null, array('autocomplete' => 'off', 'class' => 'form-control','id'=>'search_text')) !!}
                                        <input type="hidden" name="customSuggestionsJson" id="customSuggestionsJson" />
                                        {{ Form::hidden('vendorId', '', array('id' => 'venID')) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12" style="display:none;">
                                <div class="form-group">
                                    <label for="l30">Transaction Type</label>
                                    <select class="form-control transactionsele" name="transaction_type">
                                    <option value="1" selected>Purchase</option>
                                </select>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="l30">Total Diamond Weight</label>
                                    {!! Form::number('total_weight', null, array('placeholder' => 'Total Diamond Weight','class' => 'form-control', 'step' => '0.001','min'=>'0.000')) !!}
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="l30">Purchased Date</label>
                                    <br>
                                    {!! Form::text('purchased_at', null, array('class' => 'form-control datepicker','placeholder' => 'Purchased Date','autocomplete' => 'off', 'data-plugin-options'=>'{"autoclose": true, "maxDate": "today", "endDate": "today", "format": "yyyy-mm-dd"}')) !!}
                                </div>
                            </div>
                        </div>
                        <div class="form-actions btn-list">
                            <button class="btn btn-primary" type="submit">Save</button>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.js"></script>
<script src="<?=URL::to('/');?>/js/autocatch.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>

<script>
   $(document).ready(function() {
        jQuery.validator.addMethod("alphanumeric", function(value, element) {
        return this.optional(element) || /^[\w.]+$/i.test(value);
        }, "Letters, numbers, and underscores only please");

        $("#diamondrawform").validate({

           ignore: ":hidden",
            rules: {
                packet_name: {
                    required:true,
                    alphanumeric:true
                },
                total_weight: {
                    required: true,
                    min: 0,
                },
                total_amount: {
                    required: true,
                    min: 0,
                },
                vendor_name: {
                    required: true,
                },
                purchased_at: {
                    required: true,
                }
            }
        });

        src = "{{ route('searchajax') }}";
        $.ajax({
            url: src,
            dataType: "json",
            data: {
                term : '', //$(this).val()
            },
            success: function(data) {
                //console.log(data);
                var myJSON = JSON.stringify(data);
                $('#customSuggestionsJson').val(myJSON);
                //response(data);
            }
        });

        jQuery('#search_text').autocatch({
            //'currentSelector': '#search_text',
            'jsonData': '#customSuggestionsJson',
            'suggestionRenderer': '#customSuggestions',
            'idElem': '#venID',
            'txtElem': '#vendorName',
        });

        $(document).on('keydown', '.autocomplete_shape_txt', function() {
            id = this.id;
            srcshpe = "{{ route('searchajaxshape') }}";
            $('#'+id).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: srcshpe,
                        dataType: "json",
                        data: {
                            term : request.term
                        },
                        success: function(data) {
                        response(data);
                        }
                    });
                },
                select: function (event, ui) {//trigger when you click on the autocomplete item
                    $("#shapeID").val(ui.item.label);
                },
                minLength: 3,
            });
        });


        $(document).on('keydown', '.autocomplete_quality_txt', function() {
            id = this.id;
            srcshpe = "{{ route('searchajaxquality') }}";
            $('#'+id).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: srcshpe,
                        dataType: "json",
                        data: {
                            term : request.term
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                select: function (event, ui) {//trigger when you click on the autocomplete item
                    $("#qualityID").val(ui.item.label);
                },
                minLength: 3,
            });
        });

        /* For Autocomplete Code - end */

        $(document).on('click', '.btn_remove', function(){
            var button_id = $(this).attr("id");
            $('#row'+button_id+'').remove();
        });
    });
</script>
@endsection