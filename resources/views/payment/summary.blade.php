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
                                                </span></h3><img src="<?=URL::to('/');?>/assets/images/outgoing.png" class="ald">
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
                                <div class="widget-bg d-table pointer paid">
                                    <div class="widget-body d-table-cell align-middle medium-counter-font">
                                        <div class="widget-counter">
                                        <h6>PAID<small class="text-inverse"></small></h6>
                                         <h3 class="h1"><span >
                            <?=CommonHelper::covertToCurrency($paidin[0]->total_amount)?>
                                                </span></h3><i class="material-icons">credit_card</i>
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
                                <div class="widget-bg d-table pointer  unpaid">
                                    <div class="widget-body d-table-cell align-middle medium-counter-font">
                                        <div class="widget-counter">
                                            <h6>UNPAID <small class="text-inverse"></small></h6>
                                            <h3 class="h1"><span>
                                                <?=CommonHelper::covertToCurrency($unpaidin[0]->total_amount)?>
                                               </span></h3> <img src="<?=URL::to('/');?>/assets/images/unpaid.png" class="ald">
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
                                <div class="widget-bg d-table pointer  paidout">
                                    <div class="widget-body d-table-cell align-middle medium-counter-font">
                                        <div class="widget-counter">
                                        <h6>PAID<small class="text-inverse"></small></h6>
                                          <h3 class="h1"><span >
                                                <?=CommonHelper::covertToCurrency($paidoutg[0]->total_amount)?>
                                                </span></h3><i class="material-icons">credit_card</i>
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
                                <div class="widget-bg d-table pointer  unpaidout">
                                    <div class="widget-body d-table-cell align-middle clearfix medium-counter-font">
                                        <div class="widget-counter">
                                        <h6>UNPAID <small class="text-inverse"></small></h6>
                                          <h3 class="h1"><span>
                                                <?=CommonHelper::covertToCurrency($unpaidoutg[0]->total_amount)?>
                                                </span></h3><img src="<?=URL::to('/');?>/assets/images/unpaid.png" class="ald">
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
                    <div class="col-md-12 widget-holder unpaidheaderout-wrapper hide-chart">
                        <div class="widget-bg border-rad-30">
                            <div class="widget-body clearfix">
                            <div class="unpaidheaderout">   
                                    {!! $unpaidchartout->html() !!}
                            </div>
                            </div>
                            <!-- /.widget-bg -->
                        </div>
                    </div>
                </div>
                <div class="row">        
                    <div class="col-md-12 widget-holder paidheaderout-wrapper hide-chart">
                        <div class="widget-bg border-rad-30">
                            <div class="widget-body clearfix">
                            <div class="paidheaderfor">   
                                    {!! $paidchartout->html() !!}
                            </div>
                            </div>
                            <!-- /.widget-bg -->
                        </div>
                    </div>
                </div> 
                <div class="row">        
                    <div class="col-md-12 widget-holder paidheaderin-wrapper hide-chart">
                        <div class="widget-bg border-rad-30">
                            <div class="widget-body clearfix">
                                <div class="paid">   
                                    {!! $paidchart->html() !!}
                                </div>
                            </div>
                       
                            <!-- /.widget-bg -->
                        </div>
                    </div>
                </div> 
                <div class="row">        
                    <div class="col-md-12 widget-holder  unpaidheaderin-wrapper hide-chart">
                        <div class="widget-bg border-rad-30">
                            <div class="widget-body clearfix">
                                <div class="unpaid">   
                                    {!! $unpaidchart->html() !!}
                                </div>
                            </div>
                            <!-- /.widget-bg -->
                        </div>
                    </div>
                </div> 
                </div>
                {!! Charts::scripts() !!}
             {!! $paidchart->script() !!}
             {!! $unpaidchart->script() !!}
             {!! $paidchartout->script() !!}
             {!! $unpaidchartout->script() !!} 
              </div>
                <!-- multiple charts row end -->  
        </main>
        <!-- /.main-wrappper -->

@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/Counter-Up/1.0.0/jquery.counterup.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>

<script type="text/javascript">
          $('.incoming').hide();
          $('.paidheader').hide();
          $('.unpaidheader').hide();
          $('.outgoing').hide();
          $('.unpaid').parents('.unpaidheaderin-wrapper').addClass('hide-chart');
          $('.paid').parents('.paidheaderin-wrapper').addClass('hide-chart');
          $('.unpaidheaderout').parents('.unpaidheaderout-wrapper').addClass('hide-chart');
          $('.paidheaderfor').parents('.paidheaderout-wrapper').addClass('hide-chart');
         
         
       $('.incominghide').click(function(){
      
            $('.incoming').show();
            $('.outgoing').hide();
            $('.unpaid').parents('.unpaidheaderin-wrapper').addClass('hide-chart');
            $('.paid').parents('.paidheaderin-wrapper').addClass('hide-chart');
            $('.unpaidheaderout').parents('.unpaidheaderout-wrapper').addClass('hide-chart');
            $('.paidheaderfor').parents('.paidheaderout-wrapper').addClass('hide-chart');
           
                    
         });
         $('.outcominghide').click(function(){
      
            $('.incoming').hide();
            $('.outgoing').show();
            $('.unpaid').parents('.unpaidheaderin-wrapper').addClass('hide-chart');
            $('.paid').parents('.paidheaderin-wrapper').addClass('hide-chart');
            $('.unpaidheaderout').parents('.unpaidheaderout-wrapper').addClass('hide-chart');
            $('.paidheaderfor').parents('.paidheaderout-wrapper').addClass('hide-chart');
            

         });  
         $('.paid').click(function(){
                $('.paid').parents('.paidheaderin-wrapper').removeClass('hide-chart');
                $('.unpaid').parents('.unpaidheaderin-wrapper').addClass('hide-chart');
                $('.unpaidheaderout').parents('.unpaidheaderout-wrapper').addClass('hide-chart');
                $('.paidheaderfor').parents('.paidheaderout-wrapper').addClass('hide-chart');
                
         });
         $('.unpaid').click(function(){
              $('.paid').parents('.paidheaderin-wrapper').addClass('hide-chart');
              $('.unpaid').parents('.unpaidheaderin-wrapper').removeClass ('hide-chart');
              $('.unpaidheaderout').parents('.unpaidheaderout-wrapper').addClass('hide-chart');
              $('.paidheaderfor').parents('.paidheaderout-wrapper').addClass('hide-chart');
         });
         $('.paidout').click(function(){
               $('.paidheaderfor').parents('.paidheaderout-wrapper').removeClass('hide-chart');
               $('.unpaidheaderout').parents('.unpaidheaderout-wrapper').addClass('hide-chart');
         });
        $('.unpaidout').click(function(){
                $('.unpaidheaderout').parents('.unpaidheaderout-wrapper').removeClass('hide-chart');
                $('.paidheaderfor').parents('.paidheaderout-wrapper').addClass('hide-chart');
               
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