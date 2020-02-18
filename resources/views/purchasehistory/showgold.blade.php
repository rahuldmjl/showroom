@extends('layout.mainlayout')

@section('title', 'Purchase History Details')

@section('distinct_head')

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('diamond.transactions', $id) }}
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
                  <div class="widget-heading border-b-light-1 py-2 mb-4 clearfix">
                      <h5>{{'Purchase Details'}}</h5>
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

                      <table class="table table-striped table-padding table-bordered">
                            <tbody>
                           @foreach ($Transaction as $Transactiondetails)
                           <?php 
                              if($Transactiondetails->metal_type == 1){
                                $chkmetal = "Gold";
                                $goldtype = "Gold Type";
                              }else{
                                $chkmetal = "Platinum(950)";
                                $goldtype = "";
                              }
                        ?>
                           <tr class="text-dark">
                              <td colspan="10"><?php echo'<b>Product Detail</b>';?></td>
                              </tr>
                              <td colspan="5">
                                  <?php 
                                    echo '<b>Vendor Name </b> :<br/>';
                                    echo '<b>Amount Paid </b> : <br/>';
                                    echo '<b>Invoice Number </b> : <br/>';
                                    echo '<b>Transaction By </b> : <br/>';
                                    echo '<b>Transaction At </b> : <br/>';
                                    echo '<b>Due Date </b> : <br/>';
                                    echo '<b>Comment </b> : <br/>';
                                    echo '<b>Purchased Invoice</b> : <br/>';?>  
                              </td>
                              <td colspan="5">
                                  <?php 
                                  echo $Transactiondetails->vendor_name.'<br/>';
                                  echo $Transactiondetails->amount_paid.'<br/>';
                                  echo $Transactiondetails->invoice_number.'<br/>';
                                  echo $Transactiondetails->user.'<br/>';
                                  echo $Transactiondetails->purchased_at.'<br/>';
                                  echo $Transactiondetails->due_date.'<br/>';
                                  echo $Transactiondetails->comment.'<br/>';?>
                                  <a target="_blank" href="<?=URL::to(config('constants.dir.purchased_invoices').'/'.$Transactiondetails->purchased_invoice)?>"><?=$Transactiondetails->purchased_invoice?></a>
                              </td>
                              <tr class="text-dark">
                                <td colspan="10"><?php echo'<b>Metal Detail</b>';?></td>
                              </tr> 
                              <td colspan="5">
                                  <?php  
                                    echo '<b>Metal Type </b> :<br/>';
                                    echo '<b>Metal Weight </b> :<br/>';
                                   if($Transactiondetails->metal_type == 1){
                                    echo '<b>'.$goldtype.'</b>'.' :<br/>';
                                    }  else{
                                       echo '';
                                    }
                                  ?>
                                  </td>

                                  <td colspan="5">
                                  <?php  
                                    echo $chkmetal.'<br/>';
                                    echo $Transactiondetails->metal_weight.'<br/>';
                                    if($Transactiondetails->metal_type == 1){
                                        echo $Transactiondetails->gold_type.'<br/>';
                                    }  else{
                                       echo '';
                                    }
                                    
                                  ?>

                                  </td>
                              @endforeach

                            </tbody>
                        </table>
                        <a href="{{ URL::previous() }}" class="btn btn-primary">Go Back</a>
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



@endsection

@section('distinct_footer_script')

@endsection