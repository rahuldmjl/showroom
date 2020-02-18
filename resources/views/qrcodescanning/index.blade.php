<?php
use App\Helpers\InventoryHelper;
$totalScanning = InventoryHelper::scanningcount();
$inventoryStatus = InventoryHelper::getInventoryStatusOptions();
$inStatusVal = $inventoryStatus['in'];
$outStatusVal = $inventoryStatus['out'];
?>
@extends('layout.mainlayout')

@section('title', 'QR Code Scanning')

@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">

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
  					<div class="widget-header">
  						<h5 class="border-b-light-1 pb-2 mb-4 mt-0 w-100">Scanning Toolbar</h5>
  					</div>
  					<div class="alert alert-icon alert-success border-success alert-dismissible d-none fadeIn animated" role="alert" id="custom_ajax_alert"></div>
  					<div class="widget-body clearfix">
  						<div class="row m-0 label-text-pl-25">
  							<div class="tabs w-100">
  								<ul class="nav nav-tabs">
			                        <li class="nav-item active"><a class="nav-link" href="#scanning-view" data-toggle="tab" aria-expanded="true">Scan</a>
			                        </li>
			                        <li class="nav-item"><a class="nav-link" href="#manual-add" data-toggle="tab" aria-expanded="true">Manual Add</a>
			                        </li>
			                    </ul>
			                    <div class="tab-content p-3 border border-top-0">
					                <div class="tab-pane active" id="scanning-view">
					                    <div class="row custom-drop-style custom-select-style label-text-pl-25">
					                    	<div class="col-xl-6 col-sm-8">
					                          	<div class="form-group approval-person-filter">
					                          		<input type="text" class="form-control" id="writtingTextBox" name="writtingTextBox" placeholder="Tmp Write Certificate..." style="height: calc(2em + .75rem + 2px);" />
					                          	</div>
					                      	</div>
					                    </div>
					                    <div class="row custom-drop-style custom-select-style label-text-pl-25">
					                      	<div class="col-xl-6 col-sm-8">
					                          	<div class="form-group approval-person-filter">
					                          		<input type="text" class="form-control" id="scanningTextBox" name="scanningTextBox" placeholder="Put your cursor here when scanning" style="height: calc(2em + .75rem + 2px);" />
					                          	</div>
					                      	</div>
					                      	<div class="col-xl-2 col-sm-4">
					                          	<div class="form-group">
					                                <button class="btn w-100 btn-primary" id="btn_scan_and_submit" type="button">Add to List</button>
					                          	</div>
					                      	</div>
					                	</div>
					              	</div>
					                <div class="tab-pane" id="manual-add">
					                	<div class="row custom-drop-style custom-select-style label-text-pl-25">
					                		<div class="col-xl-6 col-sm-8">
					                			<div class="form-group approval-person-filter">
					                          		<input type="text" class="form-control" id="manualTextBox" name="manualTextBox" placeholder="Enter certificate to add in the list" style="height: calc(2em + .75rem + 2px);" />
					                          	</div>
					                      	</div>
					                      	<div class="col-xl-2 col-sm-4">
					                          	<div class="form-group">
					                                <button class="btn w-100 btn-primary" id="btn_manual_and_submit" type="button">Add to List</button>
					                          	</div>
					                      	</div>
					                    </div>
					                </div>
					            </div>
					        </div>
					    </div>
					</div>
				</div>
			</div>
		</div>
      	<div class="row">
  			<div class="col-md-12 widget-holder content-area">
  				<div class="widget-bg">
  					<div class="widget-header">
  						<h5 class="border-b-light-1 pb-2 mb-4 mt-0 w-100">Qrcode Scanning List</h5>
  					</div>
  					<div class="widget-body clearfix dataTable-length-top-0">
  						<table class="table table-striped table-center table-head-box checkbox checkbox-primary" id="scanningsListTable" >
  							<thead>
  								<tr class="bg-primary">
  									<th class="checkboxth"><label><input class="form-check-input " type="checkbox" name="chkAllScannings" id="chkAllScannings" ><span class="label-text"></span></label></th>
  									<th>Certificate</th>
  									<th>Action</th>
  								</tr>
  							</thead>
  							<tbody>


  								<?php
