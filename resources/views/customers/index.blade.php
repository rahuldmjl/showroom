<?php
use App\Helpers\CustomersHelper;
use App\Helpers\InventoryHelper;
$totalCustomers = $customersData['totalCount'];
$customerCollection = $customersData['customerCollection'];
//echo '<pre>';
//print_r($customerCollection);exit;
$countryList = array('country_id' => 'IN', 'name' => 'India');
?>
@extends('layout.mainlayout')

@section('title', 'Customers')

@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
  	{{ Breadcrumbs::render('customers.index') }}
      <!-- /.page-title-right -->
  </div>
  <!-- /.page-title -->
  <!-- =================================== -->
  <!-- Different data widgets ============ -->
  <!-- =================================== -->
  <div class="col-md-12 widget-holder loader-area" style="display: none;">
    <div class="widget-bg text-center">
      <div class="loader"></div>
    </div>
  </div>
  	<div class="widget-list">
      	<div class="row">
  			<div class="col-md-12 widget-holder content-area">
  				<div class="widget-bg">
  					<div class="widget-heading clearfix">
  						<h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100">Customers</h5>
						<div class="btn-top-right2">
						  <button class="btn btn-primary small-btn-style ripple pointer color-white" data-toggle="modal" data-target="#add-customer-modal"><i class="material-icons list-icon fs-24">playlist_add</i> Add Customer</button>
						</div>
  					</div>
  					<div class="widget-body clearfix dataTable-length-top-0">
  						@if ($message = Session::get('success'))
	                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show customer-alert-message" role="alert">
	                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	                        <i class="material-icons list-icon">check_circle</i>
	                        <strong>Success</strong>: {{ $message }}
	                      </div>
	                    @endif
	                    <table class="table table-striped table-center word-break mt-0" id="customersListTable" >
  							<thead>
  								<tr class="bg-primary">
  									<th>Name</th>
  									<th>FRN Code</th>
  									<th>DML Code</th>
  									<th>Contact Number</th>
  									<th>Location</th>
  									<th>Approval Products</th>
  									<th>Action</th>
  								</tr>
  							</thead>
  							<tbody>
		  						<?php
foreach ($customerCollection as $key => $customer) {
	$customerName = !empty(InventoryHelper::getCustomerName($customer->entity_id)) ? InventoryHelper::getCustomerName($customer->entity_id) : '-';
	$contactNumber = !empty(CustomersHelper::getCustomerAttrValue($customer->entity_id, 'primary_contact')) ? CustomersHelper::getCustomerAttrValue($customer->entity_id, 'primary_contact') : '-';
	$location = !empty(CustomersHelper::getCustomerAttrValue($customer->entity_id, 'location')) ? CustomersHelper::getCustomerAttrValue($customer->entity_id, 'location') : '-';
	$totalApprovalProducts = DB::select("select count(1) as total_approval FROM dml_approval_memo_histroy as memo_histroy JOIN dml_approval_memo as memo ON memo.id=memo_histroy.approval_memo_id WHERE memo.customer_id=" . $customer->entity_id . " AND memo_histroy.status='approval'");
	$totalApprovalProducts = isset($totalApprovalProducts[0]->total_approval) ? $totalApprovalProducts[0]->total_approval : '-';
	?>
									<tr>
										<td>{{$customerName}}</td>
										<td>{{$customer->frn_code}}</td>
										<td>DML{{$customer->entity_id}}</td>
										<td>{{$contactNumber}}</td>
										<td>{{$location}}</td>
										<td>{{$totalApprovalProducts}}</td>
										<td>
											<a class="color-content table-action-style" href="{{ route('customers.view',['id'=>$customer->entity_id]) }}"><i class="material-icons md-18">remove_red_eye</i></a>
											<?php
$user = Auth::user();
	?>
											<?php if ($user->hasRole('Super Admin')): ?>
												<a class="color-content table-action-style btn-delete-customer" data-href="{{ route('customers.delete',['id'=>$customer->entity_id]) }}" style="cursor:pointer;"><i class="material-icons md-18">delete</i></a>
											<?php endif;?>
										</td>
									</tr>
			  						<?php
}
?>
		  					</tbody>
	  					</table>
  					</div>
  				</div>
  			</div>
  		</div>
    </div>
  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->
