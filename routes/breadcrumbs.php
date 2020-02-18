<?php

// FOR DASHBOARD
Breadcrumbs::for ('dashboard', function ($trail) {
	$trail->push('Dashboard', route('dashboard'));
});

// FOR USERS
Breadcrumbs::for ('users.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Users', route('users.index'));
});

Breadcrumbs::for ('users.create', function ($trail) {
	$trail->parent('users.index');
	$trail->push('Create User', route('users.create'));
});

Breadcrumbs::for ('users.edit', function ($trail, $user) {
	$trail->parent('users.index');
	$trail->push('Edit User', route('users.edit', $user->id));
});

// FOR ROLES
Breadcrumbs::for ('roles.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Roles', route('roles.index'));
});

Breadcrumbs::for ('roles.create', function ($trail) {
	$trail->parent('roles.index');
	$trail->push('Create Role', route('roles.create'));
});

Breadcrumbs::for ('roles.edit', function ($trail, $role) {
	$trail->parent('roles.index');
	$trail->push('Edit Role', route('roles.edit', $role->id));
});

// FOR PERMISSIONS

Breadcrumbs::for ('permissions.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Permissions', route('permissions.index'));
});

Breadcrumbs::for ('permissions.create', function ($trail) {
	$trail->parent('permissions.index');
	$trail->push('Create Permission', route('permissions.create'));
});

Breadcrumbs::for ('permissions.edit', function ($trail, $permission) {
	$trail->parent('permissions.index');
	$trail->push('Edit Permission', route('permissions.edit', $permission->id));
});

//

// FOR TRANSACTION TYPES

Breadcrumbs::for ('transactiontype.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Transaction Type', route('transactiontype.index'));
});

Breadcrumbs::for ('transactiontype.create', function ($trail) {
	$trail->parent('transactiontype.index');
	$trail->push('Create Transaction Type', route('transactiontype.create'));
});

Breadcrumbs::for ('transactiontype.edit', function ($trail, $permission) {
	$trail->parent('transactiontype.index');
	$trail->push('Edit Transaction Type', route('transactiontype.edit', $permission->id));
});

//

// FOR Vendor
Breadcrumbs::for ('vendor.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Vendor List', route('vendor.index'));
});

Breadcrumbs::for ('vendor.vendor_details', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Vendor Details', route('vendor.vendor_details'));
});

Breadcrumbs::for ('vendor.create', function ($trail) {
	$trail->parent('vendor.vendor_details');
	$trail->push('Create Vendor', route('vendor.create'));
});

Breadcrumbs::for ('vendor.show', function ($trail, $id) {
	$trail->parent('vendor.vendor_details');
	$trail->push('Vendor', route('vendor.show', $id));
});

Breadcrumbs::for ('vendor.edit', function ($trail, $id) {
	$trail->parent('vendor.vendor_details');
	$trail->push('Edit Vendor', route('vendor.edit', $id));
});

Breadcrumbs::for ('managecharges.index', function ($trail) {
	$trail->parent('vendor.index');
	$trail->push('Manage Charges', route('vendor.index'));
});
Breadcrumbs::for ('managecharges.create', function ($trail) {
	$trail->parent('vendor.index');
	$trail->push('Managecharges Create', route('vendor.create'));
});
Breadcrumbs::for ('managecharges.edit', function ($trail, $id) {
	$trail->parent('vendor.index');
	$trail->push('Managecharges Edit', route('managecharges.edit', $id));
});
Breadcrumbs::for ('vendor-diamond-type.index', function ($trail) {
	$trail->parent('vendor.index');
	$trail->push('Diamondtype', route('vendor-diamond-type.index'));
});
Breadcrumbs::for ('vendor-diamond-type.create', function ($trail) {
	$trail->parent('vendor.index');
	$trail->push('Diamondtype Create', route('vendor-diamond-type.create'));
});
Breadcrumbs::for ('vendor-product-type.index', function ($trail) {
	$trail->parent('vendor.index');
	$trail->push('ProductType', route('vendor-product-type.index'));
});
Breadcrumbs::for ('vendor-product-type.create', function ($trail) {
	$trail->parent('vendor.index');
	$trail->push('ProductType Create', route('vendor-product-type.create'));
});
Breadcrumbs::for ('metalrates.index', function ($trail) {
	$trail->parent('vendor.index');
	$trail->push('Metal Rates', route('metalrates.index'));
});
Breadcrumbs::for ('metalrates.edit', function ($trail, $metalrates_id) {
	$trail->parent('vendor.index');
	$trail->push('Metal Rates Edit', route('metalrates.edit', $metalrates_id));
});
Breadcrumbs::for ('metalrates.create', function ($trail) {
	$trail->parent('vendor.index');
	$trail->push('Metal Rates Create', route('metalrates.create'));
});

