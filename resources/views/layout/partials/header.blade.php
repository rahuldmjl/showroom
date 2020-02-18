<?php
$user = Auth::user();
if (!empty(Auth::user()->avatar) && file_exists('assets/images/' . Auth::user()->avatar)) {
	$user_avatar = URL::to('/') . '/assets/images/' . Auth::user()->avatar;
} else {
	$user_avatar = URL::to('/') . '/assets/images/users.jpeg';
}
DB::setTablePrefix('dml_');
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
                        <h4 class="media-heading mr-b-5 text-uppercase">{{$user->name}}</h4><span class="user-type fs-12">My Account <i class="fa fa-caret-down"></i></span>
                    </div>
                </a>
                <div class="clearfix"></div>
                <ul class="nav in side-menu">

                    <li><a href="{{url('profile')}}"><i class="list-icon material-icons">person</i> Edit Profile</a>
                    </li>
                    <li><a href="{{url('changepassword')}}"><i  class="list-icon material-icons">lock</i> Change Password</a>
                    </li>
                    <li><a href="<?=URL::to('/');?>/logout" onclick="event.preventDefault();
                                                     document.getElementById('logout-form-sidebar').submit();"><i class="list-icon material-icons">settings_power</i> Logout</a>
                        <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                    </li>

                </ul>
            </div>
            <!-- /.side-user -->
            <!-- Sidebar Menu -->
            <nav class="sidebar-nav">
                <ul class="nav in side-menu">
                    <li class="@if (\Request::is('/') || \Request::is('/')) current-page @endif"><a href="<?=URL::to('/');?>" class="ripple"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="list-icon material-icons">network_check</i> <span class="hide-menu">Dashboard </span></span></a>
                    </li>
            <?php
