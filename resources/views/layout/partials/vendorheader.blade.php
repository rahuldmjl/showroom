<?php
$user = Auth::user();
if (!empty(Auth::user()->avatar) && file_exists('assets/images/' . Auth::user()->avatar)) {
	$user_avatar = URL::to('/') . '/assets/images/' . Auth::user()->avatar;
} else {
	$user_avatar = URL::to('/') . '/assets/images/users.jpeg';
}
?>
<!-- /.navbar -->
    <div class="content-wrapper">
        <!-- SIDEBAR -->
        <aside class="site-sidebar scrollbar-enabled clearfix">
            <!-- User Details -->
            <div class="side-user">
                <a class="col-sm-12 media clearfix" href="javascript:void(0);">
                    <figure class="media-left media-middle user--online thumb-sm mr-r-10 mr-b-0">
                        <img src="<?=$user_avatar?>" class="media-object rounded-circle" />
                    </figure>
                    <div class="media-body hide-menu">
                        <h4 class="media-heading mr-b-5 text-uppercase">{{$vendorName}}</h4><span class="user-type fs-12">Vendor Detail</span>
                    </div>
                </a>
                <div class="clearfix"></div>
            </div>
            <!-- /.side-user -->
            <!-- Sidebar Menu -->
            <nav class="sidebar-nav">
                <ul class="nav in side-menu vendor-menu-container">
                    <li class=" nav-item"><a href="<?=URL::to('vendor/');?>" class="ripple"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">keyboard_arrow_left</i> <span class="hide-menu">Back </span></span></a>
                    </li>
                    <li class="nav-item current-page active"><a href="#vendor_dashboard" id="vendordashboard" onclick="showCustomerSection('vendor_dashboard',this.id)"><span class="color-color-scheme"><i class="material-icons">perm_identity</i> <span class="hide-menu"> Dashboard</span></span></a>
                    </li>
                    <li class="nav-item"><a href="#vendor_detail" id="vendordetail" onclick="showCustomerSection('vendor_detail',this.id)"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">perm_identity</i><span class="hide-menu">Vendor Detail</span></span></a>
                    </li>
                    <li class="menu-item-has-children"><a href="#" class="ripple"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">dvr</i> Diamond Issue &nbsp;</a>
                        <ul class="list-unstyled sub-menu">
                            <li class=""><a href="#diamond-given" id="diamondgiven" onclick="showCustomerSection('diamond-given',this.id)">Given &nbsp;</a>
                            </li>
                            <li class=""><a href="#diamond-return" id="diamondreturn" onclick="showCustomerSection('diamond-return',this.id)">Return &nbsp;</a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-item-has-children"><a href="#" id="ripple"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">list</i><span class="hide-menu">  Gold Issue</span></span></a>
                        <ul class="list-unstyled sub-menu">
                            <li class=""><a href="#gold-given" id="goldgiven" onclick="showCustomerSection('gold-given',this.id)">Given &nbsp;</a>
                            </li>
                            <li class=""><a href="#gold-return" id="goldreturn" onclick="showCustomerSection('gold-return',this.id)">Return &nbsp;</a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-item-has-children"><a href="#" id="ripple"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">check_box</i><span class="hide-menu"> Costing Sheet</span></span></a>
                        <ul class="list-unstyled sub-menu">
                            <li class=""><a href="#costing-accepted" id="accepted" onclick="showCustomerSection('costing-accepted',this.id)">Accepted &nbsp;</a>
                            </li>
                            <li class=""><a href="#costing-rejected" id="gcostingrejected" onclick="showCustomerSection('costing-rejected',this.id)"> Rejected &nbsp;</a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-item-has-children"><a href="#" id="ripple"> <span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">list</i><span class="hide-menu"> Invoice</span></a>
                        <ul class="list-unstyled sub-menu">
                            <li class=""><a href="#paid-invoice" id="paidinvoice" onclick="showCustomerSection('paid-invoice',this.id)">Paid Invoice &nbsp;</a>
                            </li>
                            <li class=""><a href="#unpaid-invoice" id="unpaidinvoice" onclick="showCustomerSection('unpaid-invoice',this.id)"> Unpaid invoice &nbsp;</a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item"><a href="#credit-note" id="creditnote" onclick="showCustomerSection('credit-note',this.id)"> <span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">list</i><span class="hide-menu">  Credit Note</span></a>
                    </li>
                    <li class="nav-item"><a href="#debit-note" id="debitnote" onclick="showCustomerSection('debit-note',this.id)"> <span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">list</i><span class="hide-menu">  Debit note</span></a>
                    </li>
                    <li class="nav-item"><a href="#payment-history" id="paymenthistory" onclick="showCustomerSection('payment-history',this.id)"><span class="@if (\Request::is('/')) color-color-scheme @endif"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">compare_arrows</i><span class="hide-menu"> Payment History</span></span></a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-nav -->
        </aside>
        <!-- /.site-sidebar -->