Breadcrumbs::for ('vendor.view', function ($trail, $vendorId) {
	$trail->parent('vendor.index');
	$trail->push('View Vendor', route('vendor.view', $vendorId));
});

Breadcrumbs::for ('vendor.vendorpaidtransaction', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Paid Transaction ', route('vendor.vendorpaidtransaction'));
});
// FOR GOLD INVENTORY
Breadcrumbs::for ('metals.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Gold Inventory', route('metals.index'));
});

Breadcrumbs::for ('metals.create', function ($trail) {
	$trail->parent('metals.index');
	$trail->push('Create Metal Transaction', route('metals.create'));
});
Breadcrumbs::for ('gold_edit_transaction', function ($trail,$id) {
	$trail->parent('metals.index');
	$trail->push('Edit Metal Transaction', route('gold_edit_transaction',$id));
});

Breadcrumbs::for ('metals.goldissue', function ($trail) {
	$trail->parent('metals.index');
	$trail->push('Create Metal Transaction', route('metals.goldissue'));
});

Breadcrumbs::for ('metals.transactions', function ($trail, $metal_id) {
	$trail->parent('metals.index');
	$trail->push('Metal Transactions', route('metals.transactions', $metal_id));
});

// FOR DIAMOND INVENTORY
Breadcrumbs::for ('diamond.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Diamond Inventory', route('diamond.index'));
});

Breadcrumbs::for ('diamond.create', function ($trail) {
	$trail->parent('diamond.index');
	$trail->push('Create Diamond Transaction', route('diamond.create'));
});
Breadcrumbs::for ('diamond.edit', function ($trail,$id) {
	$trail->parent('diamond.index');
	$trail->push('Edit Diamond Transaction', route('diamond.edit',$id));
});

Breadcrumbs::for ('diamond.importexcel', function ($trail) {
	$trail->parent('diamond.index');
	$trail->push('Diamond Import Excel', route('diamond.importexcel'));
});

Breadcrumbs::for ('diamond.invoiceattachment', function ($trail) {
	$trail->parent('diamond.index');
	$trail->push('Diamond Invoice Attachment', route('diamond.invoiceattachment'));
});

Breadcrumbs::for ('diamond.transactions', function ($trail, $metal_id) {
	$trail->parent('diamond.index');
	$trail->push('Diamond Transactions', route('diamond.transactions', $metal_id));
});

Breadcrumbs::for ('diamond.diamondissue', function ($trail) {
	$trail->parent('diamond.index');
	$trail->push('Diamond Issue', route('diamond.diamondissue'));
});

Breadcrumbs::for ('diamond.generatediamondinvoicestore', function ($trail) {
	$trail->parent('diamond.index');
	$trail->push('Diamond Invoice', route('diamond.generatediamondinvoicestore'));
});

Breadcrumbs::for ('diamond.diamondinvoice', function ($trail) {
	$trail->parent('diamond.index');
	$trail->push('Diamond Invoice', route('diamond.diamondinvoice'));
});

Breadcrumbs::for ('diamond.diamond_statistics_by_mm', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Diamond Statistics', route('diamond.diamond_statistics_by_mm'));
});
// Breadcrumbs::for ('diamond.diamondmiscloss', function ($trail,$id) {
// 	$trail->parent('diamond.index');
// 	$trail->push('Diamond Issue', route('diamond.diamondmiscloss',$id));
// });

Breadcrumbs::for ('diamondraw.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Diamond Raw Inventory', route('diamondraw.index'));
});
Breadcrumbs::for ('diamondraw.create', function ($trail) {
	$trail->parent('diamondraw.index');
	$trail->push('Create Raw Diamond Transaction', route('diamondraw.create'));
});

Breadcrumbs::for ('diamondraw.cvd-list', function ($trail) {
	$trail->parent('diamondraw.index');
	$trail->push('CVD List', route('diamondraw.cvd-list'));
});

