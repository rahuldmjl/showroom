@extends('layout.mainlayout')

@section('title', 'Invoice Attachment')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('diamond.invoiceattachment') }}
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
                        <h5 class="box-title box-title-style mr-b-0">Add Invoice Attachment</h5>
                        <p class="text-muted">You can add invoice  by filling this form</p>

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

                        @if ($message = Session::get('success'))
                          <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                                <i class="material-icons list-icon">check_circle</i>
                                <strong>Success</strong>: {{ $message }}
                          </div>
                        @endif

                    <div class="attachment_form">
                      <form action="{{url('diamond/multiplefileupload')}}" enctype="multipart/form-data" method="post" id="myform">
                          <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                        <div class="row align-items-center">
                          <div class="col-md-12 col-lg-6">
                            <div class="form-group error-file">
                              <label for="">Multiple File Select</label>
                                <input required type="file" class="form-control" name="images[]" accept="application/pdf,image/png,image/jpeg, image/jpg"  multiple>
                            </div>
                          </div>
                          <div class="col-md-12 col-lg-6">
                            <div class="box-footer">
                              <button type="submit" class="btn btn-primary btn-save">Save</button>
                            </div>
                          </div>
                        </div>
                      </form>
                    </div>

                    <?php
if (count($invoicedatas) > 0) {?>

                        <div class="widget-body clearfix">

                        <table class="table-head-box table-center table table-striped checkbox checkbox-primary" id="attachmentTable">
                            <thead>
                              <tr class="bg-primary">
                                  <th>No</th>
                                  <th>Name</th>
                                  <th>Path</th>
                              </tr>
                              </thead>
                              <tbody>
                              <?php $count = 1;?>
                              @foreach ($invoicedatas as $key => $invoicedata)

                                <tr>
                                  <td class="sorting_1">{{ $count }}</td>
                                  <td>{{ $invoicedata->name }}</td>
                                  <td>{{ $invoicedata->invoice_attachment_path }}</td>
                                </tr>
                                <?php $count++;?>
                                @endforeach
                              </tbody>
                              <tfoot>
                              <tr>
                                  <th>No</th>
                                  <th>Name</th>
                                  <th>Path</th>
                              </tr>
                              </tfoot>
                      </table>
                        </div>

                    <?php }?>

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
<script src="<?php echo url('/') . '/cdnjs.cloudflare.com/ajax/libs/jquery/jquery.validate.js' ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.print.min.js"></script>
<script type="text/javascript">
jQuery(document).ready(function(){
$("#myform").validate({
            ignore: ":hidden",
            rules: {

                images: {
                    required:true,
                     accept:"application/pdf,image/png,image/jpeg, image/jpg"

                }
            }
        }); // This is not working and is not validating the form
});


    jQuery('#attachmentTable').DataTable({
        "dom": "<'row mb-2 align-items-center'<'col-auto dataTable-length-tb-0'l><'col'B><'col'f>><'row'<'col-md-12'<'scroll-lg' t>>><'row'<'col-md-3'i><'col-md-6 ml-auto'p>>",
        "processing": true,
        "serverSide": true,
        "deferLoading": <?=$totalcount?>,
        "buttons": [
        {
            extend: 'csv',
            footer: false,
            title: 'Diamond-Invoice-Attachment-List-Data',
            className: "btn btn-primary btn-sm px-3",
            exportOptions: {
                columns: [0,1,2],
                orthogonal: 'export'
            }
        },
        {
            extend: 'excel',
            footer: false,
            title: 'Diamond-Invoice-Attachment-List-Data',
            className: "btn btn-primary btn-sm px-3",
            exportOptions: {
                columns: [0,1,2],
                orthogonal: 'export'
            }
        }
        ],
        "ajax":{
            "url": "<?=URL::to('/') . '/diamond/invoiceattachmentResponse'?>",
            "dataType": "json",
            "type": "POST",
            "data":{ _token: "{{csrf_token()}}"}
            },"columnDefs": [ {
              "targets": [0,2],
              "orderable": false
              }]
        });
</script>
@endsection