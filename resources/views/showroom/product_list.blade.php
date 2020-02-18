@extends('layout.mainlayout')

@section('title', 'Product List')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
    {{ Breadcrumbs::render('showroom.product_list') }}
   
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
                      <h5>{{'Product List'}}</h5>
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
                      
                       <table class="productlist table table-striped table-responsive" style="overflow-x: auto;display: block;" >
                          <thead>
                              <tr>
                                 <th>No</th>
                                 <th>Image</th>
                                 <th>item</th>
                                 <th>Po Number</th>
                                 <th>Order No</th>
                                 <th>Certificate No</th>
                                 <th>Sku</th>
                                 <th>Style</th>
                                 <th>Metal Carat</th>
                                 <th>Color</th>
                                 <th>Ringsize</th>
                                 <th>Product Category</th>
                                 <th>Gross Weight</th>
                                 <th>Metal Weight</th>
                                 <th>Metal Rate</th>
                                 <th>Metal Amount</th>
                                 <th>Labour Amount</th>
                                 <th>Diamond PCS</th>
                                 <th>Diamond Weight</th>
                                 <th>Colorstone PCS</th>
                                 <th>Colorstone Weight</th>
                                 <th>Material Category</th>
                                 <th>Material Type</th>
                                 <th>Material Quality</th>
                                 <th>Seive Size</th>
                                 <th>Material MM Size</th>
                                 <th>Material PCS</th>
                                 <th>Material Weight</th>
                                 <th>Stone Rate</th>
                                 <th>Stone Amount</th>
                                 <th>Total Stone Amount</th>
                                 <th>Total Amount</th>
                                 <th>Costingdata Id</th>
                                 <th>SGST</th>
                                 <th>CGST</th>
                                 <th>IGI Charges</th>
                                 <th>HallMarking</th>
                                 <th>QC Status</th>
                                 <th>Is IGI</th>
                                 <th>Branding</th>
                                 <th>Request Invoice</th>
                                 <th>Return Memo</th>
                                 <th>Batch No</th>
                                 <th>Approved By</th>
                                 <th>Rejected By</th>
                                 <th>IGI By</th>
                                 <th>Invoice Requested By</th>
                                 <th>Memo Returned By</th>
                                 <th>Extra Price</th>
                                 <th>Extra Price For</th>
                                 <th>Created</th>
                                 <th>Updated</th>
                              </tr>
                          </thead>
                          <tbody>
                            @foreach($data as $value)
                            <tr>
                              <td>{{$value->id}}</td>
                              <td>
                                @if(File::exists($value->image))
                                     <img src="<?=URL::to('/');?>/{{$value->image}}">
                                  
                                @else
                                   
                                      <img src="<?=URL::to('/');?>/img/def_img.png">
                                   
                                @endif
                              </td>
                              <td>{{$value->item}}</td>
                              <td>{{$value->po_no}}</td>
                              <td>{{$value->order_no}}</td>
                              <td>{{$value->certificate_no}}</td>
                              <td>{{$value->sku}}</td>
                              <td>{{$value->style}}</td>
                              <td>{{$value->metal_karat}}</td>
                              <td>{{$value->color}}</td>
                              <td>{{$value->ringsize}}</td>
                              <td>{{$value->product_category}}</td>
                              <td>{{$value->gross_weight}}</td>
                              <td>{{$value->metal_weight}}</td>
                              <td>{{$value->metalrate}}</td>
                              <td>{{$value->metalamount}}</td>
                              <td>{{$value->labouramount}}</td>
                              <td>{{$value->diamond_pcs}}</td>
                              <td>{{$value->diamond_weight}}</td>
                              <td>{{$value->colorstone_pcs}}</td>
                              <td>{{$value->colorstone_weight}}</td>
                              <td>{{$value->material_category}}</td>
                              <td>{{$value->material_type}}</td>
                              <td>{{$value->material_quality}}</td>
                              <td>{{$value->seive_size}}</td>
                              <td>{{$value->material_mm_size}}</td>
                              <td>{{$value->material_pcs}}</td>
                              <td>{{$value->material_weight}}</td>
                              <td>{{$value->stone_rate}}</td>
                              <td>{{$value->stone_amount}}</td>
                              <td>{{$value->total_stone_amount}}</td>
                              <td>{{$value->total_amount}}</td>
                              <td>{{$value->costingdata_id}}</td>
                              <td>{{$value->sgst}}</td>
                              <td>{{$value->cgst}}</td>
                              <td>{{$value->igi_charges}}</td>
                              <td>{{$value->hallmarking}}</td>
                              <td>{{$value->qc_status}}</td>
                              <td>{{$value->is_igi}}</td>
                              <td>{{$value->branding}}</td>
                              <td>{{$value->request_invoice}}</td>
                              <td>{{$value->return_memo}}</td>
                              <td>{{$value->batch_no}}</td>
                              <td>{{$value->approved_by}}</td>
                              <td>{{$value->rejected_by}}</td>
                              <td>{{$value->igi_by}}</td>
                              <td>{{$value->invoice_requested_by}}</td>
                              <td>{{$value->memo_returned_by}}</td>
                              <td>{{$value->extra_price}}</td>
                              <td>{{$value->extra_price_for}}</td>
                              <td>{{$value->created_at}}</td>
                              <td>{{$value->updated_at}}</td>
                             @endforeach 
                            </tr>
                          </tbody>
                          <tfoot>
                               <th>No</th>
                                 <th>Image</th>
                                 <th>item</th>
                                 <th>Po Number</th>
                                 <th>Order No</th>
                                 <th>Certificate No</th>
                                 <th>Sku</th>
                                 <th>Style</th>
                                 <th>Metal Carat</th>
                                 <th>Color</th>
                                 <th>Ringsize</th>
                                 <th>Product Category</th>
                                 <th>Gross Weight</th>
                                 <th>Metal Weight</th>
                                 <th>Metal Rate</th>
                                 <th>Metal Amount</th>
                                 <th>Labour Amount</th>
                                 <th>Diamond PCS</th>
                                 <th>Diamond Weight</th>
                                 <th>Colorstone PCS</th>
                                 <th>Colorstone Weight</th>
                                 <th>Material Category</th>
                                 <th>Material Type</th>
                                 <th>Material Quality</th>
                                 <th>Seive Size</th>
                                 <th>Material MM Size</th>
                                 <th>Material PCS</th>
                                 <th>Material Weight</th>
                                 <th>Stone Rate</th>
                                 <th>Stone Amount</th>
                                 <th>Total Stone Amount</th>
                                 <th>Total Amount</th>
                                 <th>Costingdata Id</th>
                                 <th>SGST</th>
                                 <th>CGST</th>
                                 <th>IGI Charges</th>
                                 <th>HallMarking</th>
                                 <th>QC Status</th>
                                 <th>Is IGI</th>
                                 <th>Branding</th>
                                 <th>Request Invoice</th>
                                 <th>Batch No</th>
                                 <th>Approved By</th>
                                 <th>Rejected By</th>
                                 <th>IGI By</th>
                                 <th>Invoice Requested By</th>
                                 <th>Memo Returned By</th>
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
<script type="text/javascript">
   
  var vendorlist = $('.productlist').DataTable({

  "language": {
    "infoEmpty": "No matched records found",
    "zeroRecords": "No matched records found",
    "emptyTable": "No data available in table",
    //"sProcessing": "<div id='loader'></div>"
  },
  "deferLoading": <?=$totalcount?>,
  "processing": true,
  "serverSide": true,
  "serverMethod": "GET",
  "ajax":{
    "url": "{{action('ShowroomController@showroom_response')}}",
    "data": function(data, callback){
      for (var i = 0, len = data.columns.length; i < len; i++) {
        if (! data.columns[i].search.value) delete data.columns[i].search;
        if (data.columns[i].searchable === true) delete data.columns[i].searchable;
        if (data.columns[i].orderable === true) delete data.columns[i].orderable;
        if (data.columns[i].data === data.columns[i].sku) delete data.columns[i].sku;
       
      }
      
    }
  },"columnDefs": [ {
    "targets": [0,1,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51],
    "orderable": false
    }
  ]  
});
</script>
@endsection