Breadcrumbs::for ('diamondraw.assortinglist', function ($trail) {
	$trail->parent('diamondraw.index');
	$trail->push('Assorting List', route('diamondraw.assortinglist'));
});

Breadcrumbs::for ('diamondraw.sizinglist', function ($trail) {
	$trail->parent('diamondraw.index');
	$trail->push('Sizing List', route('diamondraw.sizinglist'));
});

// FOR  Accounts
Breadcrumbs::for ('paymenttype.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Payment Type', route('paymenttype.index'));
});
Breadcrumbs::for ('paymenttype.create', function ($trail) {
	$trail->parent('paymenttype.index');
	$trail->push('Payment Type Create', route('paymenttype.create'));
});
Breadcrumbs::for ('paymenttype.edit', function ($trail, $paymenttype) {
	$trail->parent('paymenttype.index');
	$trail->push('Payment Type Edit', route('paymenttype.edit', $paymenttype->id));
});
Breadcrumbs::for ('accountpayment.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Payment ', route('accountpayment.index'));
});
Breadcrumbs::for ('accountpayment.create', function ($trail) {
	$trail->parent('accountpayment.index');
	$trail->push('Create Payment', route('accountpayment.create'));
});
Breadcrumbs::for ('payment.createadvancepayment', function ($trail) {
	$trail->parent('accountpayment.index');
	$trail->push('Create Advance Payment', route('payment.createadvancepayment'));
});
Breadcrumbs::for ('accountpayment.edit', function ($trail, $payment) {
	$trail->parent('accountpayment.index');
	$trail->push('Edit Payment', route('accountpayment.edit', $payment->id));
});
Breadcrumbs::for ('accountpayment.decline', function ($trail) {
	$trail->parent('accountpayment.index');
	$trail->push('Decline', route('accountpayment.decline'));
});

Breadcrumbs::for ('accountpayment.approved', function ($trail) {
	$trail->parent('accountpayment.index');
	$trail->push('Approved', route('accountpayment.approved'));
});
Breadcrumbs::for ('accountpayment.incoming', function ($trail) {
	$trail->parent('accountpayment.index');
	$trail->push('Incoming', route('accountpayment.incoming'));
});
Breadcrumbs::for ('accountpayment.outgoing', function ($trail) {
	$trail->parent('accountpayment.index');
	$trail->push('Outgoing', route('accountpayment.outgoing'));
});
Breadcrumbs::for ('accountpayment.summary', function ($trail) {
	$trail->parent('accountpayment.index');
	$trail->push('Account Summary', route('accountpayment.summary'));
});

Breadcrumbs::for ('payment.payment_summary', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Payment Summary ', route('payment.payment_summary'));
});

Breadcrumbs::for ('payment.payment_daily_report', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Daily Report', route('payment.payment_daily_report'));
});

Breadcrumbs::for ('payment.paidpayment', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Paid Payment List ', route('payment.paidpayment'));
});
Breadcrumbs::for ('payment.incoming', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Incoming Payment ', route('payment.incoming'));
});
Breadcrumbs::for ('payment.outgoing', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Outgoing Payment ', route('payment.outgoing'));
});

Breadcrumbs::for ('payment.paidtransaction', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Paid Transaction ', route('payment.paidtransaction'));
});
Breadcrumbs::for ('accountpayment.gold_diamond_summary', function ($trail) {
	$trail->parent('accountpayment.index');
	$trail->push('Gold Diamond Inventory', route('accountpayment.gold_diamond_summary'));
});

//For Costings

Breadcrumbs::for ('costing.create', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Import Costing', route('costing.create'));
});

Breadcrumbs::for ('costing.costinglist', function ($trail) {
	$trail->parent('costing.create');
	$trail->push('Costing Product', route('costing.costinglist'));
});

Breadcrumbs::for ('costing.costinglog', function ($trail) {
	$trail->parent('costing.create');
	$trail->push('Costing Log', route('costing.costinglog'));
});

Breadcrumbs::for ('productupload.updateproduct', function ($trail) {
	$trail->parent('productupload.create');
	$trail->push('Update Product', route('productupload.updateproduct'));
});

// FOR ROLES
Breadcrumbs::for ('settings', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Settings', route('settings'));
});

// FOR SHOWROOM
Breadcrumbs::for ('showroom.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Products', route('showroom.index'));
});

Breadcrumbs::for ('showroom.orderhistory', function ($trail) {
	$trail->parent('showroom.index');
	$trail->push('Showroom Orders', route('showroom.orderhistory'));
});

