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
                        <h4 class="media-heading mr-b-5 text-uppercase">{{$customerName}}</h4><span class="user-type fs-12">Customer Detail</span>
                    </div>
                </a>
                <div class="clearfix"></div>
            </div>
            <!-- /.side-user -->
            <!-- Sidebar Menu -->
            <nav class="sidebar-nav">
                <ul class="nav in side-menu customer-menu-container">
                    <li class=" nav-item"><a href="<?=URL::to('customers/');?>" class="ripple"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">keyboard_arrow_left</i> <span class="hide-menu">Back </span></span></a>
                    </li>
                    <li class="nav-item current-page active"><a href="#customer_dashboard" id="customerdashboard" onclick="showCustomerSection('customer_dashboard',this.id)"><span class="color-color-scheme"><i class="material-icons">perm_identity</i> <span class="hide-menu"> Dashboard</span></span></a>
                    </li>
                    <li class="nav-item"><a href="#customer_detail" id="customerdetail" onclick="showCustomerSection('customer_detail',this.id)"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">perm_identity</i><span class="hide-menu">  Customer Detail</span></span></a>
                    </li>
                    <li class="nav-item "><a href="#customer-inventory" id="customerinventory" onclick="showCustomerSection('customer-inventory',this.id)"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">dvr</i> Customer Inventory &nbsp;</a>
                    </li>
                    <li class="nav-item"><a href="#total-invoices" id="totalinvoices" onclick="showCustomerSection('total-invoices',this.id)"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">list</i><span class="hide-menu">  Total Number of Invoice</span></span></a>
                    </li>
                    <li class="nav-item"><a href="#total-approvals" id="totalapprovals" onclick="showCustomerSection('total-approvals',this.id)"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">check_box</i><span class="hide-menu"> No Of Approval</span></span></a>
                    </li>
                    <li class="nav-item"><a href="#total-return-products" id="totalreturnproducts" onclick="showCustomerSection('total-return-products',this.id)"> <span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">list</i><span class="hide-menu">  No Of Return Products</span></a>
                    </li>
                    <li class="nav-item"><a href="#sales-return" id="salesreturn" onclick="showCustomerSection('sales-return',this.id)"> <span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">list</i><span class="hide-menu">  Sales Return</span></a>
                    </li>
                    <li class="nav-item"><a href="#credit-note" id="creditnote" onclick="showCustomerSection('credit-note',this.id)"> <span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">list</i><span class="hide-menu">  Credit Note</span></a>
                    </li>
                    <li class="nav-item"><a href="#products-exchange" id="productsexchange" onclick="showCustomerSection('products-exchange',this.id)"><span class="@if (\Request::is('/')) color-color-scheme @endif"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">compare_arrows</i><span class="hide-menu"> Exchange of products</span></span></a>
                    </li>
                    <li class="nav-item"><a href="#stock-order-templates" id="stockordertemplates" onclick="showCustomerSection('stock-order-templates',this.id)"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">list</i><span class="hide-menu"> Stock & Online orders &nbsp;</span></span></a>
                    </li>

                    <li class="nav-item"><a href="#provision-performa" id="provisionperforma" onclick="showCustomerSection('provision-performa',this.id)"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">book</i><span class="hide-menu"> Provision Of Performa</span></span></a>
                    </li>
                    <li class="nav-item"><a href="#quotation" id="quotationdata" onclick="showCustomerSection('quotation',this.id)"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">list</i> <span class="hide-menu">Quotation &nbsp;</span></span></a>
                    </li>
                    <li class="menu-item-has-children"><a href="#" class="ripple"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">settings_applications</i><span class="hide-menu"> Discount settings</span></span></a>
                        <ul class="list-unstyled sub-menu">
                            <li class=""><a href="#approval-product-discount" id="approvalproductdiscount" onclick="showCustomerSection('approval-product-discount',this.id)">Approval product discount &nbsp;</a>
                            </li>
                            <li class=""><a href="#deposit-product-discount" id="depositproductdiscount" onclick="showCustomerSection('deposit-product-discount',this.id)">Deposit product discount &nbsp;</a>
                            </li>
                        </ul>

                    </li>                    
                    <li class="nav-item"><a href="#price-markup" id="pricemarkup" onclick="showCustomerSection('price-markup',this.id)"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">arrow_downward</i><span class="hide-menu"> Price Markup </span></span></a>
                    </li>

                    <li class="nav-item"><a href="#paymentlist" id="payment" onclick="showCustomerSection('paymentlist',this.id)"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">list</i><span class="hide-menu">Wallet Histroy</span></span></a>
                    </li>
                    <li class="nav-item"><a href="#paymenthistroy" id="payment_histroy" onclick="showCustomerSection('paymenthistroy',this.id)"><span class="@if (\Request::is('/')) color-color-scheme @endif"><i class="material-icons">list</i><span class="hide-menu">Payment Histroy</span></span></a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-nav -->
        </aside>
        <!-- /.site-sidebar -->