@extends('layout.mainlayout')

@section('title', 'Showroom')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

<?php /*
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/responsive.dataTables.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.1.7/css/ion.rangeSlider.min.css"/>
 */?>

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('showroom.orderhistory') }}
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
					<div class="widget-body clearfix dataTable-length-top-0">
            <table class="table table-striped table-center table-responsive" data-toggle="datatables" id="orderHistoryTable">
            <thead>
                <tr class="bg-primary">
                   <th>Order No</th>
                   <th>PO No</th>
                   <th>Total Qty</th>
                   <th>Order Total</th>
                   <th>Status</th>
                   <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $key => $order)
                  <tr>
                      <td>{{ $order->order_number }}</td>
                      <td>{{ $order->po_number }}</td>
                      <td>{{ $order->total_qty }}</td>
                      <td>{{ $order->order_total }}</td>
                      <td>
                        <select name="order_status" class="order-status form-control h-auto" data-order-id="<?=$order->id?>" id="order_status_<?=$order->id?>" onchange="changeorderstatus(<?=$order->id?>, '{{ csrf_token() }}', this)">

                          <option <?php if ($order->order_status == config('constants.labels.pending')) {echo 'selected="selected"';}?> value="<?php echo config('constants.labels.pending'); ?>"><?php echo config('constants.labels.pending'); ?></option>
                          <option <?php if ($order->order_status == config('constants.labels.given_to_vendor')) {echo 'selected="selected"';}?> value="<?php echo config('constants.labels.given_to_vendor'); ?>"><?php echo config('constants.labels.given_to_vendor'); ?></option>
                          <option <?php if ($order->order_status == config('constants.labels.in_progress')) {echo 'selected="selected"';}?> value="<?php echo config('constants.labels.in_progress'); ?>"><?php echo config('constants.labels.in_progress'); ?></option>
                          <option <?php if ($order->order_status == config('constants.labels.completed')) {echo 'selected="selected"';}?> value="<?php echo config('constants.labels.completed'); ?>"><?php echo config('constants.labels.completed'); ?></option>
                        </select>
                    </td>
                      <td>
                        <a title="View" class="color-content table-action-style" href="{{ route('showroom.orderview',$order->id) }}"><i class="material-icons md-18">remove_red_eye</i></a>
<?php
/*
?>
<a class="color-content table-action-style" href="#"><i class="material-icons md-18">edit</i></a>
<a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deleteorder({{$order->id}}, '{{ csrf_token() }}');" data-token="{{ csrf_token() }}"><i class="material-icons md-18">delete</i></a>

{!! Form::open(['method' => 'DELETE','route' => ['orders.destroy', $order->id],'style'=>'display:none']) !!}
{!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
{!! Form::close() !!}
 */
?>
                      </td>
                  </tr>
                  @endforeach
            </tbody>
            <!-- <tfoot>
                <tr>
                   <th>Order No</th>
                   <th>PO No</th>
                   <th>Total Qty</th>
                   <th>Order Total</th>
                   <th>Status</th>
                   <th>Action</th>
                </tr>
            </tfoot> -->
        </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->
<input type="hidden" id="showroomAjax" value="<?=URL::to('/showroom/ajaxlist');?>">
@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>

<script type="text/javascript">
/*var table = $('#orderHistoryTable').DataTable({
        "scrollX": true,
        "header": "jqueryui",
        "pageButton": "bootstrap"
    });*/

function changeorderstatus(Id, token, selectbox){
  var selectedVal = $(selectbox).children("option:selected").val();
  console.log(selectedVal);
  $.ajax({
      url: '<?=URL::to('/');?>'+'/showroom/changeorderstatus/'+Id,
      type: 'PUT',
      dataType: "JSON",
      data: {
          "id": Id,
          "status": selectedVal,
          "_token": token,
          //"_method": "PUT",
      },
      success: function (data)
      {
          swal({
            title: 'Changed!',
            text: 'Order Status has been changed.',
            type: 'success',
            confirmButtonClass: 'btn btn-success',
            cancelButtonText: "Cancel",
          }).then((value) => {
            //location.reload();
          });
      }
  });
}
</script>
@endsection