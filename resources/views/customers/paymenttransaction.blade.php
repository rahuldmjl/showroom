<div class="col-md-12 title">
  <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Transactions</h4>
</div>
<input type="hidden" id="paymentId" value="{{$paymentId}}">
<?php if($totalCount > 0) :?>
<div class="col-md-12 transaction-container">
	<table class="table table-striped thumb-sm table-center table-head-box checkbox checkbox-primary custom-scroll" id="transactionListTable">
	    <thead>
	        <tr class="bg-primary">
	              <th>No</th>
		          <th>Customer Name</th>
		          <th>Invoice Number</th>
		          <th>Invoice Amount</th>
		          <th>Remaining Amount</th>
		          <th>Paid Date</th>
	        </tr>
	    </thead>
	    <tbody>
	        <?php 
	        $index = 0;
	        foreach($transactionData as $key => $transaction):?>
	          <tr>
	                <td>{{++$index}}</td>
	             	<td>{{ $transaction->customer_name}}</td>
	             	<td>{{ $transaction->invoice_number}}</td>
	             	<td class="WebRupee">&#x20B9; {{ $transaction->invoice_amount}}</td>
	             	<td class="WebRupee">&#x20B9;{{$transaction->remaining }}</td>
	             	<td>{{ $transaction->paid_at}}</td>
	          </tr>
	        <?php endforeach;?>
	    </tbody>
	    <tfoot>
	        <tr>
	              <th>No</th>
		          <th>Customer Name</th>
		          <th>Invoice Number</th>
		          <th>Invoice Amount</th>
		          <th>Remaining Amount</th>
		          <th>Paid Date</th>
	        </tr>
	    </tfoot>
	</table>
	<?php else:?>
		<div class="col-md-12">
			<p> No data available</p>
		</div>
	<?php endif;?>
</div>
<div class="col-md-12 title">
	<a class="btn btn-info small-btn-style text-white" id="btn-back-payment">Go Back</a>
</div>
<script>
  $(document).ready(function(){
    $(document).on("click","#btn-back-payment", function(){
        $("#paymenttransactionlist").addClass('hidden');
        $("#paymenthistroy").removeClass('hidden');
    });
    var transactionListTable = $('#transactionListTable').DataTable({
          "language": {
            "infoEmpty": "No matched records found",
            "zeroRecords": "No matched records found",
            "emptyTable": "No data available in table",
            "search": "_INPUT_",
            "searchPlaceholder": "Search",
          //"sProcessing": "<div id='loader'></div>"
        },
        "deferLoading": <?=$totalCount?>,
        "processing": true,
        "serverSide": true,
        "serverMethod": "post",
        "ajax":{
          "url": '<?=URL::to('/customers/paymenttransactionajaxlist');?>',
          "data": function(data, callback){
            data._token = "{{ csrf_token() }}";
            data.customer_id = $("#customer_id").val();
            data.payment_id = $("#paymentId").val();
            showLoader();
            $(".dropdown").removeClass('show');
            $(".dropdown-menu").removeClass('show');
          },
          complete: function(response){
            hideLoader();
          }
        },
        "dom": '<"datatable_top_custom_lengthinfo custom-page-length d-flex flex-wrap"i   <"datatable_top_showroom_length mx-3"l>>frtip',
        //"lengthChange": false,
      });
    $("#transactionListTable_length").addClass('mt-0');
    $("#transactionListTable_length").addClass('height-35 mt-1 mb-0');
    $('#paymenttransactionlist .dataTables_filter input')
      .unbind() // Unbind previous default bindings
      .bind("input", function(e) { // Bind our desired behavior
          // If the length is 3 or more characters, or the user pressed ENTER, search
          if(this.value.length >= 3 || e.keyCode == 13) {
              // Call the API search function
              transactionListTable.search(this.value).draw();
            }

          // Ensure we clear the search if they backspace far enough
          if(this.value == "") {
            transactionListTable.search("").draw();
          }
          return;
        });
});
</script>