if ($user->hasRole('Super Admin')) {
	?>
                    <li class="@if (\Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*')) current-page @endif menu-item-has-children "><a href="javascript:void(0);" class="ripple"><span class="@if (\Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*')) color-color-scheme @endif"><i class="list-icon material-icons">people</i> <span class="hide-menu">Users</span></span></a>
                        <ul class="list-unstyled sub-menu @if ( \Request::is('users*') || \Request::is('roles*') || \Request::is('permissions*') ) in @endif">
                            <li class="@if(\Request::is('users*')) active @endif"><a href="<?=URL::to('/users');?>">Users &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_users']?></span></a>
                            </li>
                            <li class="@if(\Request::is('roles*')) active @endif"><a href="<?=URL::to('/roles');?>">Roles &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_roles']?></span></a>
                            </li>
                            <li class="@if(\Request::is('permissions*')) active @endif"><a href="<?=URL::to('/permissions');?>">Permissions &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_permissions']?></span></a>
                            </li>
                        </ul>
                    </li>
                    <li class="@if (\Request::is('vendor*') || \Request::is('managecharges*') || \Request::is('metalrates*') ) current-page @endif menu-item-has-children "><a href="javascript:void(0);" class="ripple"><span class="@if (\Request::is('vendor*') || \Request::is('managecharges*') || \Request::is('metalrates*')) color-color-scheme @endif"><i class="list-icon material-icons">people_outline</i> <span class="hide-menu">Vendors</span></span></a>
                        <ul class="list-unstyled sub-menu @if (\Request::is('vendor*') || \Request::is('managecharges*') || \Request::is('metalrates/*') || \Request::is('metalrates*')) in @endif">
                            <li class="@if (\Request::is('vendor*')|| \Request::is('managecharges*') || \Request::is('metalrates*') || \Request::is('metalrates/{id}/edit')) active @endif"><a href="<?=URL::to('/');?>/vendor">Vendors &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_vendor']?></span></a>
                            </li>
                        </ul>
                    </li>
                    <li class="@if (\Request::is('customers*')) current-page @endif menu-item-has-children"><a href="<?=URL::to('/customers');?>" class="ripple"><span class="@if (\Request::is('customers*')) color-color-scheme @endif"><i class="list-icon material-icons">people_outline</i> <span class="hide-menu">Customers </span></span></a>
                        <ul class="list-unstyled sub-menu @if (\Request::is('customers*')) in @endif">
                            <li class="@if (\Request::is('customers')) active @endif"><a href="<?=URL::to('/customers');?>">Customers &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_customers']?></span></a></a>
                            </li>
                        </ul>
                    </li>
                    <li class="@if (\Request::is('showroom') || \Request::is('showroom/orderhistory')) current-page active @endif menu-item-has-children"><a href="<?=URL::to('/showroom');?>" class="ripple"><span class="@if (\Request::is('showroom') || \Request::is('showroom/orderhistory')) color-color-scheme @endif"><i class="list-icon material-icons">library_books</i> <span class="hide-menu">Orders </span></span></a>
                        <ul class="list-unstyled sub-menu @if (\Request::is('showroom') || \Request::is('showroom/orderhistory')) in @endif">
                            <li class="@if (\Request::is('showroom')) active @endif"><a href="<?=URL::to('/');?>/showroom">Products &nbsp;</a>
                            </li>
                            <li class="@if (\Request::is('showroom/orderhistory*') || \Request::is('showroom/orderview*')) active @endif"><a href="<?=URL::to('showroom/orderhistory');?>">Order History</a>
                            </li>
                        </ul>
                    </li>
                    <li class="@if (\Request::is('inventory/generatequotation') || \Request::is('showroom/showroominventory') || \Request::is('inventory/index') || \Request::is('inventory/stocktally') || \Request::is('showroom/approvalinventory') || \Request::is('showroom/soldinventory') || \Request::is('showroom/allstock') || \Request::is('inventory/memolist') || \Request::is('inventory/invoicelist') || \Request::is('inventory/returnmemolist') || \Request::is('showroom/salesreturnlist') || \Request::is('inventory/quotationlist')||  \Request::is('showroom/qrcodescanning') || \Request::is('inventory/exhibitionlist') || \Request::is('inventory/viewexhibition') || \Request::is('showroom/pendingstock') || \Request::is('virtualboxmanager/create') || \Request::is('virtualboxmanager/vbboxlist')  || \Request::is('virtualboxmanager/moveproducts')) current-page active @endif menu-item-has-children"><a href="<?=URL::to('showroom/showroominventory');?>" class="ripple"><span class="@if (\Request::is('inventory/generatequotation') || \Request::is('showroom/showroominventory') || \Request::is('inventory/index') || \Request::is('inventory/stocktally') || \Request::is('showroom/approvalinventory') || \Request::is('showroom/soldinventory') || \Request::is('showroom/allstock') || \Request::is('inventory/memolist') || \Request::is('inventory/invoicelist') || \Request::is('inventory/returnmemolist') || \Request::is('showroom/salesreturnlist') || \Request::is('inventory/quotationlist') ||  \Request::is('showroom/qrcodescanning') || \Request::is('inventory/exhibitionlist') || \Request::is('inventory/viewexhibition') || \Request::is('showroom/pendingstock') || \Request::is('virtualboxmanager/create') || \Request::is('virtualboxmanager/vbboxlist')  || \Request::is('virtualboxmanager/moveproducts')) color-color-scheme @endif"><i class="list-icon material-icons">queue_play_next</i><span class="hide-menu"> Showroom Inventory </span></span></a>
                        <ul class="list-unstyled sub-menu @if (\Request::is('inventory/generatequotation') || \Request::is('showroom/showroominventory') || \Request::is('inventory/index')  || \Request::is('inventory/stocktally') || \Request::is('inventory/memolist') || \Request::is('inventory/invoicelist') || \Request::is('inventory/returnmemolist') || \Request::is('inventory/quotationlist') || \Request::is('inventory/getcanceledinvoice') || \Request::is('inventory/getcompletedinvoice') ||  \Request::is('showroom/qrcodescanning') || \Request::is('showroom/pendingstock')) in @endif">
                            <li class="@if (\Request::is('inventory/generatequotation') || \Request::is('showroom/showroominventory') || \Request::is('inventory/index')  || \Request::is('inventory/stocktally') || \Request::is('showroom/approvalinventory') || \Request::is('showroom/soldinventory') || \Request::is('showroom/allstock') || \Request::is('showroom/pendingstock')) current-page active @endif menu-item-has-children"><a href="<?=URL::to('inventory/index');?>" class="ripple"><span class="@if (\Request::is('showroom/showroominventory') || \Request::is('inventory/index')  || \Request::is('inventory/stocktally') || \Request::is('showroom/approvalinventory') || \Request::is('showroom/soldinventory') || \Request::is('showroom/allstock') || \Request::is('showroom/pendingstock')) color-color-scheme @endif"><span class="hide-menu">Inventory </span></span></a>
                                <ul class="list-unstyled sub-menu @if (\Request::is('inventory/generatequotation') || \Request::is('showroom/showroominventory') || \Request::is('inventory/index')  || \Request::is('inventory/stocktally') || \Request::is('showroom/approvalinventory') || \Request::is('showroom/soldinventory') || \Request::is('showroom/allstock') || \Request::is('showroom/pendingstock')) in @endif">
                                    <li class="@if (\Request::is('inventory/stocktally')) active @endif"><a href="<?=URL::to('inventory/stocktally');?>">Manage Inventory &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('inventory/generatequotation') || \Request::is('showroom/showroominventory')) active @endif"><a href="<?=URL::to('showroom/showroominventory');?>">Showroom Stock &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('showroom/approvalinventory')) active @endif"><a href="<?=URL::to('showroom/approvalinventory');?>">Approval Stock &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('showroom/pendingstock')) active @endif"><a href="<?=URL::to('showroom/pendingstock');?>">Pending Stock &nbsp;</a>
                                    </li>
									<li class="@if (\Request::is('showroom/soldinventory')) active @endif"><a href="<?=URL::to('showroom/soldinventory');?>">Sold Stock &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('showroom/allstock')) active @endif"><a href="<?=URL::to('showroom/allstock');?>">All Stock &nbsp;</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="@if (\Request::is('inventory/memolist') || \Request::is('inventory/invoicelist') || \Request::is('inventory/returnmemolist') || \Request::is('showroom/salesreturnlist') || \Request::is('inventory/quotationlist') || \Request::is('inventory/exhibitionlist')) current-page active @endif menu-item-has-children"><a href="<?=URL::to('inventory/index');?>" class="ripple"><span class="@if (\Request::is('inventory/memolist') || \Request::is('inventory/invoicelist') || \Request::is('inventory/returnmemolist') || \Request::is('showroom/salesreturnlist') || \Request::is('inventory/quotationlist') || \Request::is('inventory/exhibitionlist')) color-color-scheme @endif"><span class="hide-menu">Sales/Approval List </span></span></a>
                                <ul class="list-unstyled sub-menu @if (\Request::is('inventory/memolist') || \Request::is('inventory/invoicelist') || \Request::is('inventory/returnmemolist') || \Request::is('showroom/salesreturnlist') || \Request::is('inventory/quotationlist') || \Request::is('inventory/exhibitionlist')) in @endif">
                                    <li class="@if (\Request::is('inventory/memolist')) active @endif"><a href="<?=URL::to('inventory/memolist');?>">Memo List &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('inventory/invoicelist') || \Request::is('inventory/getcanceledinvoice') || \Request::is('inventory/getcompletedinvoice')) active @endif"><a href="<?=URL::to('inventory/invoicelist');?>">Invoice List &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('inventory/returnmemolist')) active @endif"><a href="<?=URL::to('inventory/returnmemolist');?>">Return Memo List &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('showroom/salesreturnlist')) active @endif"><a href="<?=URL::to('showroom/salesreturnlist');?>">Sales Return List&nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('inventory/quotationlist')) active @endif"><a href="<?=URL::to('inventory/quotationlist');?>">Quotation List&nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('inventory/exhibitionlist')) active @endif"><a href="<?=URL::to('inventory/exhibitionlist');?>">Exhibition List&nbsp;</a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <li class="@if (\Request::is('showroom/qrcodescanning')) active @endif"><a href="<?=URL::to('showroom/qrcodescanning');?>">Qrcode Scanning&nbsp;</a>
                            </li>
                            <li class="@if (\Request::is('virtualboxmanager/create') || \Request::is('virtualboxmanager/moveproducts') || \Request::is('virtualboxmanager/vbboxlist')) current-page active @endif menu-item-has-children"><a href="<?=URL::to('virtualboxmanager/create');?>" class="ripple"><span class="@if (\Request::is('virtualboxmanager/create') || \Request::is('virtualboxmanager/moveproducts') || \Request::is('virtualboxmanager/vbboxlist')) color-color-scheme @endif"><span class="hide-menu">Virtual Box Manager </span></span></a>
                                <ul class="list-unstyled sub-menu @if (\Request::is('virtualboxmanager/create') ) @endif">
                                    <li class="@if (\Request::is('virtualboxmanager/create')) active @endif"><a href="<?=URL::to('virtualboxmanager/create');?>">Create Virtual Box&nbsp;</a></li>
                                    <li class="@if (\Request::is('virtualboxmanager/moveproducts')) active @endif"><a href="<?=URL::to('virtualboxmanager/moveproducts');?>">Move Products&nbsp;</a></li>
                                    <li class="@if (\Request::is('virtualboxmanager/vbboxlist')) active @endif"><a href="<?=URL::to('virtualboxmanager/vbboxlist');?>">Virtual Box List&nbsp;</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="@if (\Request::is('gold-inventory*') ||  \Request::is('purchase-history*') || \Request::is('diamond-inventory*') || \Request::is('transaction-type*') ||  \Request::is('diamond/*') || \Request::is('diamondraw') || \Request::is('diamondraw/cvd-list')|| \Request::is('diamondraw/create')  || \Request::is('diamondraw/assortinglist*') ||  \Request::is('diamondraw/issue_voucher_list')) current-page @endif menu-item-has-children ">
                        <a href="javascript:void(0);" class="ripple">
                            <span class="@if (\Request::is('gold-inventory*') || \Request::is('diamond-inventory*') || \Request::is('transaction-type*') || \Request::is('purchase-history*')  ||  \Request::is('diamond/*') ||\Request::is('diamondraw') || \Request::is('diamondraw/create') ||\Request::is('diamondraw/cvd-list')|| \Request::is('diamondraw/assortinglist*')||
                             \Request::is('diamondraw/issue_voucher_list')) color-color-scheme @endif">
                                <i class="list-icon material-icons">view_agenda</i>
                                    <span class="hide-menu">Gold & Diamond</span>
                            </span>
                        </a>
                            <ul class="list-unstyled sub-menu @if ( \Request::is('gold-inventory*') || \Request::is('transaction-type*') ||  \Request::is('purchase-history*') || \Request::is('diamond-inventory*') ||  \Request::is('diamond/*') ||\Request::is('diamondraw')|| \Request::is('diamondraw/cvd-list') || \Request::is('diamondraw/create') || \Request::is('diamondraw/assortinglist*') ||
                            \Request::is('diamondraw/issue_voucher_list')) in @endif">
                                <li class="@if(\Request::is('transaction-type*')) active @endif">
                                <a href="<?=URL::to('/');?>/transaction-type">Transaction Type &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_transaction_types']?></span></a>

                                </li>
                                <?php
