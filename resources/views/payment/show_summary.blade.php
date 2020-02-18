<?php
$user = Auth::user();
?>
@extends('layout.mainlayout')

@section('title', 'Payment Summary')

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
                {{ Breadcrumbs::render('payment.payment_summary') }}
                <!-- /.page-title-right -->
            </div>
            <!-- /.page-title -->
            <!-- =================================== -->
            <!-- Different data widgets ============ -->
            <!-- =================================== -->
              <div class="widget-list">
                <!-- Counters -->
                <div class="row summary-main summary-first">
                    <!-- Counter: Sales -->
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height summary-item">
                        <div class="widget-bg d-table pointer incominghide">
                            <div class="widget-body d-table-cell align-middle medium-counter-font">
                                <div class="widget-counter ">
                                    <h6>{{$payment_form}}<small class="text-inverse"></small></h6>
                                    <h3 class="h1"><span ><?=CommonHelper::covertToCurrency($total_amount)?></span></h3>@if($payment_form == "Incoming")<img src="<?=URL::to('/');?>/assets/images/incoming.png" class="ald">
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
                </div>
                 <div class="row summary-main incoming">
                     <div class="col-md-3 col-sm-6 widget-holder widget-full-height summary-item">
                        <div class="widget-bg d-table">
                            <div class="widget-body d-table-cell align-middle medium-counter-font clearfix">
                                <div class="widget-counter ">
                                    <h6>PAID<small class="text-inverse"></small></h6>
                                    <h3 class="h1"><span><?=CommonHelper::covertToCurrency($paid)?></span></h3><i class="material-icons">credit_card</i>
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
                    </div>
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height summary-item">
                        <div class="widget-bg d-table">
                            <div class="widget-body d-table-cell align-middle medium-counter-font clearfix">
                                <div class="widget-counter ">
                                    <h6>UNPAID<small class="text-inverse"></small></h6>
                                    <h3 class="h1"><span><?=CommonHelper::covertToCurrency($unpaid)?></span></h3><img src="<?=URL::to('/');?>/assets/images/unpaid.png" class="ald">
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
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
          
        $('.incominghide').click(function(){
          
                $('.incoming').show();
                
        });
        $('.outcominghide').click(function(){
          
                $('.incoming').hide();
        });  

         // summary add active class
           
         $('.summary-item').on('click',function(){
            $('.summary-item.active').removeClass('active');
            $(this).addClass('active');
        });

  $( '.summary-item' ).on( 'click', function ()
        {
            $( this ).parents( '.summary-main' ).find( '.summary-item.active' ).removeClass( 'active' );
            if ( $( this ).parents( '.summary-main' ).hasClass( 'summary-first' ) )
            {
                $( '.summary-main.incoming' ).find( '.summary-item.active' ).removeClass( 'active' );
                $( '.summary-main.summary-first' ).find( '.summary-item.active' ).removeClass( 'active' );
               
            }
            $( this ).addClass( 'active' );
        });
</script>

@endsection