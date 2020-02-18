<?php
$user = Auth::user();
?>
@extends('layout.mainlayout')

@section('title', 'Dashboard')

@section('distinct_head')

<link href="<?=URL::to('/');?>/assets/vendors/weather-icons-master/weather-icons.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/assets/vendors/weather-icons-master/weather-icons-wind.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expand')

@section('content')

   <main class="main-wrapper clearfix">
            <!-- Page Title Area -->
            <div class="row page-title clearfix">
                <!-- /.page-title-left -->
                {{ Breadcrumbs::render('dashboard') }}
                <!-- /.page-title-right -->
            </div>
            <!-- /.page-title -->
            <!-- =================================== -->
            <!-- Different data widgets ============ -->
            <!-- =================================== -->
            <?php
if ($user->hasRole('Super Admin')) {
	?>
            <div class="widget-list">
                <!-- Counters -->
                <div class="row">
                    <!-- Counter: Sales -->
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height" >
                        <a class="widget-bg bg-primary text-inverse" href="<?=URL::to('/users');?>">
                            <div class="widget-body">
                                <div class="widget-counter">
                                    <h6>Total Users <small class="text-inverse">Last year</small></h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_users']?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
                                    <!-- <h3 class="h1">&dollar;<span class="counter">741</span></h3><i class="material-icons list-icon">add_shopping_cart</i> -->
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </a>
                        <!-- /.widget-bg -->
                    </div>
                    <!-- /.widget-holder -->
                    <!-- Counter: Subscriptions -->
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                        <a class="widget-bg bg-color-scheme text-inverse" href="<?=URL::to('/roles');?>">
                            <div class="widget-body clearfix">
                                <div class="widget-counter">
                                    <h6>Total Roles <small class="text-inverse">Last year</small></h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_roles']?></span></h3><i class="material-icons list-icon">event_available</i>
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </a>
                        <!-- /.widget-bg -->
                    </div>
                    <!-- /.widget-holder -->
                    <!-- Counter: Users -->
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                        <a class="widget-bg" href="<?=URL::to('/customers');?>">
                            <div class="widget-body clearfix">
                                <div class="widget-counter">
                                    <h6>Total Customers</h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_customers']?></span></h3><i class="material-icons list-icon">public</i>
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </a>
                        <!-- /.widget-bg -->
                    </div>
                    <!-- /.widget-holder -->
                    <!-- Counter: Pageviews -->
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                        <a class="widget-bg" href="<?=URL::to('/showroom/orderhistory');?>">
                            <div class="widget-body clearfix">
                                <div class="widget-counter">
                                    <h6>Total Orders</h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_orders']?></span></h3><i class="material-icons list-icon">show_chart</i>
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </a>
                        <!-- /.widget-bg -->
                    </div>
                    <!-- /.widget-holder -->

                    <div class="col-md-6 widget-holder">
                        <div class="widget-bg">
                            <div class="widget-body clearfix">
                              <h5 class="box-title border-b-light-1 mb-4 pb-1">Diamond - Purchase & Issue(Last 30 Days)</h5>
                                <canvas id="chartJsLine" height="150"></canvas>
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
                    </div>
                    <div class="col-md-6 widget-holder">
                        <div class="widget-bg">
                            <div class="widget-body clearfix">
                              <h5 class="box-title border-b-light-1 mb-4 pb-1">Diamond - Purchase & Issue(Last 30 Days)</h5>
                                <canvas id="chartJsBar" height="150"></canvas>
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
                    </div>
                </div>
                <!-- /.row -->

            </div>
            <?php
} elseif ($user->hasRole('QC')) {
	?>
            <div class="widget-list">
                <!-- Counters -->
                <div class="row">
                    <!-- Counter: Sales -->
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                        <div class="widget-bg bg-primary text-inverse">
                            <div class="widget-body">
                                <div class="widget-counter">
                                    <h6>Costings Checked<small class="text-inverse">Last year</small></h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_costing_sheet']?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
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
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                        <div class="widget-bg bg-color-scheme text-inverse">
                            <div class="widget-body clearfix">
                                <div class="widget-counter">
                                    <h6>Success Products <small class="text-inverse">Last year</small></h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_costing_product']?></span></h3><i class="material-icons list-icon">event_available</i>
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
                    </div>
                    <!-- /.widget-holder -->
                </div>
                <!-- /.row -->




            </div>
            <?php
} elseif ($user->hasRole('Gold Manager') || $user->hasRole('Diamond Manager')) {

    if ($user->hasRole('Gold Manager')) {
	?>
            <div class="widget-list">
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="text-center">Gold Statistics</h5>
                    </div>
                </div>
                <!-- Counters -->
                <div class="row">
                    <!-- Counter: Sales -->
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                        <div class="widget-bg bg-primary text-inverse">
                            <div class="widget-body">
                                <div class="widget-counter">
                                    <h6>Gold Purchased<small class="text-inverse">This year (In gms)</small></h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_gold_purchased']?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
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
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                        <div class="widget-bg bg-color-scheme text-inverse">
                            <div class="widget-body clearfix">
                                <div class="widget-counter">
                                    <h6>In Stock Gold <small class="text-inverse">This year (In gms)</small></h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_gold_instock']?></span></h3><i class="material-icons list-icon">event_available</i>
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
                    </div>
                    <!-- /.widget-holder -->
                    <!-- Counter: Subscriptions -->
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                        <div class="widget-bg bg-color-scheme text-inverse">
                            <div class="widget-body clearfix">
                                <div class="widget-counter">
                                    <h6>Approved Stock<small class="text-inverse">This year (In gms)</small></h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_gold_approved']?></span></h3><i class="material-icons list-icon">event_available</i>
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
                    </div>
                    <!-- /.widget-holder -->
                    <!-- Counter: Subscriptions -->
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                        <div class="widget-bg bg-color-scheme text-inverse">
                            <div class="widget-body clearfix">
                                <div class="widget-counter">
                                    <h6>Total Loss<small class="text-inverse">This year (In gms)</small></h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_gold_loss']?></span></h3><i class="material-icons list-icon">event_available</i>
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
                    </div>
                    <!-- /.widget-holder -->
                </div><?php
            }

            if ($user->hasRole('Diamond Manager')) {
    ?>
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="text-center">Diamond Statistics</h5>
                    </div>
                </div>
                <div class="row">
                    <!-- Counter: Sales -->
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                        <div class="widget-bg bg-primary text-inverse">
                            <div class="widget-body">
                                <div class="widget-counter">
                                    <h6>Diamond Purchased<small class="text-inverse">This year (In cts)</small></h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_diamond_purchased']?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
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
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                        <div class="widget-bg bg-color-scheme text-inverse">
                            <div class="widget-body clearfix">
                                <div class="widget-counter">
                                    <h6>In Stock Diamonds <small class="text-inverse">This year (In cts)</small></h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_diamond_instock']?></span></h3><i class="material-icons list-icon">event_available</i>
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
                    </div>
                    <!-- /.widget-holder -->
                    <!-- Counter: Subscriptions -->
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                        <div class="widget-bg bg-color-scheme text-inverse">
                            <div class="widget-body clearfix">
                                <div class="widget-counter">
                                    <h6>Approved Stock<small class="text-inverse">This year (In cts)</small></h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_diamond_approved']?></span></h3><i class="material-icons list-icon">event_available</i>
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
                    </div>
                    <!-- /.widget-holder -->
                    <!-- Counter: Subscriptions -->
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                        <div class="widget-bg bg-color-scheme text-inverse">
                            <div class="widget-body clearfix">
                                <div class="widget-counter">
                                    <h6>Total Loss<small class="text-inverse">This year (In cts)</small></h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_diamond_loss']?></span></h3><i class="material-icons list-icon">event_available</i>
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
                    </div>
                    <!-- /.widget-holder -->
                </div>
                <!-- /.row -->




            </div>
            <?php
        }
} elseif ($user->hasRole('Customer Manager')) {
    ?>
            <div class="widget-list">
                <div class="row">
                    <div class="col-md-12">
                        
                    </div>
                </div>
                <!-- Counters -->
                <div class="row">
                    <!-- Counter: Sales -->
                    <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                        <div class="widget-bg bg-primary text-inverse">
                            <div class="widget-body">
                                <div class="widget-counter">
                                    <h6>Total Customers<small class="text-inverse">This year</small></h6>
                                    <h3 class="h1"><span class="counter"><?=$nav_counters['total_customers_count']?></span></h3><i class="material-icons list-icon">group</i>
                                </div>
                                <!-- /.widget-counter -->
                            </div>
                            <!-- /.widget-body -->
                        </div>
                        <!-- /.widget-bg -->
                    </div>
                    <!-- /.widget-holder -->
                </div>
            </div>
            <?php
}
//echo "<pre>";print_r($nav_counters['purchase']);exit;
?>
 
            <!-- /.widget-list -->
        </main>
        <!-- /.main-wrappper -->