Breadcrumbs::for ('showroom.orderview', function ($trail, $orderId) {
	$trail->parent('showroom.orderhistory');
	$trail->push('Showroom Order View', route('showroom.orderview', $orderId));
});
Breadcrumbs::for ('showroom.product_list', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Product List', route('showroom.product_list'));
});

Breadcrumbs::for ('costing.qcrequestinvoice', function ($trail) {
	$trail->parent('costing.create');
	$trail->push('Costing Request Invoice', route('costing.qcrequestinvoice'));
});

Breadcrumbs::for ('costing.qcreturnmemo', function ($trail) {
	$trail->parent('costing.create');
	$trail->push('Costing Return Memo', route('costing.qcreturnmemo'));
});

Breadcrumbs::for ('costing.IGIlist', function ($trail) {
	$trail->parent('costing.create');
	$trail->push('IGI Product', route('costing.IGIlist'));
});

Breadcrumbs::for ('costing.qcaccept', function ($trail) {
	$trail->parent('costing.create');
	$trail->push('Accepted Product', route('costing.qcaccept'));
});

//Product upload
Breadcrumbs::for ('productupload.create', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Product Upload', route('productupload.create'));
});

Breadcrumbs::for ('productupload.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Product List', route('productupload.index'));
});

//Inventory
Breadcrumbs::for ('inventory.index', function ($trail) {
	$trail->parent('showroom.showroominventory');
	$trail->push('Inventory', route('inventory.index'));
});
Breadcrumbs::for ('inventory.stocktally', function ($trail) {
	$trail->parent('showroom.showroominventory');
	$trail->push('Stocktally', route('inventory.stocktally'));
});
Breadcrumbs::for ('inventory.memolist', function ($trail) {
	$trail->parent('showroom.showroominventory');
	$trail->push('Memo List', route('inventory.memolist'));
});
Breadcrumbs::for ('inventory.editmemo', function ($trail, $memoId) {
	$trail->parent('inventory.memolist');
	$trail->push('Edit Memo', route('inventory.editmemo' , $memoId));
});
Breadcrumbs::for ('inventory.invoicelist', function ($trail) {
	$trail->parent('showroom.showroominventory');
	$trail->push('Invoice List', route('inventory.invoicelist'));
});
Breadcrumbs::for ('inventory.editinvoice', function ($trail, $invoiceId) {
	$trail->parent('inventory.invoicelist');
	$trail->push('Edit Invoice', route('inventory.editinvoice', $invoiceId));
});
Breadcrumbs::for ('inventory.returnmemolist', function ($trail) {
	$trail->parent('showroom.showroominventory');
	$trail->push('Return Memo List', route('inventory.returnmemolist'));
});
Breadcrumbs::for ('inventory.quotationlist', function ($trail) {
	$trail->parent('showroom.showroominventory');
	$trail->push('Quotation List', route('inventory.quotationlist'));
});
Breadcrumbs::for ('inventory.viewquotation', function ($trail, $quotationId) {
	$trail->parent('inventory.quotationlist');
	$trail->push('View Quotation', route('inventory.viewquotation', $quotationId));
});
Breadcrumbs::for ('inventory.editquotation', function ($trail, $quotationId) {
	$trail->parent('inventory.quotationlist');
	$trail->push('Edit Quotation', route('inventory.editquotation', $quotationId));
});
Breadcrumbs::for ('inventory.exhibitionlist', function ($trail) {
	$trail->parent('showroom.showroominventory');
	$trail->push('Exhibition List', route('inventory.exhibitionlist'));
});
Breadcrumbs::for ('inventory.generatequotation', function ($trail) {
	$trail->parent('showroom.showroominventory');
	$trail->push('Generate Quotation', route('inventory.generatequotation'));
});

//Showroom

