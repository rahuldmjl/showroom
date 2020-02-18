<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
$countryList = isset($countryList) ? $countryList : array();
$shapeArr = config('constants.enum.diamond_shape');
?>
@extends('layout.mainlayout')

@section('title', 'Quotation')

@section('distinct_head')
<!-- <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"> -->
<link rel="stylesheet" href="<?=URL::to('/');?>/css/autocomplete.css"/>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
	  {{ Breadcrumbs::render('inventory.generatequotation') }}
      <!-- /.page-title-right -->
  </div>
  <!-- /.page-title -->
  <!-- =================================== -->
  <!-- Different data widgets ============ -->
  <!-- =================================== -->
  	<div class="widget-list">
  		<div class="col-md-12 widget-holder loader-area" style="display: none;">
		    <div class="widget-bg text-center">
		      <div class="loader"></div>
		    </div>
		</div>
      	<div class="row">
  			<div class="col-md-12 widget-holder content-area">
  				<div class="widget-bg">
  					{!! Form::open(array('route' => 'inventory.store','method'=>'POST','id'=>'quotation-form','class'=>'form-horizontal','autocomplete'=>'nope')) !!}
  					{{ Form::hidden('product_ids', $productIds, array('id' => 'product_ids')) }}
  					{!! Form::token() !!}
  					<div class="row">
  						<div class="form-group col d-flex">
							<label class="col-form-label mr-2">Customer Type: </label>
							<div class="radiobox radio-info">
					            <label>
					                 {{ Form::radio('customerType', 'new' ,false) }}
					                 <span class="label-text">New</span>
					            </label>
					            <label>
					                {{ Form::radio('customerType', 'existing' ,false) }} <span class="label-text">Existing</span>
					            </label>
					        </div>
						</div>
  					</div>
					<section id="customer-form-container" class="hidden w-100">
					    	<div class="row align-items-center mr-b-10">
					            <div class="col-md-4 mb-3 new-customer-field hidden">
					                <label for="txtfirstname">First Name <span class="text-danger">*</span></label>
					                {!! Form::text('txtfirstname', null, array('class' => 'form-control required','id'=>'txtfirstname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					            </div>
					            <div class="col-md-4 mb-3 new-customer-field hidden">
					                <label for="txtlastname">Last Name <span class="text-danger">*</span></label>
					                {!! Form::text('txtlastname', null, array('class' => 'form-control','id'=>'txtlastname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					            </div>
					            <div class="col-md-4 mb-3 new-customer-field hidden">
					                <label for="txtcontactnumber">Contact Number <span class="text-danger">*</span></label>
					                {!! Form::text('txtcontactnumber', null, array('class' => 'form-control','id'=>'txtcontactnumber','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					            </div>
					            <div class="col-md-4 mb-3 new-customer-field hidden">
					                <label for="txtaddress">Address <span class="text-danger">*</span></label>
					                {!! Form::text('txtaddress', null, array('class' => 'form-control','id'=>'txtaddress','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					            </div>
					            <div class="col-md-4 mb-3 new-customer-field hidden">
					            	<label for="selectcountry">Country <span class="text-danger">*</span></label>
					                <select class="form-control height-35" name="selectcountry" id="selectcountry">
					                	<option value="">Select</option>
					                    <?php foreach($countryList as $country):?>
					                        <option value="<?php echo $country['country_id'];?>"><?php echo $country['name']?></option>
					                    <?php endforeach;?>
					                </select>
					            </div>
					            <div class="col-md-4 mb-3 new-customer-field hidden customer-state">
					                <label for="txtstateprovince">State/Province <span class="text-danger">*</span></label>
					                {!! Form::text('txtstateprovince', null, array('class' => 'form-control','id'=>'txtstateprovince','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					            </div>
					            <div class="col-md-4 mb-3 new-customer-field hidden">
					                <label for="txtcity">City <span class="text-danger">*</span></label>
					                {!! Form::text('txtcity', null, array('class' => 'form-control','id'=>'txtcity')) !!}
					            </div>
					            <div class="col-md-4 mb-3 new-customer-field hidden">
					                <label for="txtzipcode">Zip Code <span class="text-danger">*</span></label>
					                {!! Form::text('txtzipcode', null, array('class' => 'form-control','id'=>'txtzipcode','maxlength'=>'6','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					            </div>
					            <div class="col-md-4 mb-3 new-customer-field hidden">
					                <label for="txtemail">Email <span class="text-danger">*</span></label>
					                {!! Form::email('txtemail', null, array('class' => 'form-control','id'=>'txtemail','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					            </div>
					            <div class="col-md-4 mb-3 email-field hidden">
					                <label for="txtdmusercodeemail">DMUSERCODE or Email <span class="text-danger">*</span></label>
					                {!! Form::text('txtdmusercodeemail', null, array('class' => 'form-control','id'=>'txtdmusercodeemail','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					                {!! Form::hidden('customer_id', null, array('class' => 'form-control','id'=>'customer_id')) !!}
					            </div>
					        	<div class="col-md-4 mt-4 pt-1 align-self-start">
					        		<button type="button" id="btn-verify-customer" class="btn btn-primary ripple small-btn-style text-left">Next</button> 
					            </div>
					        </div>
  						</section>
  						<section id="diamond-calculation-container" class="hidden w-100">
  							<div class="row">
  								<div class="col-auto">
	  								<div class="checkbox checkbox-primary">
	                                    <label class="">
	                                        <input type="checkbox" id="chkDefaultQuotation" name="chkDefaultQuotation"> <span class="label-text">Set default quotation with DML price</span>
	                                    </label>
	                                </div>
	                            </div>
	                            <div class="col-auto text-center">
	                            	<label class="col-form-label">Total Products: <?= $totalProducts?></label>
	                            </div>
	                            <div class="col-7">
	                            	<div class="input-group">
			                            <div class="input-group-btn width-90">
			                                <div class="fileUpload btn w-100 btn-default height-35 lineheight-34 py-0">
			                                  	<span><i class="glyphicon glyphicon-upload"></i> Upload</span>
			                                  	<input id="uploadBtn" type="file" class="upload width-90" name="certificatecsv"/>
			                                </div>
			                            </div>
			                            <input id="certificate_file" name="rate_csv" class="form-control border bg-transparent" placeholder="Choose File" disabled="disabled">
			                            <button class="btn btn-primary small-btn-style mx-2" id="btn-bulk-upload" type="button">Submit</button>
			                            <a href="<?=URL::to('/');?>/uploads/import_rate.csv" class="btn btn-primary small-btn-style">Download Sample CSV<i class="material-icons ml-1 fs-18">file_download</i></a>
			                        </div>
	                            </div>
  							</div>
  							<div class="row mt-4">
  								<div class="tabs w-100">
		  							<ul class="nav nav-tabs">
		  								<?php $activeClass='active';foreach ($shapeArr as $key => $shape):?>
		  									<?php 
		  									$stringIndex = strcspn( $key , '0123456789' );
		  									list($start, $end) = preg_split('/(?<=.{'.$stringIndex.'})/', $key, 2);
		  									$endChar = !empty($end) ? '-'.$end : '';
		  									?>
		  									<?php if(isset($diamondShapeData[$key])):?>
		  									<li class="nav-item <?php echo $activeClass?>"><a class="nav-link" href="#<?php echo $key?>_shape" data-toggle="tab"><?php echo ucfirst($start).$endChar;?></a>
				                       		 </li>
				                       		<?php endif;?>
		  								<?php $activeClass='';endforeach;?>
				                    </ul>
				                    <div class="tab-content p-2 border border-top-0">
				                    	<?php $activeClass='active';
				                    	foreach ($shapeArr as $shapekey => $shape):?>
				                    		<div class="tab-pane <?php echo $activeClass;?>" id="<?php echo $shapekey;?>_shape">
												<?php if(isset($diamondShapeData[$shapekey]) && count($diamondShapeData[$shapekey]) > 0):?>
				                    				<?php foreach ($diamondShapeData[$shapekey] as $diamond){
				                    					$stoneQuality = '';
						                    			foreach ($diamond as $key => $value) {
						                    				$stoneQuality = isset($value['stone_quality']) ? $value['stone_quality'] : '';
						                    				?>
						                    				<div class="form-group">
						                    					<div class="col-12 px-0 stone-data-container">
						                    					<h6 class="w-100 shape-title"><?php echo isset($value['diamondShape']) ? ucfirst($value['diamondShape']) : ''?> (<?php echo isset($value['stone_quality']) ? $value['stone_quality'] : ''?>)</h6>
						                                        <div class="row m-0 py-3 px-2">
						                                        <!-- <label class="col-auto px-1 col-form-label"><?php //echo isset($value['stone_quality']) ? $value['stone_quality'] : ''?></label> -->
						                                        <?php
						                                        $rangeData = array(
						                                        	'stone_shape'=>isset($value['diamondShape']) ? $value['diamondShape'] : '',
						                                        	'stone_quality'=>isset($value['stone_quality']) ? $value['stone_quality'] : '',
						                                        	'stone_range_data'=> json_encode($stonerangedata)
						                                        	);
						                                        ?>
						                                        <input type="hidden" name="defaultstoneinfo[]" class="stoneRangeData" value='<?php echo json_encode($rangeData);?>'>
						                                        <?php foreach($stonerangedata as $index=>$stone_range_data):?>
						                                        	<?php foreach($stone_range_data as $stoneRange):?>
						                                        	<div class="w-15 col-md px-1">
						                                        		<label class="w-100 text-center" for="stone_range_<?= isset($stoneRange->stone_carat_from) ? $stoneRange->stone_carat_from : ''?>_<?= isset($stoneRange->stone_carat_to) ? $stoneRange->stone_carat_to : ''?>_<?= isset($value['stone_quality']) ? $value['stone_quality'] : ''?>"><?php echo isset($stoneRange->stone_carat_from) ? $stoneRange->stone_carat_from : ''?> - <?php echo isset($stoneRange->stone_carat_to) ? $stoneRange->stone_carat_to : ''?><?php if($rangeData['stone_shape'] == "round") { $rangeArr = json_decode($rangeData['stone_range_data']); $firstrangeval = $rangeArr->round[0]->stone_carat_from; if($firstrangeval != '0.001') { echo ' (MM)'; } } ?></label>
						                                        		<input type="hidden" name="stone_data[<?=$stoneQuality?>][<?= $value['diamondShape']?>][stone_range][]" value="<?php echo isset($stoneRange->stone_carat_from) ? $stoneRange->stone_carat_from : ''?> - <?php echo isset($stoneRange->stone_carat_to) ? $stoneRange->stone_carat_to : ''?>">
						                                        		<input type="number" min="1" class="form-control diamond-caret-val" name="stone_data[<?=$stoneQuality?>][<?= isset($value['diamondShape']) ? $value['diamondShape'] : ''?>][stone_price][]" id="stone_range_<?= isset($value['diamondShape']) ? $value['diamondShape'] : ''?>_<?= isset($stoneRange->stone_carat_from) ? $stoneRange->stone_carat_from : ''?>_<?= isset($stoneRange->stone_carat_to) ? $stoneRange->stone_carat_to : ''?>_<?= isset($value['stone_quality']) ? $value['stone_quality'] : ''?>" step="0.1"> 
							                                        </div>
								                                    <?php endforeach;?>
						                                        <?php endforeach;?>
							                                    </div>
							                                </div>
					                                    </div>
						                    			<?php
						                    			}
						                    		}
						                    		?>
						                    	<div class="form-group row p-3">
					  								<div class="w-50">
					                            		<label class="text-center" for="txtlabourcharge_<?= $shapekey?>">Metal Labour Charge: </label>
					                            		<input type="number" min="1" class="form-control labour-charge-val" id="txtlabourcharge_<?= $shapekey?>" name="txtlabourcharge[<?= $shapekey?>][]">
										            </div>
					  							</div>
					                    		<?php else:?>
						                    		<p>No products!</p>	
				                    			<?php endif;?>
				                    		</div>
				                    	<?php $activeClass='';endforeach;?>
				                    	
				                    </div>
		  						</div>
  							</div>
  							<div class="row">
  								<p class="diamond-data-error"></p>
  							</div>
  							<div class="form-group row">
  								<button class="btn btn-primary small-btn-style" type="submit" id="btn-submit-quotation">Submit</button>
  							</div>
  						</section>
  					{!! Form::close() !!}
  				</div>
  			</div>
  		</div>    
    </div>
  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->
<style>
.diamond-data-error
{
	color: #ff0000
}
.shape-title
{
	background-color: #f2f2f2;
	padding: 10px;
	margin: 0;
	color: #000;
	text-transform: uppercase;
}
.stone-data-container
{
  border: 1px solid #e6e5e5;
}
</style>
@endsection

@section('distinct_footer_script')
<script src="https://cdn.jsdelivr.net/jquery.validation/1.15.1/jquery.validate.min.js"></script>
<script>
$(document).ready(function(){
	$( "#txtdmusercodeemail" ).autocomplete({
          source: function( request, response ) {
           // Fetch data
           $.ajax({
            url:  "{{ route('searchcustomer') }}",
            type: 'POST',
            dataType: "json",
            data: {
             term: request.term,
             _token:"{{ csrf_token() }}"
            },
            
            success: function( data ) {
            	response($.map( data, function( item ) {
                    return {
                        label: item.label,
                        value: item.value
                    }
                }));
            }
           });
          },
          minLength:3,
          select: function (event, ui) {
               $("#customer_id").val(ui.item.value);
               $(this).val(ui.item.label);
               return false;
          }
     });
	$('#chkDefaultQuotation').change(function() {
        if($(this).is(":checked")) {
           	$(this).attr("checked", true);
           	//console.log($(".stoneRangeData").val());
           	var stoneInfoArr = [];
           	$(".stoneRangeData").each(function(index,value) {
           		stoneInfoArr.push(this.value);
           	});
           	
			$.ajax({
                type: 'post',
                url: '<?=URL::to('/inventory/getdefaultstoneprice');?>',
                data:{stone_data:stoneInfoArr,_token:"{{ csrf_token() }}"},
                beforeSend: function(){
                    showLoader();
                    $("#btn-verify-customer").prop('disabled',false);
                },
                success: function(response){
                	var res = JSON.parse(response);
                	$.each( res.stone_data, function( key, value ) {
                		$(".diamond-caret-val").each(function() {
	                		var id = this.id;
	                		if(id == key)
	                		{
	                			$(this).val(value);
	                		}
	                	});
					});
					$.each(res.labour_charge, function(index, item) {
					    $(".labour-charge-val").each(function() {
	                		var id = this.id;
	                		if(id == index)
	                		{
	                			$(this).val(item);
	                		}
	                	});
					});
                    hideLoader();
				}
			});           	
        }
        else
        {
        	//Get stored quotation price
            $.ajax({
                type: 'post',
                url: '<?=URL::to('/inventory/getcustomerquotation');?>',
                data:{customer_id:$("#customer_id").val(),_token:"{{ csrf_token() }}"},
                beforeSend: function(){
                    showLoader();
                },
                success: function(response){
                	hideLoader();
                	var response = JSON.parse(response);
                	$.each(response.stone_data, function(index, item) {
					    $(".diamond-caret-val").each(function() {
	                		var id = this.id;
	                		if(id == index)
	                		{
	                			$(this).val(item);
	                		}
	                	});
					});
					$.each(response.labour_charge, function(index, item) {
					    $(".labour-charge-val").each(function() {
	                		var id = this.id;
	                		if(id == index)
	                		{
	                			$(this).val(item);
	                		}
	                	});
					});
                }
            });
        	$(this).removeAttr("checked");
        }
    });
	$("#btn-submit-quotation").click(function(event){
		event.preventDefault();
		var validationForm = true;
		$(".diamond-caret-val").each(function() {
			var id = $(this).attr("id");
		  	if(this.value == '')
		  	{
		  		$(this).next().remove();
		  		$(this).after('<label id="'+id+'-error" class="error stone-error" for="'+id+'">This value is required</label>');
		  		$(".diamond-data-error").html("Please fill required field");
		  		validationForm = false;
		  	}
		  	else
		  	{
		  		$(this).next().remove();
		  		$(".diamond-data-error").html("");
		  	}
		});
		$(".labour-charge-val").each(function() {
			var id = $(this).attr("id");
			var parentId = $(this).parent().parent().parent().attr('id');
			var rangeInputLength = $("#"+parentId).find('.diamond-caret-val').length;
			if(rangeInputLength > 0)
			{
				if(this.value == '')
			  	{
			  		$(this).next().remove();
			  		$(this).after('<label id="'+id+'-error" class="error labour-charge-error" for="'+id+'">Labour charge is required</label>');
			  		$(".diamond-data-error").html("Please fill required field");
			  		validationForm = false;
			  	}
			  	else
			  	{
			  		$(this).next().remove();
			  		$(".diamond-data-error").html("");
			  	}
		    }
		});
		if($("#diamond-calculation-container .error").length > 0)
		{
			validationForm = false;
			$(".diamond-data-error").html("Please fill required field");
		}
		else
		{
			validationForm = true;
			$(".diamond-data-error").html("");
		}
		if(validationForm)
		{
			$("#quotation-form").submit();
		}
	});
	$("#btn-bulk-upload").click(function(){
		var file_data = $('#uploadBtn').prop('files')[0];
	    var fileLength = $("#uploadBtn")[0].files.length;
	    var form_data = new FormData();
	    form_data.append('file', file_data);
	    form_data.append('_token',"{{ csrf_token() }}");
	    if(fileLength > 0)
	    {
	    	$.ajax({
	            type: 'POST',
	            contentType: false,
	            dataType: "text",
	            data: form_data,
	            processData: false,
	            url: '<?=URL::to('/inventory/bulkrateupload');?>',
	            beforeSend: function()
	            {
	              showLoader();
	            },
	            success: function(response){
	            	var res = JSON.parse(response);
	            	$.each( res, function( key, value ) {
                		$(".diamond-caret-val").each(function() {
	                		var id = this.id;
	                		if(id == key)
	                		{
	                			$(this).val(value);
	                		}
	                	});
					});
	            	hideLoader();
	            }
	        });
	    }
	    else
	    {
	    	swal({
              title: 'Oops!',
              text: "<?php echo Config::get('constants.message.quotation_bulk_rate_file_not_selected'); ?>",
              type: 'info',
              showCancelButton: true,
              showConfirmButton: false
            });
	    }
	});
	$("#btn-verify-customer").click(function(){
		var customerType = $('input[name=customerType]:checked').val();
		var dmcode_email = '';
		if(customerType == 'existing')
		{
			dmcode_email = $("#txtdmusercodeemail").val();
			
			if($("#customer_id").val()!='')
	        {
	        	$("#diamond-calculation-container").removeClass('hidden');
	            $('html, body').animate({
						        scrollTop: ($("#diamond-calculation-container").offset().top - 100)
						    }, 1500);
	        }
	        else
	        {
	        	$("#txtdmusercodeemail").addClass('error');
	        	$("#txtdmusercodeemail").after('<label id="txtdmusercodeemail-error" class="error" for="txtdmusercodeemail">DMUSERCODE/Email is required</label>');
	        	//$("#btn-verify-customer").prop('disabled',true);
	        }
		}
		else if(customerType == "new")
		{
			$("#quotation-form").validate({
		        rules: {
		            txtfirstname: "required",
		            txtlastname: "required",
		            txtcontactnumber:{
		            	required: true,
		            	number: true,
		            	maxlength: 13
		            },
		            txtaddress: "required",
		            selectcountry: "required",
		            txtstateprovince: "required",
		            txtcity: "required",
		            txtzipcode:{
		            	required: true,
		            	number: true,
		            	maxlength: 6,
		            	minlength: 6
		            },
		            txtemail: {
		            	required: true,
		            	email: true
		            }
		        },
		        messages: {
		            txtfirstname: "First name is required",
		            txtlastname: "Last name is required",
		            txtcontactnumber:{
		            	required: "Contact number is required",
		            	number: "Invalid contact number",
		            	maxlength: "Invalid contact number"
		            },
		            txtaddress: "Address is required",
		            selectcountry: "Country is required",
		            txtstateprovince: "State/Province is required",
		            txtcity: "City is required",
		            txtzipcode:{
		            	required: "Zip code is required",
		            	number: "Invalid zip code"
		            },
		            txtemail:{
		            	required: "Email is required",
		            	email: "Invalid email"
		            }
		        }
		    });
			if($("#quotation-form").valid())
			{
				$.ajax({
					type: "post",
					url: '<?=URL::to('/inventory/createcustomer');?>',
		            data:{form_data:$("#quotation-form").serialize(),_token:"{{ csrf_token() }}"},
		            beforeSend: function(){
		                showLoader();
		            },
		            success: function(response){
		                hideLoader();
		                var res = JSON.parse(response);
		                if(res.status)
		                {
							
		                	$("#diamond-calculation-container").removeClass('hidden');
		                	$("#customer_id").val(res.customer_id);
		                	$('html, body').animate({
						        scrollTop: ($("#diamond-calculation-container").offset().top - 100)
						    }, 1500);
		                }
		                else
		                {
		                	swal({
			                  title: 'Oops!',
			                  text: res.message,
			                  type: 'error',
			                  showCancelButton: true,
			                  showConfirmButton: false,
			                  confirmButtonClass: 'btn btn-danger',
			                  cancelButtonText: 'Ok'
			                });
			                $("#diamond-calculation-container").addClass('hidden');
		                }
		            }
				});
				
			}
			else
			{
				$("#diamond-calculation-container").addClass("hidden");
			}
		}
		
	});
	$("#selectcountry").change(function(){
		var countryId = this.value;
		$.ajax({
            type: 'post',
            url: '<?=URL::to('/inventory/getstatelist');?>',
            data:{country_id:countryId,_token:"{{ csrf_token() }}"},
            beforeSend: function(){
                showLoader();
            },
            success: function(response){
                hideLoader();
                var response = JSON.parse(response);
                //console.log(response);
                if(response.status!='success')
                {
                    swal({
	                  title: 'Oops!',
	                  text: response.message,
	                  type: 'error',
	                  showCancelButton: true,
	                  showConfirmButton: false,
	                  confirmButtonClass: 'btn btn-danger',
	                  cancelButtonText: 'Ok'
	                });
                }  
                else
                {
                	$("#txtstateprovince-error").remove();
                	if(response.data!='')
                	{
                		$("#txtstateprovince").remove();
                		var stateHtml = '<select class="form-control" id="txtstateprovince" name="txtstateprovince">';

                		$.each(response.data, function(index, item) {
						    console.log(item);
						    stateHtml+='<option value='+item.region_id+'>'+item.name+'</option>';
						});
						stateHtml+='</select>';
						$(".customer-state").append(stateHtml);
                	}
                	else
                	{
                		$("#txtstateprovince").remove();
                		$(".customer-state").append('<input type="text" class="form-control" id="txtstateprovince" name="txtstateprovince">');
                	}
                }
            },
            error: function(){
                hideLoader();
                $("#btn-verify-customer").prop('disabled',false);
            }
        });
	});
	$('input[name=customerType]').change(function(){
		$("#diamond-calculation-container").addClass("hidden");
		var customerType = this.value;
		$("#customer-form-container").removeClass("hidden");
		if(this.value == 'new')
	    {
	      $(".new-customer-field").removeClass("hidden");
	      $(".email-field").addClass('hidden');
	      if($("#operation_type").val()=='invoice')
	      {
	          $(".invoice-field").removeClass('hidden');
	          $(".invoiceqr-commission-field").removeClass('hidden');
	          $(".invoice-commission-field").removeClass('hidden');
	      }
	      else
	      {
	          $(".invoice-field").addClass('hidden');
	          $(".invoiceqr-commission-field").removeClass('hidden');
	          $(".invoice-commission-field").addClass('hidden');
	      }
	      $(".existing-customer-field").removeClass('hidden');
	      $(".btn-row").removeClass('hidden');

	    }
	    else if(this.value=='existing')
	    {
	        $(".existing-customer-field").removeClass('hidden');
	        $(".new-customer-field").addClass('hidden');
	        $(".email-field").removeClass('hidden');
	        if($("#operation_type").val()=='invoice')
	        {
	            $(".invoice-field").removeClass('hidden');
	            $(".invoiceqr-commission-field").removeClass('hidden');
	            $(".invoice-commission-field").removeClass('hidden');
	        }
	        else
	        {
	            $(".invoice-field").addClass('hidden');
	            $(".invoiceqr-commission-field").removeClass('hidden');
	            $(".invoice-commission-field").addClass('hidden');
	        }
	    }
	});
	/** custom upload file **/
document.getElementById("uploadBtn").onchange = function () {
    document.getElementById("certificate_file").value = this.value.substring(12);
};
});
</script>
<style>
	.ui-autocomplete{z-index: 9999;}.form-control:disabled, .form-control[readonly] {background-color: #fff;}
</style>
@endsection