@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/Counter-Up/1.0.0/jquery.counterup.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/raphael/2.2.7/raphael.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.bundle.min.js"></script>
<script type="text/javascript">
      var ctx1 = document.getElementById("chartJsLine").getContext("2d");
      var data1 = {
        labels: <?php echo $nav_counters['lable']; ?>,
        datasets: [
          {
            label: "Purchase",
            backgroundColor: "rgba(88, 103, 195, 0.4)",
            borderColor: "rgba(88, 103, 195, 0.4)",
            pointStrokeColor: "#fff",
            pointBorderColor: "#fff",
            pointHoverBackgroundColor: "#fff",
            pointHoverBorderColor: "rgba(88, 103, 195, 0.4)",
            data: <?php echo $nav_counters['purchase']; ?>
          },
          {
            label: "Issue",
            backgroundColor: "rgba(255, 128, 111, 0.4)",
            borderColor: "rgba(255, 128, 111, 0.4)",
            pointStrokeColor: "#fff",
            pointBorderColor: "#fff",
            pointHoverBackgroundColor: "#fff",
            pointHoverBorderColor: "rgba(255, 128, 111, 0.4)",
            data: <?php echo $nav_counters['issue']; ?>,
          }
        ]
      };

      var chartJsLine = new Chart(ctx1, {
        type: "line",
        data: data1,
        responsive: true,
        options: {
          legend: {
            display: false
          },
          tooltips: {
            mode: 'index',
            intersect: false,
            titleFontColor: "#000",
            titleMarginBottom: 10,
            backgroundColor: "rgba(255,255,255,.9)",
            bodyFontColor: "#000",
            borderColor: "#e9e9e9",
            bodySpacing: 10,
            borderWidth: 3,
            xPadding: 10,
            yPadding: 10,
          },
          scales: {
            xAxes: [{
              gridLines: {
                display:false
              }
            }],
            yAxes: [{
              gridLines: {
                display:false
              }
            }]
          }
        }
});
     

var ctx2 = document.getElementById("chartJsBar").getContext("2d");
var data2 = {
labels: <?php echo $nav_counters['diamond_lable']; ?>,
datasets: [
    {
    label: "Purchase",
    backgroundColor: "#5867c3",
    strokeColor: "#5867c3",
    data: <?php echo $nav_counters['diamond_purchase']; ?>
    },
    {
    label: "Issue",
    backgroundColor: "#00cedc",
    strokeColor: "#00cedc",
    data: <?php echo $nav_counters['diamond_issue']; ?>
    }
]
};

var chartJsBar = new Chart(ctx2, {
type: "bar",
data: data2,
options: {
    legend: {
    display: false
    },
    tooltips: {
    mode: 'index',
    intersect: false,
    titleFontColor: "#000",
    titleMarginBottom: 10,
    backgroundColor: "rgba(255,255,255,.9)",
    bodyFontColor: "#000",
    borderColor: "#e9e9e9",
    bodySpacing: 10,
    borderWidth: 3,
    xPadding: 10,
    yPadding: 10,
    },
    scales: {
    xAxes: [{
        gridLines: {
        display:false
        }
    }],
    yAxes: [{
        gridLines: {
        display:false
        }
    }]
    }
},
responsive: true
});

</script>
@endsection