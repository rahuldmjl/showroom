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
                <!-- Counters -->
                <div class="row">
                    <!-- left col start -->
                    <div class="col-12 col-xl-6">
                        <div class="row summary-main summary-first">
                            <!-- Counter: Sales -->
                            <div class="col-md-6 widget-holder widget-full-height summary-item">
                                <div class="widget-bg d-table pointer incominghide">
                                    <div class="widget-body d-table-cell align-middle medium-counter-font">
                                        <div class="widget-counter">
                                            <h6>INCOMING<small class="text-inverse"></small></h6>
                                            <h3 class="h1"><span >
                                              
                                                <?=CommonHelper::covertToCurrency($incoming)?>
                                                  
                                                </span></h3><img src="<?=URL::to('/');?>/assets/images/incoming.png" class="ald">
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
                            <div class="col-md-6 widget-holder widget-full-height summary-item">
                                <div class="widget-bg d-table pointer outcominghide">
                                    <div class="widget-body d-table-cell align-middle medium-counter-font clearfix">
                                        <div class="widget-counter">
                                            <h6>OUTGOING <small class="text-inverse"></small></h6>
                                            <h3 class="h1"><span> 
                                                <?=CommonHelper::covertToCurrency($outgoing)?>
                                                  </span></h3><img src="<?=URL::to('/');?>/assets/images/outgoing.png"  class="ald" >
                                        </div>
                                        <!-- /.widget-counter -->
                                    </div>
                                    <!-- /.widget-body -->
                                </div>
                                <!-- /.widget-bg -->
                            </div>
                        </div>
                        <div class="row summary-main incoming">
                            <div class="col-md-6 widget-holder widget-full-height summary-item">
                                <div class="widget-bg d-table pointer approved">
                                    <div class="widget-body d-table-cell align-middle medium-counter-font">
                                        <div class="widget-counter">
                                            <h6>APPROVED<small class="text-inverse"></small></h6>
                                            <h3 class="h1"><span >
                                                <?=CommonHelper::covertToCurrency($approved)?>
                                                 </span></h3><i class="material-icons">offline_pin</i>
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
                            <div class="col-md-6 widget-holder widget-full-height summary-item">
                                <div class="widget-bg d-table pointer decline">
                                    <div class="widget-body d-table-cell align-middle medium-counter-font">
                                        <div class="widget-counter ">
                                            <h6>DECLINE  <small class="text-inverse"></small></h6>
                                            <h3 class="h1"><span>
                                                <?=CommonHelper::covertToCurrency($decline)?>
                                                 </span></h3><i class="material-icons">backspace</i>
                                        </div>
                                        <!-- /.widget-counter -->
                                    </div>
                                    <!-- /.widget-body -->
                                </div>
                                <!-- /.widget-bg -->
                            </div>
                         </div>
                        <div class="row summary-main outgoing">
                            <div class="col-md-6 widget-holder widget-full-height summary-item">
                                <div class="widget-bg d-table pointer outapproved">
                                    <div class="widget-body d-table-cell align-middle medium-counter-font">
                                        <div class="widget-counter ">
                                            <h6>APPROVED<small class="text-inverse"></small></h6>
                                            <h3 class="h1"><span >
                                                <?=CommonHelper::covertToCurrency($outapproved)?>
                                                </span></h3><i class="material-icons">offline_pin</i>
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
                            <div class="col-md-6 widget-holder widget-full-height summary-item">
                                <div class="widget-bg d-table pointer outdecline">
                                    <div class="widget-body d-table-cell align-middle clearfix medium-counter-font">
                                        <div class="widget-counter  ">
                                            <h6>DECLINE  <small class="text-inverse"></small></h6>
                                            <h3 class="h1"><span>
                                                <?=CommonHelper::covertToCurrency($outdecline)?>
                                                </span></h3><i class="material-icons">backspace</i>
                                        </div>
                                        <!-- /.widget-counter -->
                                    </div>
                                    <!-- /.widget-body -->
                                </div>
                                <!-- /.widget-bg -->
                            </div>
                        </div>
                    </div>
                    <!-- /.widget-holder -->
                    <!-- Counter: Users -->
               <!-- left col end -->
               <!-- right col start -->
               <div class="col-12 col-xl-6">
               <div class="row">        
                    <div class="col-md-12 widget-holder duedateapprovedin-wrapper hide-chart">
                        <div class="widget-bg border-rad-30">
                            <div class="widget-body clearfix">
                                <div class="duedateout">   
                                    {!! $datechart->html() !!}
                                </div>
                            </div>
                            <!-- /.widget-bg -->
                        </div>
                    </div>
                </div>
                <div class="row">        
                    <div class="col-md-12 widget-holder duedatedeclinein-wrapper hide-chart">
                        <div class="widget-bg border-rad-30">
                            <div class="widget-body clearfix">
                                <div class="duedate">   
                                    {!! $datechartdecline->html() !!}
                                </div>
                            </div>
                       
                            <!-- /.widget-bg -->
                        </div>
                    </div>
                </div> 
                <div class="row">        
                    <div class="col-md-12 widget-holder duedateapproveout-wrapper hide-chart">
                        <div class="widget-bg border-rad-30">
                            <div class="widget-body clearfix">
                                <div class="outapprove">   
                                    {!! $datechartapproveout->html() !!}
                                </div>
                            </div>
                       
                            <!-- /.widget-bg -->
                        </div>
                    </div>

                </div> 
                <div class="row">        
                    <div class="col-md-12 widget-holder duedatedeclilneout-wrapper hide-chart">
                        <div class="widget-bg border-rad-30">
                            <div class="widget-body clearfix">
                                <div class="outdecline">   
                                    {!! $datechartdeclineout->html() !!}
                                </div>
                            </div>
                            <!-- /.widget-bg -->
                        </div>
                    </div>
                </div> 
                </div>
              </div>
                <!-- multiple charts row end -->
           {!! Charts::scripts() !!}
              {!! $datechartdecline->script() !!}
           {!! $datechart->script() !!}
           {!! $datechartapproveout->script() !!}
            {!! $datechartdeclineout->script() !!}
        </main>
        <!-- /.main-wrappper -->