if ($user->hasPermissionTo('metals-list') || $user->hasRole('Super Admin')) {
		?>
                                <li class="@if(\Request::is('gold-inventory') || \Request::is('gold-inventory/edit-transaction/*') ) active @endif">
                                    <a href="<?=URL::to('/');?>/gold-inventory">Gold Inventory</a>
                                </li>

                                 <li class="@if(\Request::is('gold-inventory/goldissue')) active @endif">
                                    <a href="<?=URL::to('/');?>/gold-inventory/goldissue">Gold Issue</a>
                                </li>
                                <?php }?>

                                <li class="@if (\Request::is('diamondraw') || \Request::is('diamondraw/cvd-list') ||\Request::is('diamondraw/create') ||\Request::is('diamondraw/assortinglist*')) current-page active @endif menu-item-has-children ">
                                    <a href="<?=URL::to('/diamondraw');?>" class="ripple">
                                        <span class="@if (\Request::is('diamondraw') || \Request::is('diamondraw/cvd-list') || \Request::is('diamondraw/assortinglist*') ||\Request::is('diamondraw/create') || \Request::is('diamond-inventory/create/*')) color-color-scheme @endif">Raw Diamond</span>
                                    </a>
                                    <ul class="list-unstyled sub-menu @if (\Request::is('diamondraw')|| \Request::is('diamondraw/cvd-list') || \Request::is('diamondraw/assortinglist*') || \Request::is('diamond-inventory/create/*')) in @endif">
                                        <li class="@if(\Request::is('diamondraw')|| \Request::is('diamondraw/create') )active @endif">
                                            <a href="<?=URL::to('/');?>/diamondraw">Raw Diamond List</a>
                                        </li>
                                        <li class="@if(\Request::is('diamondraw/cvd-list'))active @endif">
                                            <a href="<?=URL::to('/');?>/diamondraw/cvd-list">Raw CVD List</a>
                                        </li>
                                        <li class="@if(\Request::is('diamondraw/assortinglist*') || \Request::is('diamond-inventory/create/*'))active @endif">
                                           <a href="<?=URL::to('/');?>/diamondraw/assortinglist">Raw Assorting List</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="@if(\Request::is('diamond-inventory*') || \Request::is('diamond-inventory/creatediamonds') )active @endif">
                                <a href="<?=URL::to('/');?>/diamond-inventory">Diamond Inventory
                                <span class="badge badge-border badge-border-inverted bg-primary" style="display: none;">
                                    <?=$nav_counters['total_diamond']?>
                                </span>
                                </a>
                                </li>

                                <li class="@if(\Request::is('diamond/diamondissue')) active @endif">
                                    <a href="<?=URL::to('/');?>/diamond/diamondissue">Diamond Issue</a>
                                </li>

                                <li class="@if(\Request::is('diamond/generatediamondinvoice'))active @endif">
                                <a href="<?=URL::to('/');?>/diamond/generatediamondinvoice">Diamond Invoice</a>
                                </li>

                                <li class="@if(\Request::is('diamond/diamondinvoice'))active @endif">
                                <a href="<?=URL::to('/');?>/diamond/diamondinvoice">Diamond Invoices List</a>
                                </li>


                                <li class="@if(\Request::is('diamond/invoiceattachment')) active @endif">
                                    <a href="<?=URL::to('/');?>/diamond/invoiceattachment">Invoice Attachment</a>
                                </li>

                                <li class="@if(\Request::is('diamond/importexcel')) active @endif">
                                    <a href="<?=URL::to('/');?>/diamond/importexcel">Import Excel</a>
                                </li>


                                <li class="@if(\Request::is('purchase-history') || \Request::is('purchase-history-diamond/*') || \Request::is('purchase-history-gold/*')) active @endif">
                                    <a href="<?=URL::to('/');?>/purchase-history">Purchase History
                                    <span class="badge badge-border badge-border-inverted bg-primary">
                                        <?=$nav_counters['total_purchase']?>
                                    </span>
                                    </a>
                                </li>
                                <li class="@if(\Request::is('diamondraw/issue_voucher_list') ||\Request::is('diamond/edit_issue_voucher')) active  @endif">
                                    <a href="<?=URL::to('/');?>/diamondraw/issue_voucher_list">Issue Voucher List</a>
                                </li>

                                <li class="@if(\Request::is('diamond/diamond_statistics_by_mm')) active  @endif">
                                    <a href="<?=URL::to('/');?>/diamond/diamond_statistics_by_mm">Diamond Statistics By MM</a>
                                </li>
                            </ul>
                    </li>

                   <!--  <li class="@if (\Request::is('costing/*') || \Request::is('showroom/product_list*') || \Request::is('productupload/*') ) current-page @endif menu-item-has-children"><a href="<?=URL::to('/costing/create');?>" class="ripple"><span class="@if (\Request::is('costing/*') || \Request::is('productupload/*') || \Request::is('showroom/product_list*') ) color-color-scheme @endif"><i class="list-icon material-icons">playlist_add_check</i> <span class="hide-menu">QC </span></span></a>
                        <ul class="list-unstyled sub-menu @if (\Request::is('costing/*') || \Request::is('productupload/*') || \Request::is('showroom/product_list*')  ) in @endif">

                            <li class="@if (\Request::is('productupload/*')) active @endif"><a href="<?=URL::to('/productupload/create');?>">Product Upload &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>

                             <li class="@if (\Request::is('costing/product_list*')) active @endif"><a href="<?=URL::to('/costing/product_list');?>">Product List &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>


                            <li class="@if (\Request::is('costing/create*')) active @endif"><a href="<?=URL::to('/costing/create');?>">Costing Sheet &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/costinglist*')) active @endif"><a href="<?=URL::to('/costing/costinglist');?>">Costing Product &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qccostingproductcount"><?=$nav_counters['total_costing_product']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/costinglog*')) active @endif"><a href="<?=URL::to('/costing/costinglog');?>">Costing log &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_costing_sheet']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/qcaccept*')) active @endif"><a href="<?=URL::to('/costing/qcaccept');?>">Accepted product &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcacceptcount"><?=$nav_counters['total_costing_qc_accept']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/qcreject*')) active @endif"><a href="<?=URL::to('/costing/qcreject');?>">Rejected product &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcrejectcount"><?=$nav_counters['total_costing_qc_reject']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/IGIlist*')) active @endif"><a href="<?=URL::to('/costing/IGIlist');?>">IGI &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcigicount"><?=$nav_counters['total_costing_qc_igi']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/qcrequestinvoice*')) active @endif"><a href="<?=URL::to('/costing/qcrequestinvoice');?>">Requested invoice &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcrequestinvoice"><?=$nav_counters['total_costing_qc_request_invoice']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/qcreturnmemo*')) active @endif"><a href="<?=URL::to('/costing/qcreturnmemo');?>">Retured memo &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcreturnmemo"><?=$nav_counters['total_costing_qc_return_memo']?></span></a>
                            </li>
                            <li class=""><a href="#">Post IGI upload costing &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Return product &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Photoshoot &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Tagging &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Summery &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Listing IGI Sheet&nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Repairing &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Listing of Orders &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Purchase Detail &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                        </ul>
                    </li> -->

                     <li class="@if (\Request::is('costing/*') || \Request::is('showroom/product_list*') || \Request::is('productupload/*') ) current-page @endif menu-item-has-children"><a href="<?=URL::to('/costing/create');?>" class="ripple"><span class="@if (\Request::is('costing/*') || \Request::is('productupload/*') || \Request::is('showroom/product_list*') ) color-color-scheme @endif"><i class="list-icon material-icons">playlist_add_check</i> <span class="hide-menu">QC </span></span></a>
                        <ul class="list-unstyled sub-menu @if (\Request::is('costing/*') || \Request::is('productupload/*') || \Request::is('showroom/product_list*')  ) in @endif">

                            <li class="@if (\Request::is('productupload/*')) active @endif"><a href="<?=URL::to('/productupload/create');?>">Product Upload &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>

                            <li class="@if (\Request::is('costing/create*')) active @endif"><a href="<?=URL::to('/costing/create');?>">Costing Sheet &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/costinglist*')) active @endif"><a href="<?=URL::to('/costing/costinglist');?>">Costing Product &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qccostingproductcount"><?=$nav_counters['total_costing_product']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/qcaccept*')) active @endif"><a href="<?=URL::to('/costing/qcaccept');?>">Accepted product &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcacceptcount"><?=$nav_counters['total_costing_qc_accept']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/product_list*') || \Request::is('costing/updateproduct*')  ) active @endif"><a href="<?=URL::to('/costing/product_list');?>">Product List &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/qcreject*')) active @endif"><a href="<?=URL::to('/costing/qcreject');?>">Rejected product &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcrejectcount"><?=$nav_counters['total_costing_qc_reject']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/IGIlist*')) active @endif"><a href="<?=URL::to('/costing/IGIlist');?>">IGI &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcigicount"><?=$nav_counters['total_costing_qc_igi']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/qcrequestinvoice*')) active @endif"><a href="<?=URL::to('/costing/qcrequestinvoice');?>">Requested invoice &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcrequestinvoice"><?=$nav_counters['total_costing_qc_request_invoice']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/qcreturnmemo*')) active @endif"><a href="<?=URL::to('/costing/qcreturnmemo');?>">Retured memo &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcreturnmemo"><?=$nav_counters['total_costing_qc_return_memo']?></span></a>
                            </li>
                            <li class=""><a href="#">Post IGI upload costing &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Return product &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Photoshoot &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Tagging &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Summery &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Listing IGI Sheet&nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Repairing &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Listing of Orders &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#">Purchase Detail &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/costinglog*')) active @endif"><a href="<?=URL::to('/costing/costinglog');?>">Costing log &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_costing_sheet']?></span></a>
                            </li>
                        </ul>
                    </li>


                      <li class="@if ( \Request::is('account/payment-types*')|| \Request::is('account/payments/decline*') || \Request::is('account/payments') || \Request::is('account/payments/create')||\Request::is('account/payments/*/edit')  || \Request::is('account/payments/createadvancepayment') || \Request::is('account/payments/incoming*') ||\Request::is('account/payments/outgoing*') || \Request::is('account/payments/approved*') || \Request::is('account/payments/decline*') || \Request::is('account/payments/summary*')|| \Request::is('account/payments/gold_diamond_summary*')) current-page @endif menu-item-has-children">

                        <a href="javascript:void(0);" class="ripple">
                            <span class="@if ( \Request::is('account/payment-types*')|| \Request::is('account/payments/decline*')|| \Request::is('account/payments') || \Request::is('account/payments/create')||\Request::is('account/payments/*/edit')  || \Request::is('account/payments/createadvancepayment') || \Request::is('account/payments/incoming*') ||\Request::is('account/payments/outgoing*') || \Request::is('account/payments/approved*') || \Request::is('account/payments/decline*')|| \Request::is('account/payments/summary*')|| \Request::is('account/payments/gold_diamond_summary*')) color-color-scheme @endif">
                                <i class="list-icon material-icons">account_balance</i>
                                <span class="hide-menu">Accounts</span>
                            </span>
                        </a>
                        <ul class="list-unstyled sub-menu @if (\Request::is('account/payment-types*')||\Request::is('account/payments') || \Request::is('account/payments/create') || \Request::is('account/payments/decline*') ||\Request::is('account/payments/*/edit')  || \Request::is('account/payments/createadvancepayment') || \Request::is('account/payments/incoming*') ||\Request::is('account/payments/outgoing*') || \Request::is('account/payments/approved*') || \Request::is('account/payments/decline*')|| \Request::is('account/payments/summary*')|| \Request::is('account/payments/gold_diamond_summary*') || \Request::is('payment-types/*/edit')) in @endif">
                            <li class="@if (\Request::is('account/payment-types*') || \Request::is('account/payment-types/*/edit') ) active @endif">
                                 <a href="<?=URL::to('/account/payment-types');?>"> Payment Header&nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_paymenttype']?></span></a>
                            </li>
                            <li class="@if (\Request::is('account/payments/create')) active @endif">
                                <a href="<?=URL::to('/account/payments/create');?>">Create Payment&nbsp;
                                </a>
                            </li>
                            <li class="@if (\Request::is('account/payments/createadvancepayment')) active @endif">
                                <a href="<?=URL::to('/account/payments/createadvancepayment');?>">Create Advance Payment&nbsp;
                                </a>
                            </li>
                            <li class="@if (\Request::is('account/payments') || \Request::is('account/payments/*/edit')) active @endif">
                                <a href="<?=URL::to('account/payments#over_due');?>">Payment List&nbsp;
                                    <span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_paymentlist']?>
                                    </span>
                                </a>
                            </li>
                            <li class="@if (\Request::is('account/payments/incoming*')) active @endif">
                                <a href="<?=URL::to('/account/payments/incoming');?>">Incoming Payments&nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_incoming']?></span>
                                </a>
                            </li>
                            <li class="@if (\Request::is('account/payments/outgoing*')) active @endif">
                                <a href="<?=URL::to('/account/payments/outgoing');?>">Outgoing Payments&nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_outgoing']?></span>

                                </a>
                            </li>
                            <li class="@if (\Request::is('account/payments/approved*')) active @endif">
                            <a href="<?=URL::to('/account/payments/approved');?>">Approved Payments&nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_approved']?></span>

                                </a>
                            </li>
                            <li class="@if (\Request::is('account/payments/decline*')) active @endif">
                                <a href="<?=URL::to('/account/payments/decline');?>">Decline Payments&nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_decline']?></span>
                                </a>
                            </li>
                            <li class="@if (\Request::is('account/payments/summary*')) active @endif">
                                <a href="<?=URL::to('/account/payments/summary');?>">Summary&nbsp;

                                </a>
                            </li>
                            <li>
                                <a href="#">Reminder&nbsp;

                                </a>
                            </li>
                            <li class="@if (\Request::is('account/payments/gold_diamond_summary*')) active @endif">
                                <a href="<?=URL::to('/account/payments/gold_diamond_summary');?>">Gold Diamond Inventory&nbsp;

                                </a>
                            </li>
                            <li>
                                <a href="#">Store Statistics&nbsp;

                                </a>
                            </li>
                        </ul>
                    </li>
                   <li class="@if (\Request::is('payment*') || \Request::is('/payment/payment_daily_report') || \Request::is('payment/paidtransaction')) current-page @endif menu-item-has-children">

                        <a href="javascript:void(0);" class="ripple">
                            <span class="@if (\Request::is('payment*') || \Request::is('payment/incoming') || \Request::is('/payment/payment_daily_report') || \Request::is('payment/paidtransaction')) color-color-scheme @endif">
                                <i class="list-icon material-icons">payment</i>
                                <span class="hide-menu">Payments</span>
                            </span>
                        </a>
                         <ul class="list-unstyled sub-menu @if (\Request::is('payment/*') || \Request::is('payment/paidtransaction') )in @endif">
                            <li class="@if (\Request::is('payment/incoming')|| \Request::is('payment/paidtransaction')) active @endif">
                                 <a href="<?=URL::to('payment/incoming');?>">Incoming Payments&nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_payment_incoming']?></span></a>
                            </li>
                        </ul>
                         <ul class="list-unstyled sub-menu @if (\Request::is('payment/*') || \Request::is('payment/paidtransaction')) in @endif">
                            <li class="@if (\Request::is('payment/outgoing') || \Request::is('payment/paidtransaction')) active @endif">
                                 <a href="<?=URL::to('/payment/outgoing');?>"> Outgoing Payments&nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_payment_outgoing']?></span></a>
                            </li>
                        </ul>
                        <ul class="list-unstyled sub-menu @if (\Request::is('payment*')||\Request::is('payment/paidpayment')|| \Request::is('payment/paidtransaction/*')) in @endif">
                            <li class="@if (\Request::is('payment/paidpayment') || \Request::is('payment/paidtransaction/*') ) active @endif">
                                 <a href="<?=URL::to('/payment/paidpayment');?>"> Paid Payments&nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_paidpayment']?></span></a>
                            </li>
                        </ul>
                        <ul class="list-unstyled sub-menu @if (\Request::is('payment*')) in @endif">
                            <li class="@if (\Request::is('#')) active @endif">
                                 <a href="<?=URL::to('#');?>"> Cash Voucher&nbsp;</a>
                            </li>
                        </ul>
                        <ul class="list-unstyled sub-menu @if (\Request::is('payment*')) in @endif">
                            <li class="@if (\Request::is('payment/summary')) active @endif">
                                 <a href="<?=URL::to('/payment/summary');?>"> Summary&nbsp;</a>
                            </li>
                        </ul>
                        <ul class="list-unstyled sub-menu @if (\Request::is('payment*')) in @endif">
                            <li class="@if (\Request::is('payment/payment_daily_report*')) active @endif">
                                 <a href="<?=URL::to('/payment/payment_daily_report');?>"> Daily Report&nbsp;</a>
                            </li>
                        </ul>
                    </li>
                    <li class="@if (\Request::is('settings')) current-page @endif"><a href="<?=URL::to('/settings');?>" class="ripple"><span class="@if (\Request::is('settings')) color-color-scheme @endif"><i class="list-icon material-icons">settings</i> <span class="hide-menu">Settings</span></span></a></li>

            <?php
} elseif ($user->hasRole('QC')) {
	?>

                    <?php if ($user->is_admin) {?>
                    <li class="@if(\Request::is('users*')) active @endif"><a href="<?=URL::to('/users');?>"><span class="@if (\Request::is('users*')) color-color-scheme @endif"><i class="list-icon material-icons">people</i> <span class="hide-menu">Users </span></span><span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_users']?></span></a>
                    </li>
                    <?php }?>
                            <li class="@if (\Request::is('productupload/*')) active @endif"><a href="<?=URL::to('/productupload/create');?>"><span class="@if (\Request::is('productupload/*')) color-color-scheme @endif"><i class="list-icon material-icons">file_upload</i> <span class="hide-menu">Product Upload</span></span><span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>

                           <li class="@if (\Request::is('costing/create*')) active @endif"><a href="<?=URL::to('/costing/create');?>"><span class="@if (\Request::is('costing/create*')) color-color-scheme @endif"><i class="list-icon material-icons">assignment</i> <span class="hide-menu">Costing Sheet </span></span><span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/costinglist*')) active @endif"><a href="<?=URL::to('/costing/costinglist');?>"><span class="@if (\Request::is('costing/costinglist*')) color-color-scheme @endif"><i class="list-icon material-icons">assignment_returned</i> <span class="hide-menu">Costing Products </span></span><span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_costing_product']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/qcaccept*')) active @endif"><a href="<?=URL::to('/costing/qcaccept');?>"><span class="@if (\Request::is('costing/qcaccept*')) color-color-scheme @endif"><i class="list-icon material-icons">done</i> <span class="hide-menu">Accepted product &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcacceptcount"><?=$nav_counters['total_costing_qc_accept']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/product_list*')) active @endif"><a href="<?=URL::to('/costing/product_list');?>"><span class="@if (\Request::is('costing/product_list*')) color-color-scheme @endif"><i class="list-icon material-icons">list</i> Product List &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/qcreject*')) active @endif"><a href="<?=URL::to('/costing/qcreject');?>"><span class="@if (\Request::is('costing/qcreject*')) color-color-scheme @endif"><i class="list-icon material-icons">list</i> Rejected product &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcrejectcount"><?=$nav_counters['total_costing_qc_reject']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/IGIlist*')) active @endif"><a href="<?=URL::to('/costing/IGIlist');?>"><span class="@if (\Request::is('costing/IGIlist*')) color-color-scheme @endif"><i class="list-icon material-icons">list</i>IGI &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcigicount"><?=$nav_counters['total_costing_qc_igi']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/qcrequestinvoice*')) active @endif"><a href="<?=URL::to('/costing/qcrequestinvoice');?>"><span class="@if (\Request::is('costing/qcrequestinvoice*')) color-color-scheme @endif"><i class="list-icon material-icons">list</i>Requested invoice &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcrequestinvoice"><?=$nav_counters['total_costing_qc_request_invoice']?></span></a>
                            </li>
                            <li class="@if (\Request::is('costing/qcreturnmemo*')) active @endif"><a href="<?=URL::to('/costing/qcreturnmemo');?>"><span class="@if (\Request::is('costing/qcreturnmemo')) color-color-scheme @endif"><i class="list-icon material-icons">list</i>Retured memo &nbsp;<span class="badge badge-border badge-border-inverted bg-primary qcreturnmemo"><?=$nav_counters['total_costing_qc_return_memo']?></span></a>
                            </li>
                            <li class=""><a href="#"><span class="@if (\Request::is('#')) color-color-scheme @endif"><i class="list-icon material-icons">list</i>Post IGI upload costing &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#"><span class="@if (\Request::is('#')) color-color-scheme @endif"><i class="list-icon material-icons">list</i>Return product &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#"><span class="@if (\Request::is('#')) color-color-scheme @endif"><i class="list-icon material-icons">list</i>Photoshoot &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#"><span class="@if (\Request::is('#')) color-color-scheme @endif"><i class="list-icon material-icons">list</i>Tagging &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#"><span class="@if (\Request::is('#')) color-color-scheme @endif"><i class="list-icon material-icons">list</i>Summery &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#"><span class="@if (\Request::is('#')) color-color-scheme @endif"><i class="list-icon material-icons">list</i>Listing IGI Sheet&nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#"><span class="@if (\Request::is('#')) color-color-scheme @endif"><i class="list-icon material-icons">list</i>Repairing &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#"><span class="@if (\Request::is('#')) color-color-scheme @endif"><i class="list-icon material-icons">list</i>Listing of Orders &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                            <li class=""><a href="#"><span class="@if (\Request::is('#')) color-color-scheme @endif"><i class="list-icon material-icons">list</i>Purchase Detail &nbsp;<span class="badge badge-border badge-border-inverted bg-primary"></span></a>
                            </li>
                           <li class="@if (\Request::is('costing/costinglog*')) active @endif"><a href="<?=URL::to('/costing/costinglog');?>"><span class="@if (\Request::is('costing/costinglog*')) color-color-scheme @endif"><i class="list-icon material-icons">assessment</i> <span class="hide-menu">Costing Logs </span></span><span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_costing_sheet']?></span></a>
                    </li>