foreach ($allscannings as $key => $scanning) {
	//$scanningName = !empty(InventoryHelper::getCustomerName($scanning->entity_id)) ? InventoryHelper::getCustomerName($scanning->entity_id) : '-';
	?>
									<tr>
										<td><label><input data-productid="<?=$scanning->product_id;?>" class="form-check-input chkScanning" value="<?=$scanning->id;?>" data-certificate="<?=$scanning->certificate_no;?>" type="checkbox" name="chkScanning[]" id="chkScanning_<?=$scanning->id;?>" /><span class="label-text"></label></td>
										<td><?=$scanning->certificate_no?></td>
										<td>

											  <a href="javascript:void(0)"><i title="Detail" id="btn_detail" value ="{{$scanning->certificate_no}}" data-id="{{$scanning->certificate_no}}" class="material-icons list-icon">info</i></a>
											<a title="Delete Certificate" data-scanningid="<?=$scanning->id;?>" data-certificate="<?=$scanning->certificate_no;?>" class="color-content table-action-style1 btn-delete-certificate pointer"><i class="list-icon fa fa-trash-o"></i></a>
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
<div class="modal fade bs-modal-lg modal-color-scheme view" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
      	<button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
          <div class="modal-header text-inverse">
              <h5 class="modal-title" id="myLargeModalLabel">Product Detail</h5>
          </div>
          <div class="modal-body">
          </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- /.main-wrappper -->
<!-- Large Modal -->
<div class="modal fade bs-modal-lg" id="invoice-memo-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {!! Form::open(array('method'=>'POST','id'=>'invoicememo-generate-form','class'=>'form-horizontal','autocomplete'=>'nope','enctype'=>'multipart/form-data')) !!}

            {!! Form::close() !!}
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
            <!-- /.modal -->
<input type="hidden" id="exportProductExcelAction" value="<?=URL::to('/inventory/exportproductexcel');?>">
@endsection

@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){

		$('#writtingTextBox').keyup(function(){
			$('#scanningTextBox').val($(this).val());
		});

		//setup before functions
		var typingTimer;                //timer identifier
		var doneTypingInterval = 1000;  //time in ms, 5 second for example
		var $input = $('#writtingTextBox');

		//on keyup, start the countdown
		$input.on('keyup', function () {
		  clearTimeout(typingTimer);
		  typingTimer = setTimeout(doneTyping, doneTypingInterval);
		});

		//on keydown, clear the countdown
		$input.on('keydown', function () {
		  clearTimeout(typingTimer);
		});

		//user is "finished typing," do something
		function doneTyping () {
		  //alert("Scanned !");
		  $('#btn_scan_and_submit').trigger('click');
		}

	});

		$(document).on("click","#chkAllScannings",function(){
			$('.chkScanning').prop('checked', this.checked);
		});

		var scanningsListTable = $('#scanningsListTable').DataTable({
		  "aLengthMenu": [[25,50,100,200,300,500], [25,50,100,200,300,500]],
		  "iDisplayLength": 50,
		  "dom": 'l<"#scanning-toolbar">frtip',
		  "language": {
		    "infoEmpty": "No matched records found",
		    "zeroRecords": "No matched records found",
		    "emptyTable": "No data available in table",
		    "search": "_INPUT_",
            "searchPlaceholder": "Search",
            "lengthMenu": "Show _MENU_",
	          "info": "Showing _START_ to _END_ of _TOTAL_"
		    //"sProcessing": "<div id='loader'></div>"
		  },
		  "deferLoading": <?=$totalScanning?>,
		  "processing": true,
		  "serverSide": true,
		  "serverMethod": "post",
		  "ajax":{
		    "url": '<?=URL::to('/showroom/qrcodescanningajax');?>',
		    "data": function(data, callback){
		    	data._token = "{{ csrf_token() }}";
		    	showLoader();
		    },
		    complete: function(response){
		      hideLoader();
		    }
		  },
		  "columnDefs": [
		      { "orderable": false, "targets": [0,1,2] }
		  ],
		});

		$divContainer = $('<div class="scanning-action-container"/>').appendTo('#scanning-toolbar')
		$('#scanning-toolbar').addClass('submit-area d-inline-block');
		$select = $('<select class="mx-2 mr-3 height-35 padding-four vertical-middle" id="scanning_bulk_action"/>').appendTo($divContainer)
		$('<option data-code=""/>').val('delete_certificate').text('Delete Certificate').appendTo($select);
		$('<option data-code=""/>').val('invoice').text('Generate Invoice').appendTo($select);
		$('<option data-code=""/>').val('memo').text('Generate Memo').appendTo($select);
		$('<option data-code=""/>').val('return_memo').text('Generate Return Memo').appendTo($select);
		$('<option data-code="<?=$inStatusVal?>"/>').val('in').text('In').appendTo($select);
		$('<option data-code="<?=$outStatusVal?>"/>').val('out').text('Out').appendTo($select);
		$('<option/>').val('product_excel').text('Product Excel').appendTo($select);
		$('<option/>').val('quotation').text('Quotation').appendTo($select);
		$('<button class="btn btn-primary small-btn-style" type="button" id="btn_bulk_action_submit"/>').text('Submit').appendTo($divContainer);

		$('.dataTables_filter input')
		  .unbind() // Unbind previous default bindings
		  .bind("input", function(e) { // Bind our desired behavior
		      // If the length is 3 or more characters, or the user pressed ENTER, search
		      if(this.value.length >= 3 || e.keyCode == 13) {
		          // Call the API search function
		          scanningsListTable.search(this.value).draw();
		      }
		      // Ensure we clear the search if they backspace far enough
		      if(this.value == "") {
		          scanningsListTable.search("").draw();
		      }
		      return;
		});
		$("#scanningsListTable tr th").removeClass('sorting_asc');

		function show_custom_ajax_alert(message){
			$('*#custom_ajax_alert').html('<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><i class="material-icons list-icon">check_circle</i><strong>Success</strong> '+message);
    		$('*#custom_ajax_alert').removeClass('d-none');
    		$('*#custom_ajax_alert').addClass('d-block');
    		console.log($('.navbar').height());
    		/*$('html, body').animate({
    			scrollTop: $("#custom_ajax_alert").offset().top - $('.navbar').height()
		    }, 1000);*/
		    setTimeout(function(){
		    	$('*#custom_ajax_alert').removeClass('d-block');
    			$('*#custom_ajax_alert').addClass('d-none');
		    }, 3000);
		}

		$(document).on('click','.btn-delete-certificate',function(){

			var scanningId = $(this).data('scanningid');
			var certificate_no = $(this).data('certificate');
			 swal({
		        title: 'Are you sure?',
		        text: "This certificate will be permanantely deleted from this list",
		        type: 'error',
		        showCancelButton: true,
		        confirmButtonClass: 'btn btn-danger',
		        confirmButtonText: 'Yes, delete it!'
		      }).then(function () {
					if(scanningId!='')
					{
						$.ajax({
			                type: 'post',
			                url: '<?=URL::to('/showroom/deletecertfromscanning');?>',
			                data:{scanning_id:scanningId,certificate_no:certificate_no,_token:"{{ csrf_token() }}"},
			                beforeSend: function(){
			                    $('.loader-area').show();
								$('.content-area').css({'opacity':'0.35', 'pointer-events':'none'});
			                    //$("#btn-verify-scanning").prop('disabled',false);

			                },
			                success: function(response){
			                	var res = JSON.parse(response);
			                	console.log(res.status);
			                	if(res.status)
			                	{
			                		scanningsListTable.draw();
				            		$('.loader-area').hide();
				            		$('.content-area').css({'opacity':'1', 'pointer-events':'all'});
				            		show_custom_ajax_alert(res.message);
			                	}
			                	else
			                	{
			                		$('.loader-area').hide();
			            			$('.content-area').css({'opacity':'1', 'pointer-events':'all'});
			                		swal({
			                          title: 'Oops!',
			                          text: res.message,
			                          type: 'error',
			                          showCancelButton: true,
			                          showConfirmButton: true,
			                          confirmButtonClass: 'btn btn-danger',
			                          cancelButtonText: 'Ok'
			                        });
			                	}
			                }
			            });
					}
		});
		});

		function validURL(str) {
		  var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
		    '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
		    '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
		    '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
		    '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
		    '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
		  return !!pattern.test(str);
		}

		var getUrlParameter = function getUrlParameter(url, param) {
			var urlVariablesStr = url.split("?");
			console.log(urlVariablesStr);
			if(urlVariablesStr.length > 1){
				var urlVariables = urlVariablesStr[1].split("&");
				for (i = 0; i < urlVariables.length; i++) {
			        sParameterName = urlVariables[i].split('=');

			        if (sParameterName[0] === param) {
			            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
			        }
			    }
			} else {
				var urlVariables = urlVariablesStr[0].split("/");
				for (i = 0; i < urlVariables.length; i++) {
			        sParameterName = urlVariables[i];
			        //alert(sParameterName);
			        if (sParameterName === param) {
			            return sParameterName === undefined ? true : decodeURIComponent(urlVariables[i+1]);
			        }
			    }
			}
		};

		function add_ajax(scanned_url){

			//alert(certificate_no);

			if(validURL(scanned_url)){
				certificate_no = getUrlParameter(scanned_url, 'certificate_no');
			} else {
				certificate_no = scanned_url;
			}

			//alert(certificate_no);

			//return;

			//console.log(certificate_no);

			if(certificate_no !== '' && certificate_no !== undefined)
			{
				$.ajax({
		            type: 'post',
		            url: '<?=URL::to('/showroom/addtoscanninglist');?>',
		            data:{certificate_no:certificate_no,_token:"{{ csrf_token() }}"},
		            beforeSend: function(){
		                $('.loader-area').show();
						$('.content-area').css({'opacity':'0.35', 'pointer-events':'none'});

		            },
		            success: function(response){
		            	$('#writtingTextBox').val('');
		            	$('#scanningTextBox').val('');
		            	$('#manualTextBox').val('');
		            	var res = JSON.parse(response);
		            	if(res.status)
		            	{
		            		scanningsListTable.draw();
		            		$('.loader-area').hide();
		            		$('.content-area').css({'opacity':'1', 'pointer-events':'all'});
		            		show_custom_ajax_alert(res.message);
		            	}
		            	else
		            	{
		            		$('.loader-area').hide();
		            		$('.content-area').css({'opacity':'1', 'pointer-events':'all'});
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
			} else {

				if(validURL(scanned_url)){
					product_id = getUrlParameter(scanned_url, 'product');
				} else {
					product_id = scanned_url;
				}

                if(product_id !== '' && product_id !== undefined)
				{
					$.ajax({
			            type: 'post',
			            url: '<?=URL::to('/showroom/addtoscanninglist');?>',
			            data:{product_id:product_id,_token:"{{ csrf_token() }}"},
			            beforeSend: function(){
			                $('.loader-area').show();
							$('.content-area').css({'opacity':'0.35', 'pointer-events':'none'});

			            },
			            success: function(response){
			            	$('#writtingTextBox').val('');
			            	$('#scanningTextBox').val('');
			            	$('#manualTextBox').val('');
			            	var res = JSON.parse(response);
			            	if(res.status)
			            	{
			            		scanningsListTable.draw();
			            		$('.loader-area').hide();
			            		$('.content-area').css({'opacity':'1', 'pointer-events':'all'});
			            		show_custom_ajax_alert(res.message);
			            	}
			            	else
			            	{
			            		$('.loader-area').hide();
			            		$('.content-area').css({'opacity':'1', 'pointer-events':'all'});
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
				} else {
					$('#writtingTextBox').val('');
	            	$('#scanningTextBox').val('');
	            	$('#manualTextBox').val('');
	            	$('.loader-area').hide();
	        		$('.content-area').css({'opacity':'1', 'pointer-events':'all'});
	        		swal({
	                  title: 'Oops!',
	                  text: 'There is no Certificate',
	                  type: 'error',
	                  showCancelButton: true,
	                  showConfirmButton: false,
	                  confirmButtonClass: 'btn btn-danger',
	                  cancelButtonText: 'Ok'
	                });
				}
			}
		}

		$(document).on('click','#btn_scan_and_submit',function(){
			var certificate_no = $('#scanningTextBox').val();
			//alert(certificate_no);
			add_ajax(certificate_no);
		});

		$(document).on('click','#btn_manual_and_submit',function(){
			var certificate_no = $('#manualTextBox').val();
			//alert(certificate_no);
			add_ajax(certificate_no);
		});

		//For bulk action
		$("#btn_bulk_action_submit").click(function(){
			var certificate_no = $('#manualTextBox').val();
		    var action = $('#scanning_bulk_action option:selected').val();
		    var certificateIds = new Array();
		    var scanningIds = new Array();
		    jQuery.each($(".chkScanning:checked"), function() {
		        scanningIds.push(jQuery(this).val());
		        certificateIds.push(jQuery(this).data('certificate'));
		    });
		    var scannings = scanningIds.join(",");

		    var productIds = new Array();
		    $.each($(".chkScanning:checked"), function() {
		        productIds.push($(this).data('productid'));
		    });
		    var ids = productIds.join(",");

		    var certificates = certificateIds.join(",");

		    if(action=="delete_certificate"){

		    	swal({
		        title: 'Are you sure?',
		        text: "This certificate will be permanantely deleted from this list",
		        type: 'error',
		        showCancelButton: true,
		        confirmButtonClass: 'btn btn-danger',
		        confirmButtonText: 'Yes, delete it!'
		      }).then(function () {
		    	//alert(action);
		        if(scannings!='')
		        {
		          $.ajax({
		              url:'<?=URL::to('/showroom/bulkdeletecertfromscanning');?>',
		              method:"post",
		              data:{scannings: scannings,certificates: certificates,_token: "{{ csrf_token() }}"},
		              beforeSend: function()
		              {
		                $('.loader-area').show();
						$('.content-area').css({'opacity':'0.35', 'pointer-events':'none'});
		              },
		              success: function(response){
		              	var res = JSON.parse(response);
		              	console.log(res);
		              	if(res.status)
	                	{
	                		scanningsListTable.draw();
		            		$('.loader-area').hide();
		            		$('.content-area').css({'opacity':'1', 'pointer-events':'all'});
		            		show_custom_ajax_alert(res.message);
	                	}
	                	else
	                	{
	                		$('.loader-area').hide();
	            			$('.content-area').css({'opacity':'1', 'pointer-events':'all'});
	                		if(res.type == 'error'){
	                			swal({
		                          title: 'Oops!',
		                          text: res.message,
		                          type: res.type,
		                          showCancelButton: true,
		                          showConfirmButton: false,
		                          confirmButtonClass: 'btn btn-danger',
		                          cancelButtonText: 'Ok'
		                        });
	                		} else {
	                			swal({
		                          title: 'Oops!',
		                          text: res.message,
		                          type: res.type,
		                          showCancelButton: true,
		                          showConfirmButton: false,
		                          confirmButtonClass: 'btn btn-danger',
		                          cancelButtonText: 'Ok'
		                        });
		                        scanningsListTable.draw();
	                		}
	                	}
		              }
		            })
		        }
		        else
		        {
		            swal({
			              title: 'Are you sure?',
			              text: "<?php echo Config::get('constants.message.inventory_generate_memo_product_not_selected'); ?>",
			              type: 'error',
			              showCancelButton: true,
			              showConfirmButton: false
			        });
			    }
			});
		}
		else if(action == 'invoice' || action == 'memo'){

				    if(ids!='')
			        {
			            $.ajax({
			              url:'<?=URL::to('/inventory/getinvoicememomodalcontent');?>',
			              method:"post",
			              data:{productIds: ids, action: action,_token: "{{ csrf_token() }}"},
			              success: function(response){
			                  $("#invoice-memo-modal #invoicememo-generate-form").html(response);
			                  $("#invoice-memo-modal").modal("show");
			              }
			            })
			        }
			    }
			    else if(action == 'return_memo'){
			    	if(ids!='')
			        {
			          $.ajax({
			              url:'<?=URL::to('/inventory/generatereturnmemo');?>',
			              method:"post",
			              data:{productIds: ids,_token: "{{ csrf_token() }}"},
			              beforeSend: function()
			              {
			                showLoader();
			              },
			              success: function(response){
			                  hideLoader();
			                  var res = JSON.parse(response);
			                  if(res.status)
			                  {
			                      swal({
			                        title: 'Success',
			                        text: res.message,
			                        type: 'success',
			                        buttonClass: 'btn btn-primary'
			                      }).then(function() {
			                            window.location.href = '<?=URL::to('/inventory/returnmemolist');?>';
			                        });
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
			            })
			        }
			    }
			    else if(action == 'in' || action == 'out'){
			    	var inventoryCode = '';
				    if($('#scanning_bulk_action option:selected').data('code'))
				    {
				      inventoryCode = $('#scanning_bulk_action option:selected').data('code');
				    }
				    if(inventoryCode != ''){
				    	$.ajax({
				            type: "POST",
				            dataType: "json",
				            data: {
				              status:action,productIds:ids,inventoryCode:inventoryCode,_token: "{{ csrf_token() }}"
				            },
				            url: '<?=URL::to('/inventory/changeinventorystatus');?>',
				            beforeSend: function()
				            {
				              showLoader();
				            },
				            success: function(data) {
				              if(data.status)
				              {
				                  swal({
				                    title: 'Success',
				                    text: data.message,
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
				                swal({
				                  title: 'Oops!',
				                  text: data.message,
				                  type: 'error',
				                  showCancelButton: true,
				                  showConfirmButton: false,
				                  confirmButtonClass: 'btn btn-danger',
				                  cancelButtonText: 'Ok'
				                });
				              }
				              hideLoader();
				          }
				      });
				    }
			    }
			    else if(action == 'product_excel'){
			    	alert(ids);
			    	if(ids!='')
			        {
			        	/*$.ajax({
		                    url:'<?=URL::to('/inventory/storeqrproductids');?>',
		                    method:"post",
		                    data:{productIds: productIds,_token: "{{ csrf_token() }}"},
		                    beforeSend: function()
		                    {
		                      showLoader();
		                    },
		                    success: function(response){
		                        hideLoader();
		                        var res = JSON.parse(response);
		                        if(res.status)
		                        {
						          	window.location.href = '<?php /*URL::to('/inventory/exportproductexcel'); */?>';
		                        }
		                    }
		            	});*/

		            	var url = $("#exportProductExcelAction").val()+'?productIds='+ids;
          				window.location.href = url;
			        }
			        else
			        {
			            swal({
			              title: 'Are you sure?',
			              text: "<?php echo Config::get('constants.message.inventory_export_excel_product_not_selected'); ?>",
			              type: 'info',
			              showCancelButton: true,
			              confirmButtonText: 'Confirm',
			              confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
			              }).then(function() {
			                  var url = '<?=URL::to('/inventory/exportproductexcel');?>';
			                  window.location.href = url;
			            });
			        }
			    }
			    else if(action == "quotation")
			    {
			    	if(ids!='')
			    	{
			    		$.ajax({
				              url:'<?=URL::to('/inventory/storeproductids');?>',
				              method:"post",
				              data:{productIds: ids,_token: "{{ csrf_token() }}"},
				              beforeSend: function()
				              {
				                showLoader();
				              },
				              success: function(response){
				                  hideLoader();
				                  var res = JSON.parse(response);
				                  if(res.status)
				                  {
				                      window.location.href = '<?=URL::to('/inventory/generatequotation');?>';
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
				            })
			    	}
			    	else
			    	{
			    		swal({
			              title: 'Oops!',
			              text: '<?=config('constants.message.inventory_status_product_not_selected');?>',
			              type: 'error',
			              showCancelButton: true,
			              showConfirmButton: false,
			              confirmButtonClass: 'btn btn-danger',
			              cancelButtonText: 'Ok'
			            });
			    	}
			    }
		    //}


		//});
		});

		$(document).on('click','#btn_detail', function() {
			var id = $(this).attr('data-id');

			jQuery.ajax({
				type: "GET",
				dataType: "json",
				url: "{{action('ShowroomController@viewdetail')}}",
				data: {
				"_token": '{{ csrf_token() }}',
				"id": id,
				},
				success: function(data) {
				  $('.modal-body').html(data.html);
				  console.log(data.html);
				  $('.view').modal('show');
				}
		   });
		});

</script>
@endsection