<div class="modal fade bs-modal-lg" id="add-customer-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog modal-color-scheme modal-lg">
    <div class="modal-content">
		{!! Form::open(array('method'=>'POST','id'=>'add-customer-form','class'=>'form-horizontal','autocomplete'=>'nope','enctype'=>'multipart/form-data')) !!}


		<div class="modal-header text-inverse">
			<button type="button" class="close p-0 m-0" data-dismiss="modal" aria-hidden="true">×</button>
			<h5 class="modal-title" id="">Add Customer</h5>
		</div>
		<div class="modal-body">
			<div class="row medium-input">
				<div class="col-md-4 mb-3">
					<label for="txtfirstname">First Name <span class="text-danger">*</span></label>
					{!! Form::text('txtfirstname', null, array('class' => 'form-control required','id'=>'txtfirstname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
				</div>
				<div class="col-md-4 mb-3">
					<label for="txtlastname">Last Name <span class="text-danger">*</span></label>
					{!! Form::text('txtlastname', null, array('class' => 'form-control','id'=>'txtlastname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
				</div>
				<div class="col-md-4 mb-3">
					<label for="txtcontactnumber">Contact Number <span class="text-danger">*</span></label>
					{!! Form::text('txtcontactnumber', null, array('class' => 'form-control','id'=>'txtcontactnumber','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
				</div>
				<div class="col-md-4 mb-3">
					<label for="txtaddress">Address <span class="text-danger">*</span></label>
					{!! Form::text('txtaddress', null, array('class' => 'form-control','id'=>'txtaddress','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
				</div>
				<div class="col-md-4 mb-3">
					<label for="selectcountry">Country <span class="text-danger">*</span></label>
					<select class="form-control" name="selectcountry" id="selectcountry" data-placeholder="Select">
						<option value="">Select</option>
						<option value="<?php echo $countryList['country_id']; ?>"><?php echo $countryList['name'] ?></option>
					</select>
				</div>
				<div class="col-md-4 mb-3 customer-state">
					<label for="txtstateprovince">State/Province <span class="text-danger">*</span></label>
					{!! Form::text('txtstateprovince', null, array('class' => 'form-control','id'=>'txtstateprovince','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
				</div>
				<div class="col-md-4 mb-3">
					<label for="txtcity">City <span class="text-danger">*</span></label>
					{!! Form::text('txtcity', null, array('class' => 'form-control','id'=>'txtcity','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
				</div>
				<div class="col-md-4 mb-3">
					<label for="txtzipcode">Zip Code <span class="text-danger">*</span></label>
					{!! Form::text('txtzipcode', null, array('class' => 'form-control','id'=>'txtzipcode','maxlength'=>'6','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
				</div>
				<div class="col-md-4 mb-3">
					<label for="txtemail">Email <span class="text-danger">*</span></label>
					{!! Form::email('txtemail', null, array('class' => 'form-control','id'=>'txtemail','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
				</div>
				<div class="col-md-4 mb-3">
					<label for="txtemail">GSTIN</label>
					{!! Form::text('txtgstin', null, array('class' => 'form-control','id'=>'txtgstin','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
				</div>
				<div class="col-md-4 mb-3">
					<label for="txtemail">GSTIN Attachment </label>
					<div class="input-group">
					  <div class="input-group-btn width-90">
						<div class="fileUpload btn w-100 btn-default">
						  <span><i class="glyphicon glyphicon-upload"></i> Upload</span>
						  <input id="gstinattachment" type="file" class="upload width-90" name="gstinattachment" accept="image/*,application/pdf">
						</div>
					  </div>
					  <input id="gstin_attachment_file" name="gstin_attachment_file" class="form-control border bg-transparent" placeholder="Choose File" disabled="disabled">
					</div>
				</div>
			</div>
			<div class="row medium-input">
				<div class="col-12">
					<h5 class="border-b-light-1 pb-2 mb-3">Franchise Details</h5>
				</div>
				<div class="col-md-6 mb-3">
					<label class="">Is Franchise? </label>
					<div class="radiobox radio-info">
						<label class="mr-2">
							 <input name="radioIsFranchise" type="radio" value="yes" checked="checked">
							 <span class="label-text">Yes</span>
						</label>
						<label>
							<input name="radioIsFranchise" type="radio" value="no"> <span class="label-text">No</span>
						</label>
					</div>
				</div>
				<div class="col-md-6 mb-3 txtfrncode-input-div">
					<label for="txtfrncode" id="frncode_label">FRN Code <span class="text-danger">*</span></label>
					{!! Form::text('txtfrncode', null, array('class' => 'form-control','id'=>'txtfrncode','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" id="btn-add-customer" class="btn btn-info ripple text-left">Submit</button>
			<button type="button" class="btn btn-danger ripple text-left" data-dismiss="modal">Close</button>
		</div>
		{!! Form::close() !!}
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<style type="text/css">
.form-control[readonly] {background-color: #fff;}
</style>
@endsection

@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.print.min.js"></script>
<script src="<?=URL::to('/');?>/js/jquery.validate.min.js"></script>
<script src="<?=URL::to('/');?>/js/additional-methods.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		document.getElementById("gstinattachment").onchange = function () {
			document.getElementById("gstin_attachment_file").value = this.value.substring(12);
		};
		$("#btn-add-customer").click(function(){
			$.validator.addMethod(
			  "regex",
			   function(value, element, regexp) {
				   if (regexp.constructor != RegExp)
					  regexp = new RegExp(regexp);
				   else if (regexp.global)
					  regexp.lastIndex = 0;
					  return this.optional(element) || regexp.test(value);
			   },"erreur expression reguliere"
			);
			$.validator.addMethod('filesize', function (value, element, param) {
				return this.optional(element) || (element.files[0].size <= param)
			}, 'File size must be less than {0}');
			$("#add-customer-form").validate({
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
					txtgstin:{
						regex : /^([0-9]){2}([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}([0-9]){1}([a-zA-Z]){1}([a-zA-Z0-9]){1}?$/,
					},
					gstinattachment: {
						extension: "png|jpg|jpeg|pdf",
						filesize: 2097152
					},
					txtzipcode:{
						required: true,
						number: true,
						maxlength: 6,
						minlength: 6
					},
					txtemail: {
						required: true,
						email: true
					},
					txtfrncode: {
						required: function(element){
								return $("input[name=radioIsFranchise]:checked").val()=="yes";
							}
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
					transportation_mode: "Transportation mode is required",
					txtshippingcharge:{
						number: "Invalid shipping charge",
					},
					txtgstin:{
						regex: "Invalid GSTIN"
					},
					gstinattachment:{
						extension: 'Invalid file type',
						filesize: 'File size must be less than 2 MB'
					},
					txtaddress: "Address is required",
					selectcountry: "Country is required",
					txtstateprovince: "State/Province is required",
					txtcity: "City is required",
					txtinvoicenumber: "Invoice number is required",
					txtinvoicedate: "Invoice date is required",
					txtmemonumber: "Approval number is required",
					txtapprovaldate: "Approval date is required",
					txtzipcode:{
						required: "Zip code is required",
						number: "Invalid zip code"
					},
					txtemail:{
						required: "Email is required",
						email: "Invalid email"
					},
					paymentmode: "Payment mode is required",
					discount_type: "Discount is required",
					txtdmusercodeemail: "DMUSERCODE or Email is required",
					//txtdiscountval: "Discount value is required",
					approval_type: "Approval type is required",
					deposit_type: "Deposit type is required",
					txtfrncode: "FRN Code is required"
				}
			});
			if($("#add-customer-form").valid())
			{
				var newCustomerForm=$("#add-customer-form");
				var formData = new FormData(newCustomerForm[0]);
				$.ajax({
					contentType: false,
					type: 'post',
					url: '<?=URL::to('/customers/createnewcustomer');?>',
					processData: false,
					cache: false,
					data: formData,
					beforeSend: function(){
						$("#btn-add-customer").prop("disabled",true);
						showLoader();
					},
					success: function(response){
						$("#btn-add-customer").prop("disabled",false);
						document.getElementById("add-customer-form").reset();
						hideLoader();
						var res = JSON.parse(response);

						if(res.status==true)
						{
							customersListTable.draw();
							$("#add-customer-modal").modal('hide');
							swal({
							  title: 'Success',
							  text: res.message,
							  type: 'success',
							  buttonClass: 'btn btn-primary'
							  //showSuccessButton: true,
							  //showConfirmButton: false,
							  //successButtonClass: 'btn btn-primary',
							  //successButtonText: 'Ok'
							});
						}
						else
						{
							$("#add-customer-modal").modal('hide');
							swal({
							  title: 'Oops!',
							  text: res.message,
							  type: 'error',
							  showCancelButton: true,
							  showConfirmButton: false,
							  confirmButtonClass: 'btn btn-danger',
							  cancelButtonText: 'Ok'
							});
						}
					},
					error: function(){
						hideLoader();
					}
				})
			}
		})
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
                            //console.log(item);
                            stateHtml+='<option value='+item.region_id+'>'+item.name+'</option>';
                        });
                        stateHtml+='</select>';
                        $(".customer-state").append(stateHtml);
                    }
                    else
                    {
                        $("#txtstateprovince").remove();
                        $(".customer-state").append('<input type="text" class="form-control" id="txtstateprovince" name="txtstateprovince" readonly="true" onfocus="this.removeAttribute(\'readonly\')">');
                    }
                }
            },
            error: function(){
                hideLoader();
                $("#btn-verify-customer").prop('disabled',false);
            }
        });
    });
		 $(document).on('change','input[name=radioIsFranchise]',function(){
			if(this.value == 'yes')
			{
				$('#frncode_label').html('FRN Code <span class="text-danger">*</span>');
				$('#txtfrncode').attr('required', true);
			}
			else
			{
				$('#frncode_label').html('FRN Code');
				$('#txtfrncode').attr('required', false);
			}
		});
		setTimeout(function(){
	        $(".customer-alert-message").removeClass('show');
	        $(".customer-alert-message").addClass('hidden');
	    }, 5000);
		$(document).on('click','.btn-delete-customer',function(){
			var deleteUrl = $(this).data('href');
		    swal({
		        title: 'Are you sure?',
		        text: "<?php echo Config::get('constants.message.customer_delete_confirmation_message'); ?>",
		        type: 'info',
		        showCancelButton: true,
		        confirmButtonText: 'Confirm',
		        confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
		        }).then(function(data) {
		        	if (data.value) {
		        		window.location.href = deleteUrl;
		        	}

		    });
		});
		$(document).on('click','.btn-view-quotation',function(){
			var customerId = $(this).data('customerid');
			if(customerId!='')
			{
				$.ajax({
	                type: 'post',
	                url: '<?=URL::to('/customers/getquotationcount');?>',
	                data:{customer_id:customerId,_token:"{{ csrf_token() }}"},
	                beforeSend: function(){
	                    $('.loader-area').show();
						$('.content-area').css({'opacity':'0.35', 'pointer-events':'none'});
	                    //$("#btn-verify-customer").prop('disabled',false);

	                },
	                success: function(response){
	                	var res = JSON.parse(response);
	                	$('.loader-area').hide();
                    	$('.content-area').css({'opacity':'1', 'pointer-events':'all'});
	                	if(res.status)
	                	{
	                		window.open('<?=URL::to('/customers/viewcustomerquotation');?>/'+customerId,'_blank');
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
	                	}
	                }
	            });
			}
		});
	});
	var customersListTable = $('#customersListTable').DataTable({

		"dom": "<'row mb-2 align-items-center'<'col-auto dataTable-length-tb-0'l><'col'B><'col'f>><'row'<'col-md-12't>><'row'<'col-md-3'i><'col-md-6 ml-auto'p>>",
		"lengthMenu": [[10, 50, 100, 200,500], [10, 50, 100, 200,500]],
		"buttons": [
			{
			extend: 'csv',
			footer: false,
			title: 'Customer-List-Data',
			className: "btn btn-primary btn-sm px-3",
			exportOptions: {
				columns: [0,1,2,3,4],
				orthogonal: 'export'
			}
			},
			{
			extend: 'excel',
			footer: false,
			title: 'Customer-List-Data',
			className: "btn btn-primary btn-sm px-3",
			exportOptions: {
				columns: [0,1,2,3,4],
				orthogonal: 'export'
			}
			}
		],
	  "language": {
	    "infoEmpty": "No matched records found",
	    "zeroRecords": "No matched records found",
	    "emptyTable": "No data available in table",
	    //"sProcessing": "<div id='loader'></div>"
	  },
	  "deferLoading": <?=$totalCustomers?>,
	  "processing": true,
	  "serverSide": true,
	  "pageLength": 10,
	  "serverMethod": "post",
	  "ajax":{
	    "url": '<?=URL::to('/customers/ajaxlist');?>',
	    "data": function(data, callback){
	    	data._token = "{{ csrf_token() }}";
	    	showLoader();
	    },
	    complete: function(response){
	      hideLoader();
	    }
	  },
	  "columnDefs": [
	      { "orderable": false, "targets": [0,1,2,3,4,5] }
	  ],
	});
	$('.dataTables_filter input')
	  .unbind() // Unbind previous default bindings
	  .bind("input", function(e) { // Bind our desired behavior
	      // If the length is 3 or more characters, or the user pressed ENTER, search
	      if(this.value.length >= 3 || e.keyCode == 13) {
	          // Call the API search function
	          customersListTable.search(this.value).draw();
	      }
	      // Ensure we clear the search if they backspace far enough
	      if(this.value == "") {
	          customersListTable.search("").draw();
	      }
	      return;
	});
	$("#customersListTable tr th").removeClass('sorting_asc');
</script>
@endsection