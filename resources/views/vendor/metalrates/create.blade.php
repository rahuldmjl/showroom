 @extends('layout.mainlayout')

 @section('title', 'Metal Rates')

 @section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

 @section('content')


 <main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
    {{ Breadcrumbs::render('metalrates.create') }}
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
            <h5 class="box-title box-title-style mr-b-0">Create Metal Rates</h5>
            <p class="text-muted">You can add metal rates by filling this form</p>
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
           {!! Form::open(array('route' => 'metalrates.store','method'=>'POST' ,$vendor_id,$name,'id'=>'metalrates_form')) !!}
           <div class="row">
            <div class="col-12 col-xl-6">
             <div class="form-group">
              {!! Form::hidden('vendor_id',$vendor_id, null, array('placeholder' => 'vendor_id','class' => 'form-control cid' ,'accept-charset'=>"UTF-8")) !!}
              <strong>Type:</strong>
              <select name="metal_type" class="form-control" id="metal_type">
               @foreach($metaltype as $metal)
               <option value="{{$metal->grp_metal_type_id}}">{{$metal->metal_type}}</option>
               @endforeach
              </select>
            </div>
            </div>
            <div class="col-12 col-xl-6">
           <div class="form-group metalquality">
             <strong>Quality:</strong>
             <select name="metal_quality" class="form-control Quality">
                
               <option class="quality" value="<?php echo Config::get('constants.Metal_Quality.14K Yellow Gold'); ?> "><?php echo Config::get('constants.Metal_Quality.14K Yellow Gold'); ?> </option>
               <option class="quality" value="<?php echo Config::get('constants.Metal_Quality.14K White Gold'); ?>"><?php echo Config::get('constants.Metal_Quality.14K White Gold'); ?> </option>
               <option class="quality" value="<?php echo Config::get('constants.Metal_Quality.14K Rose Gold'); ?>"><?php echo Config::get('constants.Metal_Quality.14K Rose Gold'); ?> </option>
               <option class="quality" value="<?php echo Config::get('constants.Metal_Quality.18K Yellow Gold'); ?>"><?php echo Config::get('constants.Metal_Quality.18K Yellow Gold'); ?> </option>
               <option class="quality" value="<?php echo Config::get('constants.Metal_Quality.18K White Gold'); ?>"><?php echo Config::get('constants.Metal_Quality.18K White Gold'); ?> </option>
               <option class="quality" value="<?php echo Config::get('constants.Metal_Quality.18K Rose Gold'); ?>"><?php echo Config::get('constants.Metal_Quality.18K Rose Gold'); ?> </option>
               <option class="quality" value="<?php echo Config::get('constants.Metal_Quality.14K Two Tone'); ?>"><?php echo Config::get('constants.Metal_Quality.14K Two Tone'); ?> </option>
               <option class="quality" value="<?php echo Config::get('constants.Metal_Quality.18K Two Tone'); ?>"><?php echo Config::get('constants.Metal_Quality.18K Two Tone'); ?> </option>
               <option class="metal-quality" value="<?php echo Config::get('constants.Metal_Quality.Platinum(950)'); ?>"><?php echo Config::get('constants.Metal_Quality.Platinum(950)'); ?> </option>
               <option class="quality" value="<?php echo Config::get('constants.Metal_Quality.14K Three Tone'); ?>"><?php echo Config::get('constants.Metal_Quality.14K Three Tone'); ?> </option>
               <option class="quality" value="<?php echo Config::get('constants.Metal_Quality.18K Three Tone'); ?>"><?php echo Config::get('constants.Metal_Quality.18K Three Tone'); ?> </option>
             </select>
           </div>
            </div>
            <div class="col-12 col-xl-6">
           <div class="form-group">
            <label for="l30">Gold Rate(%)</label>
            {!! Form::number('gold_rate', null, array('class' => 'form-control','placeholder' => 'Gold Rate','id'=>'gold_rate')) !!}
           </div>
            </div>
            <div class="col-12 col-xl-6">
             <div class="form-group">
              <label for="l30">Metal Price</label>
              {!! Form::number('rate', null, array('class' => 'form-control','placeholder' => 'Metal Price','step'=>'0.01','id'=>'rate')) !!}
            </div>
            </div>
           </div>
    <div class="form-actions btn-list">
      <button class="btn btn-primary" type="submit" >Submit</button>
      <button class="btn btn-outline-default" onclick="goBack()" type="reset">Cancel</button>
    </div>
    {!! Form::close() !!}
  </div>
  </div>
</div>
</div>
</div>
</main>



@endsection
@section('distinct_footer_script')

<script type="text/javascript">
 $.ajaxSetup({

  headers: {

    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

  }

});

 $(function () {
    MetalRateFunction(); //this calls it on load
    $(".Quality").change(MetalRateFunction);
  });

 function MetalRateFunction() {
  var Quality = $('.Quality').val();

  $.ajax({
    method: 'POST',
    url: "{{ action('MetalratesController@getmetaldata') }}" ,
    data :{
      "_token": "{{ csrf_token() }}",
      "id": Quality
    },
    success: function(data) {

      $("#gold_rate").val(data.result[0].gold_rate);
      $("#rate").val(data.result[0].rate);

    }
  });
}

$(document).ready( function() {
  
 
 jQuery('select[name="metal_type"]').on('change',function(){
  $(".metalquality").show();
    var metal_type = jQuery(this).val();
    if (metal_type == "1391") {
          $('.metal-quality').attr('selected', true);
          $('.quality').attr('selected', false);
          $(".metal-quality").show();
          $(".quality").hide();
      }else if(metal_type == "3"){
        $('.quality').attr('selected', true);
        $('.metal-quality').attr('selected', false);
        $(".quality").show();
        $(".metal-quality").hide();
      }else{
          $(".metalquality").hide();
      }
   
              
});
$('#metalrates_form').on('load', function(){
    $(".metal_type").show();
    $(".quality").show();
});
 
 $("#metalrates_form").validate({
   ignore: ":hidden",
   rules: {
    metal_type: {
      required:true,

    },
    metal_quality: {
      required: true
    },
    gold_rate: {
      required: true,
      min: 1,
      max:100

    },
    rate: {
      required: true,
      maxlength: [13]
    }
  },
  messages: {
    gold_rate: {
      required:"This field is required.",
      min:"The gold rate must be at least 1.",
      max:"The gold rate may not be greater than 100"
    },
    rate: {
     required:"This field is required.",
     maxlength:"Please enter a value maximum legth of (12,2)."
   }
 }

});
});

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
@endsection