@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/Counter-Up/1.0.0/jquery.counterup.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>
<script type="text/javascript">
            $('.incoming').hide();
            $('.outgoing').hide();
            $('.duedate').parents('.duedatedeclinein-wrapper').addClass ('hide-chart');
            $('.duedateout').parents('.duedateapprovedin-wrapper').addClass ('hide-chart');
            $('.outdecline').parents('.duedatedeclilneout-wrapper').addClass('hide-chart');
            $('.outapprove').parents('.duedateapproveout-wrapper').addClass ('hide-chart');      
           
         $('.incominghide').click(function(){
            $('.incoming').show();
            $('.outgoing').hide(); 
            $('.duedate').parents('.duedatedeclinein-wrapper').addClass ('hide-chart');
            $('.duedateout').parents('.duedateapprovedin-wrapper').addClass ('hide-chart');
            $('.outdecline').parents('.duedatedeclilneout-wrapper').addClass('hide-chart');
            $('.outapprove').parents('.duedateapproveout-wrapper').addClass ('hide-chart');      
         });

         $('.outcominghide').click(function(){
            $('.incoming').hide();
            $('.outgoing').show();
            $('.duedate').parents('.duedatedeclinein-wrapper').addClass ('hide-chart');
            $('.duedateout').parents('.duedateapprovedin-wrapper').addClass ('hide-chart');
            $('.outdecline').parents('.duedatedeclilneout-wrapper').addClass('hide-chart');
            $('.outapprove').parents('.duedateapproveout-wrapper').addClass ('hide-chart');      
         }); 

         $('.approved').click(function(){
            $('.duedateout').parents('.duedateapprovedin-wrapper').removeClass ('hide-chart');
            $('.duedate').parents('.duedatedeclinein-wrapper').addClass ('hide-chart');
            $('.outdecline').parents('.duedatedeclilneout-wrapper').addClass('hide-chart');
            $('.outapprove').parents('.duedateapproveout-wrapper').addClass ('hide-chart');              
         });

         $('.decline').click(function(){
            $('.duedate').parents('.duedatedeclinein-wrapper').removeClass ('hide-chart');
            $('.duedateout').parents('.duedateapprovedin-wrapper').addClass ('hide-chart');
            $('.outdecline').parents('.duedatedeclilneout-wrapper').addClass('hide-chart');
            $('.outapprove').parents('.duedateapproveout-wrapper').addClass ('hide-chart');      
         });

         $('.outapproved').click(function(){
            $('.duedate').parents('.duedatedeclinein-wrapper').addClass ('hide-chart');
            $('.duedateout').parents('.duedateapprovedin-wrapper').addClass ('hide-chart');
             $('.outapprove').parents('.duedateapproveout-wrapper').removeClass ('hide-chart');
             $('.outdecline').parents('.duedatedeclilneout-wrapper').addClass('hide-chart');
         });

         $('.outdecline').click(function(){
            $('.duedate').parents('.duedatedeclinein-wrapper').addClass ('hide-chart');
            $('.duedateout').parents('.duedateapprovedin-wrapper').addClass ('hide-chart');
            $('.outapprove').parents('.duedateapproveout-wrapper').addClass ('hide-chart');
            $('.outdecline').parents('.duedatedeclilneout-wrapper').removeClass('hide-chart');
         });
         
         // summary add active class
           
       $( '.summary-item' ).on( 'click', function ()
        {
            $( this ).parents( '.summary-main' ).find( '.summary-item.active' ).removeClass( 'active' );
            if ( $( this ).parents( '.summary-main' ).hasClass( 'summary-first' ) )
            {
                $( '.summary-main.incoming' ).find( '.summary-item.active' ).removeClass( 'active' );
                $( '.summary-main.outgoing' ).find( '.summary-item.active' ).removeClass( 'active' );
            }
            $( this ).addClass( 'active' );
        });
</script>

@endsection