<?php
} elseif ($user->hasRole('Gold Manager') || $user->hasRole('Diamond Manager')) {
	?>
                    <?php if ($user->is_admin) {?>
                    <li class="@if(\Request::is('users*')) active @endif"><a href="<?=URL::to('/users');?>"><span class="@if (\Request::is('users*')) color-color-scheme @endif"><i class="list-icon material-icons">people</i> <span class="hide-menu">Users </span></span><span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_users']?></span></a>
                    </li>
                    <?php }?>
                    <li class="@if(\Request::is('transaction-type*')) active @endif"><a href="<?=URL::to('/transaction-type');?>"><span class="@if (\Request::is('transaction-type*')) color-color-scheme @endif"><i class="list-icon material-icons">storage</i> <span class="hide-menu">Transaction Types </span></span><span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_transaction_types']?></span>
                        </a>
                    </li>
                      <li class="@if(\Request::is('Vendors*')) active @endif"><a href="<?=URL::to('/vendor_details');?>"><span class="@if (\Request::is('Vendors*')) color-color-scheme @endif"><i class="list-icon material-icons">people_outline</i> <span class="hide-menu">Vendors</span></span><span class="badge badge-border badge-border-inverted bg-primary"></span>
                        </a>
                    </li>
                    <?php
if ($user->hasRole('Gold Manager')) {
		?>
                                        <li class="@if(\Request::is('gold-inventory*')) active @endif">
                                            <a href="<?=URL::to('/gold-inventory');?>"><span class="@if (\Request::is('gold-inventory*')) color-color-scheme @endif"><i class="list-icon material-icons">style</i> <span class="hide-menu">Gold Inventory </span></span></a>
                                        </li>
                                         <li class="@if(\Request::is('/gold-inventory/goldissue')) active @endif">
                                            <a href="<?=URL::to('/');?>/gold-inventory/goldissue"><span class="@if (\Request::is('/gold-inventory/goldissue*')) color-color-scheme @endif"><i class="list-icon material-icons">turned_in</i> <span class="hide-menu">Gold Issue</span></span></a>
                                        </li>
                    <?php
}
	if ($user->hasRole('Diamond Manager')) {
		?>
                                        <li class="@if(\Request::is('diamondraw') || \Request::is('diamondraw/cvd-list') || \Request::is('diamondraw/assortinglist')||\Request::is('diamondraw/create') || \Request::is('diamond-inventory/create/*')) active @endif">
                                            <a href="<?=URL::to('/');?>/diamondraw"><span class="@if (\Request::is('diamondraw')|| \Request::is('diamondraw/cvd-list')||\Request::is('diamondraw/create') || \Request::is('diamondraw/assortinglist') || \Request::is('diamond-inventory/create/*')) color-color-scheme @endif" class="ripple"><i class="list-icon material-icons">star_half</i> <span class="hide-menu">Raw Diamond </span></span></a>
                                            <ul class="list-unstyled sub-menu @if (\Request::is('diamondraw*')) in @endif">
                                        <li class="@if(\Request::is('diamondraw')||\Request::is('diamondraw/create'))active @endif">
                                        <a href="<?=URL::to('/');?>/diamondraw">Raw Diamond List
                                        <span class="badge badge-border badge-border-inverted bg-primary" style="display: none;">
                                        </span>
                                        </a>
                                        </li>
                                         <li class="@if(\Request::is('diamondraw/cvd-list*'))active @endif">
                                            <a href="<?=URL::to('/');?>/diamondraw/cvd-list">Raw CVD List
                                                <span class="badge badge-border badge-border-inverted bg-primary" style="display: none;">
                                            </span>
                                            </a>
                                        </li>
                                        <li class="@if(\Request::is('diamondraw/assortinglist')|| \Request::is('diamond-inventory/create/*'))active @endif">
                                            <a href="<?=URL::to('/');?>/diamondraw/assortinglist">Raw Assorting List
                                            <span class="badge badge-border badge-border-inverted bg-primary" style="display: none;">
                                            </span>
                                            </a>
                                        </li>
                                    </ul>
                                        </li>

                                        <li class="@if(\Request::is('diamond-inventory') ||\Request::is('diamond-inventory/creatediamonds')) active @endif">
                                            <a href="<?=URL::to('/diamond-inventory');?>"><span class="@if (\Request::is('diamond-inventory')||\Request::is('diamond-inventory/creatediamonds')) color-color-scheme @endif"><i class="list-icon material-icons">star_border</i> <span class="hide-menu">Diamond Inventory </span></span></a>
                                        </li>
                                        <li class="@if(\Request::is('diamond/diamondissue*')) active @endif">
                                            <a href="<?=URL::to('/');?>/diamond/diamondissue"><span class="@if (\Request::is('diamond/diamondissue*')) color-color-scheme @endif"><i class="list-icon material-icons">turned_in</i> <span class="hide-menu">Diamond Issue</span></span></a>
                                        </li>
                                        <li class="@if(\Request::is('diamond/generatediamondinvoice')) active @endif">
                                            <a href="<?=URL::to('/');?>/diamond/generatediamondinvoice"><span class="@if (\Request::is('diamond/generatediamondinvoice')) color-color-scheme @endif"><i class="list-icon material-icons">class</i> <span class="hide-menu">Diamond Invoice</span></span></a>
                                        </li>

                                        <li class="@if(\Request::is('diamond/invoiceattachment*')) active @endif">
                                            <a href="<?=URL::to('/');?>/diamond/invoiceattachment"><span class="@if (\Request::is('diamond/invoiceattachment*')) color-color-scheme @endif"><i class="list-icon material-icons">attach_file</i> <span class="hide-menu">Invoice Attachment</span></span></a>
                                        </li>

                                        <li class="@if(\Request::is('diamond/importexcel*')) active @endif">
                                            <a href="<?=URL::to('/');?>/diamond/importexcel"><span class="@if (\Request::is('diamond/importexcel*')) color-color-scheme @endif"><i class="list-icon material-icons">file_download</i> <span class="hide-menu">Import Excel</span></span></a>
                                        </li>



                                        <li class="@if(\Request::is('purchase-history')) active @endif">
                                            <a href="<?=URL::to('/purchase-history');?>"><span class="@if (\Request::is('purchase-history')) color-color-scheme @endif"><i class="list-icon material-icons">hourglass_empty</i> <span class="hide-menu">Purchase History</span></span></a>
                                        </li>

                                         <li class="@if(\Request::is('diamondraw/issue_voucher_list')) active @endif">
                                            <a href="<?=URL::to('/diamondraw/issue_voucher_list');?>"><span class="@if (\Request::is('diamondraw/issue_voucher_list')) color-color-scheme @endif"><i class="list-icon material-icons">hourglass_empty</i> <span class="hide-menu">Issue Voucher List</span></span></a>
                                        </li>

                                </li>
                    <?php
}

} elseif ($user->hasRole('Account Manager')) {
	?>
                    <?php if ($user->is_admin) {?>
                    <li class="@if(\Request::is('users*')) active @endif"><a href="<?=URL::to('/users');?>"><span class="@if (\Request::is('users*')) color-color-scheme @endif"><i class="list-icon material-icons">people</i> <span class="hide-menu">Users </span></span><span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_users']?></span></a>
                    </li>
                    <?php }?>
                    <li class="@if (\Request::is('account/payment-types*')) active @endif">
                         <a href="<?=URL::to('/account/payment-types');?>"><span class="@if (\Request::is('account/payment-types*')) color-color-scheme @endif"><i class="list-icon material-icons">storage</i> <span class="hide-menu">Payment Types </span></span></a>
                    </li>
                    <li class="@if (\Request::is('account/payments/create')) active @endif">
                        <a href="<?=URL::to('/account/payments/create');?>"><span class="@if (\Request::is('account/payments/create')) color-color-scheme @endif"><i class="list-icon material-icons">payment</i> <span class="hide-menu">Create Payment</span></span>
                        </a>
                    </li>
                    <li class="@if (\Request::is('account/payments')) active @endif">
                        <a href="<?=URL::to('/account/payments');?>"><span class="@if (\Request::is('account/payments')) color-color-scheme @endif"><i class="list-icon material-icons">dns</i> <span class="hide-menu">Payment List</span></span>
                        </a>
                    </li>
                    <li class="@if (\Request::is('account/payments/incoming*')) active @endif">
                        <a href="<?=URL::to('/account/payments/incoming');?>"><span class="@if (\Request::is('account/payments/incoming*')) color-color-scheme @endif"><i class="list-icon material-icons">vertical_align_bottom</i> <span class="hide-menu">Incoming Payments</span></span>
                        </a>
                    </li>
                    <li class="@if (\Request::is('account/payments/outgoing*')) active @endif">
                        <a href="<?=URL::to('/account/payments/outgoing');?>"><span class="@if (\Request::is('account/payments/outgoing*')) color-color-scheme @endif"><i class="list-icon material-icons">vertical_align_top</i> <span class="hide-menu">Outgoing Payments</span></span>

                        </a>
                    </li>
                    <li class="@if (\Request::is('account/payments/approved*')) active @endif">
                    <a href="<?=URL::to('/account/payments/approved');?>"><span class="@if (\Request::is('account/payments/approved*')) color-color-scheme @endif"><i class="list-icon material-icons">check_circle</i> <span class="hide-menu">Approved Payments</span></span>

                        </a>
                    </li>
                    <li class="@if (\Request::is('account/payments/decline*')) active @endif">
                        <a href="<?=URL::to('/account/payments/decline');?>"><span class="@if (\Request::is('account/payments/decline*')) color-color-scheme @endif"><i class="list-icon material-icons">remove_circle</i> <span class="hide-menu">Decline Payments</span></span>
                        </a>
                    </li>
                    <li >
                        <a href="#"><span class="@if (\Request::is('dknfdgkjndfg*')) color-color-scheme @endif"><i class="list-icon material-icons">dashboard</i> <span class="hide-menu">Summary</span></span>

                        </a>
                    </li>
                    <li>
                        <a href="#"><span class="@if (\Request::is('dknfdgkjndfg*')) color-color-scheme @endif"><i class="list-icon material-icons">alarm_on</i> <span class="hide-menu">Reminder</span></span>
                        </a>
                    </li>
                    <li>
                        <a href="#"><span class="@if (\Request::is('dknfdgkjndfg*')) color-color-scheme @endif"><i class="list-icon material-icons">view_comfy</i> <span class="hide-menu">Gold Diamond</span></span> Inventory
                        </a>
                    </li>
                    <li>
                        <a href="#"><span class="@if (\Request::is('dknfdgkjndfg*')) color-color-scheme @endif"><i class="list-icon material-icons">insert_chart</i> <span class="hide-menu">Store Statistics</span></span>
                        </a>
                    </li>
                    <?php
} elseif ($user->hasRole('Showroom Manager')) {
	?>
                            <?php if ($user->is_admin) {?>
                            <li class="@if(\Request::is('users*')) active @endif"><a href="<?=URL::to('/users');?>"><span class="@if (\Request::is('users*')) color-color-scheme @endif"><i class="list-icon material-icons">people</i> <span class="hide-menu">Users </span></span><span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_users']?></span></a>
                            </li>
                            <?php }?>
                            <li class="@if (\Request::is('inventory/generatequotation') || \Request::is('showroom/showroominventory') || \Request::is('inventory/index')  || \Request::is('inventory/stocktally') || \Request::is('showroom/approvalinventory') || \Request::is('showroom/soldinventory') || \Request::is('showroom/allstock') || \Request::is('showroom/pendingstock')) current-page active @endif menu-item-has-children"><a href="<?=URL::to('inventory/index');?>" class="ripple"><span class="@if (\Request::is('showroom/showroominventory') || \Request::is('inventory/index')  || \Request::is('inventory/stocktally') || \Request::is('showroom/approvalinventory') || \Request::is('showroom/soldinventory') || \Request::is('showroom/allstock') || \Request::is('showroom/pendingstock')) color-color-scheme @endif"><i class="list-icon material-icons">dns</i><span class="hide-menu">Inventory </span></span></a>
                                <ul class="list-unstyled sub-menu @if (\Request::is('inventory/generatequotation') || \Request::is('showroom/showroominventory') || \Request::is('inventory/index')  || \Request::is('inventory/stocktally') || \Request::is('showroom/approvalinventory') || \Request::is('showroom/soldinventory') || \Request::is('showroom/allstock') || \Request::is('showroom/pendingstock')) in @endif">
                                    <li class="@if (\Request::is('inventory/stocktally')) active @endif"><a href="<?=URL::to('inventory/stocktally');?>">Manage Inventory &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('inventory/generatequotation') || \Request::is('showroom/showroominventory')) active @endif"><a href="<?=URL::to('showroom/showroominventory');?>">Showroom Stock &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('showroom/approvalinventory')) active @endif"><a href="<?=URL::to('showroom/approvalinventory');?>">Approval Stock &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('showroom/soldinventory')) active @endif"><a href="<?=URL::to('showroom/soldinventory');?>">Sold Stock &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('showroom/pendingstock')) active @endif"><a href="<?=URL::to('showroom/pendingstock');?>">Pending Stock &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('showroom/allstock')) active @endif"><a href="<?=URL::to('showroom/allstock');?>">All Stock &nbsp;</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="@if (\Request::is('inventory/memolist') || \Request::is('inventory/invoicelist') || \Request::is('inventory/returnmemolist') || \Request::is('showroom/salesreturnlist') || \Request::is('inventory/quotationlist')) current-page active @endif menu-item-has-children"><a href="<?=URL::to('inventory/index');?>" class="ripple"><span class="@if (\Request::is('inventory/memolist') || \Request::is('inventory/invoicelist') || \Request::is('inventory/returnmemolist') || \Request::is('showroom/salesreturnlist') || \Request::is('inventory/quotationlist')) color-color-scheme @endif"><i class="list-icon material-icons">assignment</i><span class="hide-menu">Sales/Approval List </span></span></a>
                                <ul class="list-unstyled sub-menu @if (\Request::is('inventory/memolist') || \Request::is('inventory/invoicelist') || \Request::is('inventory/returnmemolist') || \Request::is('showroom/salesreturnlist') || \Request::is('inventory/quotationlist')) in @endif">
                                    <li class="@if (\Request::is('inventory/memolist')) active @endif"><a href="<?=URL::to('inventory/memolist');?>">Memo List &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('inventory/invoicelist') || \Request::is('inventory/getcanceledinvoice') || \Request::is('inventory/getcompletedinvoice')) active @endif"><a href="<?=URL::to('inventory/invoicelist');?>">Invoice List &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('inventory/returnmemolist')) active @endif"><a href="<?=URL::to('inventory/returnmemolist');?>">Return Memo List &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('showroom/salesreturnlist')) active @endif"><a href="<?=URL::to('showroom/salesreturnlist');?>">Sales Return List&nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('inventory/quotationlist')) active @endif"><a href="<?=URL::to('inventory/quotationlist');?>">Quotation List&nbsp;</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="@if(\Request::is('showroom/qrcodescanning')) active @endif"><a href="<?=URL::to('showroom/qrcodescanning');?>"><span class="@if (\Request::is('showroom/qrcodescanning')) color-color-scheme @endif"><i class="list-icon  material-icons">list</i> <span class="hide-menu">Qrcode Scanning </span></span></a>

                    <?php
} elseif ($user->hasRole('Showroom Inventory Manager')) {
	?>
    <?php if ($user->is_admin) {?>
                    <li class="@if(\Request::is('users*')) active @endif"><a href="<?=URL::to('/users');?>"><span class="@if (\Request::is('users*')) color-color-scheme @endif"><i class="list-icon material-icons">people</i> <span class="hide-menu">Users </span></span><span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_users']?></span></a>
                    </li>
                    <?php }?>

                        <li class="list-unstyled sub-menu @if (\Request::is('inventory/generatequotation') || \Request::is('showroom/showroominventory') || \Request::is('inventory/index')  || \Request::is('inventory/stocktally') || \Request::is('inventory/memolist') || \Request::is('inventory/invoicelist') || \Request::is('inventory/returnmemolist') || \Request::is('inventory/quotationlist') || \Request::is('inventory/getcanceledinvoice') || \Request::is('inventory/getcompletedinvoice')) in @endif">
                            <li class="@if (\Request::is('inventory/generatequotation') || \Request::is('showroom/showroominventory') || \Request::is('inventory/index')  || \Request::is('inventory/stocktally') || \Request::is('showroom/approvalinventory') || \Request::is('showroom/soldinventory') || \Request::is('showroom/allstock') || \Request::is('showroom/pendingstock')) current-page active @endif menu-item-has-children"><a href="<?=URL::to('inventory/index');?>" class="ripple"><span class="@if (\Request::is('showroom/showroominventory') || \Request::is('inventory/index')  || \Request::is('inventory/stocktally') || \Request::is('showroom/approvalinventory') || \Request::is('showroom/soldinventory') || \Request::is('showroom/allstock') || \Request::is('showroom/pendingstock')) color-color-scheme @endif"><i class="list-icon material-icons">add_to_queue</i><span class="hide-menu">Inventory </span></span></a>
                                <ul class="list-unstyled sub-menu @if (\Request::is('inventory/generatequotation') || \Request::is('showroom/showroominventory') || \Request::is('inventory/index')  || \Request::is('inventory/stocktally') || \Request::is('showroom/approvalinventory') || \Request::is('showroom/soldinventory') || \Request::is('showroom/allstock') || \Request::is('showroom/pendingstock')) in @endif">
                                    <li class="@if (\Request::is('inventory/stocktally')) active @endif"><a href="<?=URL::to('inventory/stocktally');?>">Manage Inventory &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('inventory/generatequotation') || \Request::is('showroom/showroominventory')) active @endif"><a href="<?=URL::to('showroom/showroominventory');?>">Showroom Stock &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('showroom/approvalinventory')) active @endif"><a href="<?=URL::to('showroom/approvalinventory');?>">Approval Stock &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('showroom/soldinventory')) active @endif"><a href="<?=URL::to('showroom/soldinventory');?>">Sold Stock &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('showroom/pendingstock')) active @endif"><a href="<?=URL::to('showroom/pendingstock');?>">Pending Stock &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('showroom/allstock')) active @endif"><a href="<?=URL::to('showroom/allstock');?>">All Stock &nbsp;</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="@if (\Request::is('inventory/memolist') || \Request::is('inventory/invoicelist') || \Request::is('inventory/returnmemolist') || \Request::is('showroom/salesreturnlist') || \Request::is('inventory/quotationlist')) current-page active @endif menu-item-has-children"><a href="<?=URL::to('inventory/index');?>" class="ripple"><span class="@if (\Request::is('inventory/memolist') || \Request::is('inventory/invoicelist') || \Request::is('inventory/returnmemolist') || \Request::is('showroom/salesreturnlist') || \Request::is('inventory/quotationlist')) color-color-scheme @endif"><i class="list-icon material-icons">list</i><span class="hide-menu">Sales/Approval List </span></span></a>
                                <ul class="list-unstyled sub-menu @if (\Request::is('inventory/memolist') || \Request::is('inventory/invoicelist') || \Request::is('inventory/returnmemolist') || \Request::is('showroom/salesreturnlist') || \Request::is('inventory/quotationlist')) in @endif">
                                    <li class="@if (\Request::is('inventory/memolist')) active @endif"><a href="<?=URL::to('inventory/memolist');?>">Memo List &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('inventory/invoicelist') || \Request::is('inventory/getcanceledinvoice') || \Request::is('inventory/getcompletedinvoice')) active @endif"><a href="<?=URL::to('inventory/invoicelist');?>">Invoice List &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('inventory/returnmemolist')) active @endif"><a href="<?=URL::to('inventory/returnmemolist');?>">Return Memo List &nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('showroom/salesreturnlist')) active @endif"><a href="<?=URL::to('showroom/salesreturnlist');?>">Sales Return List&nbsp;</a>
                                    </li>
                                    <li class="@if (\Request::is('inventory/quotationlist')) active @endif"><a href="<?=URL::to('inventory/quotationlist');?>">Quotation List&nbsp;</a>
                                    </li>
                                </ul>

                            </li>
                            <li class="@if(\Request::is('showroom/qrcodescanning')) active @endif"><a href="<?=URL::to('showroom/qrcodescanning');?>"><span class="@if (\Request::is('showroom/qrcodescanning')) color-color-scheme @endif"><i class="list-icon  material-icons">list</i> <span class="hide-menu">Qrcode Scanning </span></span></a>
                    </li>


                        </ul>
                    </li>

        <?php } elseif ($user->hasRole('Customer Manager')) {?>
             <?php if ($user->is_admin) {?>

                     <li class="@if (\Request::is('customers*'))  active @endif"><a href="<?=URL::to('/customers');?>" class="ripple"><span class="@if (\Request::is('customers*')) color-color-scheme @endif"><i class="list-icon material-icons">people_outline</i> <span class="hide-menu">Customers </span></span></a>
                    </li>
            <?php }?>
         <?php } elseif ($user->hasRole('Payment Manager')) {?>
             <?php if ($user->is_admin) {?>
                  <li class="@if(\Request::is('users*')) active @endif"><a href="<?=URL::to('/users');?>"><span class="@if (\Request::is('users*')) color-color-scheme @endif"><i class="list-icon material-icons">people</i> <span class="hide-menu">Users </span></span><span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_users']?></span></a>
                    </li>
                <?php }?>
                    <li class="@if (\Request::is('payment*')) current-page @endif menu-item-has-children">

                        <a href="javascript:void(0);" class="ripple">
                            <span class="@if (\Request::is('payment*')) color-color-scheme @endif">
                                <i class="list-icon material-icons">payment</i>
                                <span class="hide-menu">Payments</span>
                            </span>
                        </a>
                         <ul class="list-unstyled sub-menu @if (\Request::is('payment*')) in @endif">
                            <li class="@if (\Request::is('payment/incoming*')) active @endif">
                                 <a href="<?=URL::to('/payment/incoming');?>"> Incoming Payments&nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_payment_incoming']?></span></a>
                            </li>
                        </ul>
                         <ul class="list-unstyled sub-menu @if (\Request::is('payment*')) in @endif">
                            <li class="@if (\Request::is('payment/outgoing*')) active @endif">
                                 <a href="<?=URL::to('/payment/outgoing');?>"> Outgoing Payments&nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_payment_outgoing']?></span></a>
                            </li>
                        </ul>
                        <ul class="list-unstyled sub-menu @if (\Request::is('payment*')) in @endif">
                            <li class="@if (\Request::is('payment/incoming*')) active @endif">
                                 <a href="<?=URL::to('/payment/paidpayment');?>"> Paid Payments&nbsp;<span class="badge badge-border badge-border-inverted bg-primary"><?=$nav_counters['total_paidpayment']?></span></a>
                            </li>
                        </ul>
                        <ul class="list-unstyled sub-menu @if (\Request::is('payment*')) in @endif">
                            <li class="@if (\Request::is('#')) active @endif">
                                 <a href="<?=URL::to('#');?>"> Cash Voucher&nbsp;</a>
                            </li>
                        </ul>
                        <ul class="list-unstyled sub-menu @if (\Request::is('payment*')) in @endif">
                            <li class="@if (\Request::is('/payment/summary')) active @endif">
                                 <a href="<?=URL::to('/payment/summary');?>"> Summary&nbsp;</a>
                            </li>
                        </ul>
                    </li>
             <?php }?>
        </ul>
    </nav>
            <!-- /.sidebar-nav -->
</aside>
        <!-- /.site-sidebar -->

