<?php
use App\VirtualBoxManagerProduct;
?>
@extends('layout.mainlayout')

@section('title', 'Virtual Box List')

@section('distinct_head')

<link href="<?= URL::to('/'); ?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('virtualboxmanager.vbboxlist') }}
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
                        <h5 class="border-b-light-1 pb-1 mt-0 mb-2 w-100">{{'Virtual Box List'}}</h5>
                    </div>
                    <!-- /.widget-heading -->
                    <div class="widget-body clearfix dataTable-length-top-0">

                        @if ($message = Session::get('success'))
                        <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                            <i class="material-icons list-icon">check_circle</i>
                            <strong>Success</strong>: {{ $message }}
                        </div>
                        @endif
                        
                        <table class="table table-center table-striped table-responsive" id="vbTable">
                            <thead>
                                <tr class="bg-primary">
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Price From</th>
                                    <th>Price To</th>
                                    <th>Categories</th>
                                    <th>Product Limit</th>
                                    <th>Added Product</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $i=0; ?>
                            @foreach ($vbCollections as $key => $vbCollectionsVal)
                            <?php
                            $rootCategoryId = DB::select("SELECT entity_id FROM catalog_category_flat_store_1 WHERE level=1");
                            $rootCategoryId = $rootCategoryId[0]->entity_id;
                            $categories = DB::select("SELECT DISTINCT catalog_category_flat_store_1.entity_id,catalog_category_flat_store_1.name FROM catalog_category_flat_store_1 JOIN catalog_category_product ON catalog_category_product.category_id=catalog_category_flat_store_1.entity_id WHERE catalog_category_flat_store_1.parent_id=" . $rootCategoryId ." AND catalog_category_flat_store_1.entity_id = ".$vbCollectionsVal->category_id."");
                            $vbProducts = VirtualBoxManagerProduct::where('vb_id',$vbCollectionsVal->id)->count();
                            //echo "<pre>";print_r($vbProducts);exit;
                            ?>
                            <tr class="result">
                                <td>{{ $vbCollectionsVal->code }}</td>
                                <td>{{ $vbCollectionsVal->name }}</td>
                                <td>{{ $vbCollectionsVal->price_from }}</td>
                                <td>{{ $vbCollectionsVal->price_to }}</td>
                                <td>{{ $categories[0]->name }}</td>
                                <td>{{ $vbCollectionsVal->products_limit }}</td>
                                <td>{{ $vbProducts }}</td>
                                <td>{{ $vbCollectionsVal->created_at }}</td>
                                <td>
                                <a class='color-content table-action-style' href="javascript:void(0);"><i title="Detail" onclick="showDetail('<?php echo $vbCollectionsVal->id; ?>')" class="material-icons list-icon md-18">info</i></a>
                                <a class="color-content table-action-style" title="Edit" href="{{ route('virtualboxmanager.editvb',$vbCollectionsVal->id) }}"><i class="material-icons md-18">edit</i></a>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Price From</th>
                                    <th>Price To</th>
                                    <th>Categories</th>
                                    <th>Product Limit</th>
                                    <th>Added Product</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
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
<div class="modal fade bs-modal-lg modal-color-scheme vbproduct_showDetail popup-scroll" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header text-inverse">
              <button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
              <h5 class="modal-title" id="myLargeModalLabel">Virtual Box Products</h5>
          </div>
          <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('distinct_footer_script')

<script src="<?= URL::to('/'); ?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.print.min.js"></script>
<script type="text/javascript">
var buttonCommon = {
        exportOptions: {
            format: {
                body: function ( data, row, column, node ) {
                    if (column === 7) {
                        data = data.replace(/(&nbsp;|<([^>]+)>)/ig, "");
                    }
                    return data;
                }
            }
        }
    };
    var vbTable = $('#vbTable').DataTable({
        "dom": "<'row mb-2 align-items-center'<'col-auto dataTable-length-tb-0'l><'col'B><'col'f>><'row'<'col-md-12't>><'row'<'col-md-3'i><'col-md-6 ml-auto'p>>",
        "language": {
            "infoEmpty": "No matched records found",
            "zeroRecords": "No matched records found",
            "emptyTable": "No data available in table",
            //"sProcessing": "<div id='loader'></div>"
        },
        "deferLoading": <?= $vbCount ?>,
        "aLengthMenu": [[10,25, 50, 100,200,300,500], [10,25, 50, 100,200,300,500]],
        "processing": true,
        "serverSide": true,
        "serverMethod": "post",
        "buttons": [
            $.extend( true, {}, buttonCommon, {
            extend: 'csv',
            footer: false,
            title: 'Virtual-Box-List',
            className: "btn btn-primary btn-sm px-3",
            exportOptions: {
                columns: [0,1,2,3,4,5,6,7],
                orthogonal: 'export'
            }
            }),
            $.extend( true, {}, buttonCommon, {
            extend: 'excel',
            footer: false,
            title: 'Virtual-Box-List',
            className: "btn btn-primary btn-sm px-3",
            exportOptions: {
                columns: [0,1,2,3,4,5,6,7],
                orthogonal: 'export'
            }
            })
        ],
        "columnDefs": [{
            "orderable": false,
            "targets": [4,6,8]
        }],
        "ajax": {
            "url": "{{action('VirtualBoxManagerController@ajaxvb')}}",
            "data": function(data, callback) {
                data._token = "{{ csrf_token() }}";
            }
        }
    });
function showDetail(id) {
    jQuery.ajax({
      type: "GET",
      dataType: "json",
      url: "<?=URL::to('/') . '/virtualboxmanager/showDetail'?>",
      data: {
      "_token": '{{ csrf_token() }}',
      "id": id
      },
      success: function(data) {
          if(data.success == false){
            swal({
                  title: 'Oops!',
                  text: data.message,
                  type: 'info',
                  showCancelButton: false,
                  showConfirmButton: true,
                  confirmButtonClass: 'btn btn-default',
                  cancelButtonText: 'Ok'
                });
          }else{
            $('.modal-body').html(data.html);
            $('.vbproduct_showDetail').modal('show');
          }
          
      }
   });
}
</script>
@endsection