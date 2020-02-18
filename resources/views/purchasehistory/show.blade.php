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

                      <table class=" table table-bordered">
                            <tbody>
                          
                            @foreach ($Transaction as $Transactiondetails)
                             <tr class="bg-light text-dark">
                              <td colspan="10"><?php echo'<b>Product Detail</b>';?></td>
                              </tr>
                              <td colspan="5">
                                  <?php 
                                    echo '<b>Vendor Name </b> :<br/>';
                                    echo '<b>Amount Paid </b> : <br/>';
                                    echo '<b>Amount Paid With GST </b> : <br/>';
                                    echo '<b>Transaction By </b> : <br/>';
                                    echo '<b>Transaction At </b> : <br/>';
                                    echo '<b>Due Date </b> : <br/>';
                                    echo '<b>Comment </b> : <br/>';?>  
                              </td>
                              <td colspan="5">
                                  <?php 
                                  echo $Transactiondetails->vendor_name.'<br/>';
                                  echo $Transactiondetails->amount_paid.'<br/>';
                                  echo $Transactiondetails->amount_paid_with_gst.'<br/>';
                                  echo $Transactiondetails->user.'<br/>';
                                  echo $Transactiondetails->transaction_at.'<br/>';
                                  echo $Transactiondetails->due_date.'<br/>';
                                  echo $Transactiondetails->comment.'<br/>';
                                  ?>
                              </td>
                                
                              @endforeach
                              <tr class="bg-light text-dark">
                                <td colspan="10"><?php echo'<b>Diamond Detail</b>';?></td>
                              </tr>
                              <?php
                                $sieve_size = array();
                                $mm_size = array();
                                $diamond_weight = array();
                                $i =1; 
                                foreach ($daimonddata as $key=>$diamonddetails){
                                      $sieve_size[$key] = $diamonddetails->sieve_size;
                                      $mm_size[$key] = $diamonddetails->mm_size;
                                      $diamond_weight[$key] = $diamonddetails->diamond_weight;
                                      $maxVal = max(count($sieve_size),count($mm_size),count($diamond_weight)); 
                                  ?>
                                   <tr class="bg-light text-dark">
                                     <td colspan="10"><?php echo'<b>Diamond </b>'.$i++;?></td>
                                   </tr>
                                      <td colspan="5">
                                        <?php 
                                              echo '<b>Packet ID</b> :<br/>';
                                              echo '<b>Diamond Shape</b> :<br/>';
                                              echo '<b>Diamond Quality</b> :<br/>';
                                              echo '<b>MM Size</b> :<br/>';
                                              echo '<b>Sieve Size</b> :<br/>';
                                              echo '<b>Total Diamond Wt</b> :<br/>';
                                            ?>
                                      <td colspan="5">
                                        <?php 
                                          echo $diamonddetails->packet_id.'<br/>';
                                          echo $diamonddetails->stone_shape.'<br/>';
                                          echo $diamonddetails->diamond_quality.'<br/>';
                                          echo $diamonddetails->mm_size.'<br/>';
                                          echo $diamonddetails->sieve_size.'<br/>';
                                          echo $diamonddetails->diamond_weight.'<br/>';
                                        ?>

                                      </td >
                                <?php } ?>

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