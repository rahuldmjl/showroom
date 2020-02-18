<?php
$user = Auth::user();
?>
@extends('layout.mainlayout')

@section('title', 'Account Summary')

@section('distinct_head')

<link href="<?=URL::to('/');?>/assets/vendors/weather-icons-master/weather-icons.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/assets/vendors/weather-icons-master/weather-icons-wind.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expand')

@section('content')

   <main class="main-wrapper clearfix">
            <!-- Page Title Area -->
            <div class="row page-title clearfix">
                <!-- /.page-title-left -->
                {{ Breadcrumbs::render('accountpayment.summary') }}
                <!-- /.page-title-right -->
            </div>
            <!-- /.page-title -->
            <!-- =================================== -->
            <!-- Different data widgets ============ -->
            <!-- =================================== -->
              <div class="widget-list">
                 <div class="widget-body clearfix">

                 @if (count($errors) > 0)
                           <div class="alert alert-icon alert-danger border-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                             <i class="material-icons">highlight_off</i>
                               <strong>error</strong>: {{ $errors }}
                          </div>
                @endif
                <!-- Counters -->
                <div class="row summary-main summary-first">
                    <!-- Counter: Sales -->
                    <div class="col-md-3 col-lg-6 col-xl-3 widget-holder widget-full-height summary-item">
                        <div class="widget-bg d-table pointer incominghide">
                            <div class="widget-body d-table-cell align-middle medium-counter-font">
                                <div class="widget-counter ">
                                    <h6>{{$total[0]->payment_form}}<small class="text-inverse"></small></h6>
                                    <h3 class="h1"><span ><?=CommonHelper::covertToCurrency($total[0]->total_amount)?></span></h3>@if($total[0]->payment_form == "Incoming")<img src="<?=URL::to('/');?>/assets/images/incoming.png" class="ald">
                                    @else
                                    <img src="<?=URL::to('/');?>/assets/images/outgoing.png" class="ald">    
                                    @endif
                                    <!-- <h3 class="h1">&dollar;<span class="counter">741</span></h3><i class="material-icons list-icon">add_shopping_cart</i> -->
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
                    </div>
                    <!-- /.widget-holder -->
                    <!-- Counter: Subscriptions -->
                    <div class="col-md-3 col-lg-6 col-xl-3 incoming widget-holder widget-full-height summary-item">
                        <div class="widget-bg  d-table pointer">
                            <div class="widget-body clearfix date d-table-cell align-middle medium-counter-font">
                                <div class="widget-counter ">
                                    <h6>@if($total[0]->account_status == 2) Decline @else Approved @endif<small class="text-inverse"></small></h6>
                                    <h3 class="h1"><span><?=CommonHelper::covertToCurrency($total[0]->total_amount)?></span></h3>@if($total[0]->account_status == 2)  
                                    <i class="material-icons">backspace</i>@else <i class="material-icons">offline_pin</i> @endif
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
                    </div>
                    <div class="col-md-3 col-lg-6 col-xl-3  due  widget-holder widget-full-height summary-item">
                        <div class="widget-bg  d-table pointer">
                            <div class="widget-body clearfix d-table-cell align-middle medium-counter-font">
                                <div class="widget-counter ">
                                    <h6>{{$due}}<small class="text-inverse"></small></h6>
                                    <h3 class="h1"><span><?=CommonHelper::covertToCurrency($total[0]->total_amount)?></span></h3>@if($due == "Over-Due")  
                                    <i class="material-icons">schedule</i>@elseif($due == "Current-Due") <i class="material-icons">track_changes</i>@else <i class="material-icons">access_alarms</i> @endif
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
                    </div>
                    
                </div>
            </div>
           </div>
                  
        </main>
        <!-- /.main-wrappper -->

@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/Counter-Up/1.0.0/jquery.counterup.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>
<script type="text/javascript">
      
        $('.incoming').hide();
          $('.due').hide();
          
        $('.incominghide').click(function(){
          
                $('.incoming').show();
                $('.due').hide();
        });
        $('.date').click(function(){
          
                $('.due').show();
        });  
     
       // summary add active class
           
          
       $( '.summary-item' ).on( 'click', function ()
        {
            $( this ).parents( '.summary-main' ).find( '.summary-item.active' ).removeClass( 'active' );
            if ( $( this ).parents( '.summary-main' ).hasClass( 'summary-first' ) )
            {
                $( '.summary-main.incominghide' ).find( '.summary-item.active' ).removeClass( 'active' );
                $( '.summary-main.due' ).find( '.summary-item.active' ).removeClass( 'active' );
            }
            $( this ).addClass( 'active' );
        });

</script>

@endsection