Breadcrumbs::for ('showroom.showroominventory', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Showroom Inventory', route('showroom.showroominventory'));
});
Breadcrumbs::for ('showroom.approvalinventory', function ($trail) {
	$trail->parent('showroom.showroominventory');
	$trail->push('Approval Inventory', route('showroom.approvalinventory'));
});
Breadcrumbs::for ('showroom.pendingstock', function ($trail) {
	$trail->parent('showroom.showroominventory');
	$trail->push('Pending Stock', route('showroom.pendingstock'));
});
Breadcrumbs::for ('showroom.soldinventory', function ($trail) {
	$trail->parent('showroom.showroominventory');
	$trail->push('Sold Inventory', route('showroom.soldinventory'));
});
Breadcrumbs::for ('showroom.allstock', function ($trail) {
	$trail->parent('showroom.showroominventory');
	$trail->push('All Stock', route('showroom.allstock'));
});
Breadcrumbs::for ('showroom.salesreturnlist', function ($trail) {
	$trail->parent('showroom.showroominventory');
	$trail->push('Sales Return List', route('showroom.salesreturnlist'));
});
Breadcrumbs::for ('showroom.bulksalesreturn', function ($trail) {
	$trail->parent('showroom.soldinventory');
	$trail->push('Generate Sales Return', route('showroom.bulksalesreturn'));
});

/*Customer*/
Breadcrumbs::for ('customers.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Customers', route('customers.index'));
});
Breadcrumbs::for ('customers.view', function ($trail, $customerId) {
	$trail->parent('customers.index');
	$trail->push('View Customer', route('customers.view', $customerId));
});


/*Customer*/
Breadcrumbs::for ('purchasehistory.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Purchase History', route('purchasehistory.index'));
});

Breadcrumbs::for ('purchasehistory.editgold', function ($trail, $id) {
	$trail->parent('purchasehistory.index');
	$trail->push('Purchase History Edit', route('purchasehistory.editgold', $id));
});
Breadcrumbs::for ('purchasehistory.editdiamond', function ($trail, $id) {
	$trail->parent('purchasehistory.index');
	$trail->push('Purchase History Edit', route('purchasehistory.editdiamond', $id));
});

/* Virtual Box Manager */
Breadcrumbs::for ('virtualboxmanager.create', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Create Virtual Box', route('virtualboxmanager.create'));
});

Breadcrumbs::for ('virtualboxmanager.moveproducts', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Move Products', route('virtualboxmanager.moveproducts'));
});

Breadcrumbs::for ('virtualboxmanager.vbboxlist', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Virtual Box List', route('virtualboxmanager.vbboxlist'));
});

Breadcrumbs::for ('virtualboxmanager.editvb', function ($trail,$id) {
	$trail->parent('virtualboxmanager.vbboxlist');
	$trail->push('Edit Virtual Box', route('virtualboxmanager.editvb', $id));
});
/* Virtual Box Manager End */

/*
photoshop
*/

Breadcrumbs::for ('photography.pending', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Photography Pending', route('photography.pending'));
});
Breadcrumbs::for ('photography.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Photography', route('photography.index'));
});
Breadcrumbs::for ('photography.done', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Photography Done', route('photography.done'));
});
Breadcrumbs::for ('photography.rework', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Photography Rework', route('photography.rework'));
});

//PSD
Breadcrumbs::for ('psd.index', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('PSD', route('psd.index'));
});
Breadcrumbs::for ('psd.pending', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('PSD Pending', route('psd.pending'));
});
Breadcrumbs::for ('psd.done', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('PSD Done', route('psd.done'));
});
Breadcrumbs::for ('psd.rework', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('PSD Pending', route('psd.rework'));
});
Breadcrumbs::for ('editing.pending', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Editing Pending', route('editing.pending'));
});
Breadcrumbs::for ('editing.done', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Editing done', route('editing.done'));
});
Breadcrumbs::for ('editing.rework', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Editing Rework', route('editing.rework'));
});
Breadcrumbs::for ('jpeg.pending', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('JPEG Pending', route('jpeg.pending'));
});
Breadcrumbs::for ('jpeg.done', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('JPEG done', route('jpeg.done'));
});
Breadcrumbs::for ('jpeg.rework', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('JPEG Rework', route('jpeg.rework'));
});

Breadcrumbs::for ('placement_pending', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Placement Pending', route('placement_pending'));
});
Breadcrumbs::for ('placement_done', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Placement done', route('placement_done'));
});
Breadcrumbs::for ('placement_rework', function ($trail) {
	$trail->parent('dashboard');
	$trail->push('Placement Rework', route('placement_rework'));
});
Breadcrumbs::for('product_list',function($trail){
	$trail->parent('dashboard');
	$trail->push('Product List', route('product_list'));
});
Breadcrumbs::for('product_add',function($trail){
	$trail->parent('dashboard');
	$trail->push('Product Add', route('product_add'));
});