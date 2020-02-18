<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('qr-code-s', ['https' => true], function () {
	return view('qrCodeScanner');
});

Route::get('/batchproducts', 'ProductuploadController@listofproducts');

//Route::get('/securepage', ['uses' => 'HomeController@securepage', 'https' => true]);

Route::get('/', ['as' => 'dashboard', 'uses' => 'HomeController@dashboard'])->middleware('auth', 'navdata');

/*Route::get('/', function () {
return view('demo');
})->middleware('auth', 'navdata')->name('dashboard');*/

Auth::routes();

Route::group(['middleware' => ['auth', 'navdata']], function () {
/* User Profile Pages */

	Route::get('settings/clear-application-cache', ['as' => 'settings.clear.application.cache', 'uses' => 'SettingController@clear_application_cache']);

	Route::get('settings/clear-config-cache', ['as' => 'settings.clear.config.cache', 'uses' => 'SettingController@clear_config_cache']);

	Route::get('settings/clear-raa-cache', ['as' => 'settings.clear.raa.cache', 'uses' => 'SettingController@clear_route_cache']);

	Route::get('settings/clear-view-cache', ['as' => 'settings.clear.view.cache', 'uses' => 'SettingController@clear_view_cache']);

	Route::get('settings/clear-all-cache', ['as' => 'settings.clear.all.cache', 'uses' => 'SettingController@clear_all_cache']);

	Route::get('settings/backup-dng', ['as' => 'settings.backup.dng', 'uses' => 'SettingController@backup_dng']);
	Route::get('settings/backup-dump-dng', ['as' => 'settings.backup.dump.dng', 'uses' => 'SettingController@backup_dump_dng']);

	Route::get('/users/filterdata', 'UserController@filterdata');
	Route::post('/profile', 'UserController@update_avatar');
	Route::get('/profile', ['as' => 'profile', 'uses' => 'UserController@profile']);
	Route::get('removeavatar', ['as' => 'removeavatar', 'uses' => 'UserController@removeavatar']);

	Route::get('/changepassword', 'ProfileController@showChangePasswordForm');
	Route::post('/changepassword', 'ProfileController@changePassword')->name('changePassword');

	Route::get('reset', 'HomeController@reset');
/* User Profile Pages */

/* Vendor Name get for auto complate */

	Route::get('diamond-inventory/searchajax', array('as' => 'searchajax', 'uses' => 'DiamondController@autoComplete'));
	Route::get('diamond-inventory/searchmmtosieveajax', array('as' => 'searchmmtosieveajax', 'uses' => 'DiamondController@mmToSeive'));
	Route::get('diamond-inventory/searchsievetommajax', array('as' => 'searchsievetommajax', 'uses' => 'DiamondController@seiveToMm'));
/* End */

/* For Diamond Shape & Diamond Quality - start */
/*Route::get('diamond-inventory/searchdatabaseshape', array('as' => 'searchdatabaseshape', 'uses' => 'DiamondController@autoCompletedataShape')); */
	Route::get('diamond-inventory/searchinvoicequality', array('as' => 'searchinvoicequality', 'uses' => 'DiamondController@autoCompleteInvoiceQuality'));
	Route::get('diamond-inventory/selectedinvoicequality', array('as' => 'selectedinvoicequality', 'uses' => 'DiamondController@SelectedAutoCompleteInvoiceQuality'));
	Route::get('diamond-inventory/searchajaxshape', array('as' => 'searchajaxshape', 'uses' => 'DiamondController@autoCompleteShape'));
	Route::get('diamond-inventory/searchajaxquality', array('as' => 'searchajaxquality', 'uses' => 'DiamondController@autoCompleteQuality'));
	Route::get('diamond/generatediamondinvoice/searchinvoiceshape', array('as' => 'searchinvoiceshape', 'uses' => 'DiamondController@autoCompleteInvoiceShape'));
/* For Diamond Shape & Diamond Quality - end */

/* Gold & Diamond Inventory */

	Route::get('/diamond-inventory/download-invoice/{id}', ['as' => 'diamond_download_purchase_invoice', 'uses' => 'DiamondTransactionController@downloadInvoice']);

	Route::get('/gold-inventory/download-invoice/{id}', ['as' => 'gold_download_purchase_invoice', 'uses' => 'MetalTransactionController@downloadInvoice']);

	Route::get('/gold-inventory/edit-transaction/{id}', ['as' => 'gold_edit_transaction', 'uses' => 'MetalTransactionController@editTransaction']);

	Route::get('/diamond-inventory/download-issuevaucher/{id}', ['as' => 'diamond_download_issue_vaucher', 'uses' => 'DiamondController@issuevaucher']);

/*Account & Payment Route start*/
	Route::post('account/payments/storeadvancepayment', 'PaymentController@storeadvancepayment');
	Route::post('account/payments/getcustomername', ['as' => 'payment.getcustomername', 'uses' => 'PaymentController@getname']);

	Route::get('payment/summary/{id}', ['as' => 'payment.show_summary', 'uses' => 'PaymentController@show_summary']);
	Route::get('payment/summary', ['as' => 'payment.payment_summary', 'uses' => 'PaymentController@payment_summary']);
	Route::get('account/payments/gold_diamond_summary', ['as' => 'accountpayment.gold_diamond_summary', 'uses' => 'PaymentController@gold_diamond']);
	Route::get('account/payments/summary/{id}', ['as' => 'accountpayment.account_summary', 'uses' => 'PaymentController@account_summary']);

	Route::get('account/payments/summary', ['as' => 'accountpayment.summary', 'uses' => 'PaymentController@summary']);
	Route::get('account/payments/outgoing', ['as' => 'accountpayment.outgoing', 'uses' => 'PaymentController@outgoing']);
	Route::get('account/payments/decline/{id}', 'PaymentController@out_decline');
	Route::get('account/payments/approve/{id}', 'PaymentController@out_approved');
	Route::get('account/payments/approved', ['as' => 'accountpayment.approved', 'uses' => 'PaymentController@approved']);
	Route::get('account/payments/decline', ['as' => 'accountpayment.decline', 'uses' => 'PaymentController@decline']);
	Route::get('account/payments/incoming', ['as' => 'accountpayment.incoming', 'uses' => 'PaymentController@incoming']);
	Route::get('account/payments/createadvancepayment', ['as' => 'accountpayment.createadvancepayment', 'uses' => 'PaymentController@createadvancepayment']);
	Route::post('account/payments/createcustomer', ['as' => 'accountpayment.createcustomer', 'uses' => 'PaymentController@createcustomer']);
	Route::get('account/payments/getemail', 'PaymentController@getemail');
	Route::get('account/payments/customerstore', 'PaymentController@customerstore');
	Route::get('account/payments/dropdown', 'PaymentController@dropdown');
	Route::get('account/payments/multiple_delete', 'PaymentController@multiple_delete');
	Route::get('payment/incoming', ['as' => 'payment.incoming', 'uses' => 'PaymentController@payment_incoming']);
	Route::get('payment/paidpayment', ['as' => 'payment.paidpayment', 'uses' => 'PaymentController@paid_payment']);
	Route::get('payment/incoming/{id}', 'PaymentController@out_paid');
	Route::get('payment/outgoing', ['as' => 'payment.outgoing', 'uses' => 'PaymentController@payment_outgoing']);
	Route::get('payment/payment', ['as' => 'payment.getpayment', 'uses' => 'PaymentController@getpayment']);

	Route::post('payment/payment_transaction', ['as' => 'payment.payment_transaction', 'uses' => 'PaymentController@payment_transaction']);
	Route::get('payment/paidtransaction', ['as' => 'payment.paidtransaction', 'uses' => 'PaymentController@paid_transaction']);
	Route::post('payment/getpaymenttransaction', 'PaymentController@getpaymenttransaction');
	Route::post('payment/generatecashvoucher', 'PaymentController@generatecashvoucher');
	Route::get('payment/viewcashvoucher/{id}', 'PaymentController@viewcashvoucher');

/*Payment response*/

	Route::get('account/payments/paymentresponse', ['as' => 'accountpayment.paymentresponse', 'uses' => 'PaymentController@paymentresponse']);
	Route::get('account/payments/declineresponse', ['as' => 'accountpayment.declineresponse', 'uses' => 'PaymentController@declineresponse']);
	Route::get('account/payments/approvedresponse', ['as' => 'accountpayment.approvedresponse', 'uses' => 'PaymentController@approvedresponse']);
	Route::get('account/payments/incomingresponse', ['as' => 'accountpayment.incomingresponse', 'uses' => 'PaymentController@incomingresponse']);
	Route::get('account/payments/outgoingresponse', ['as' => 'accountpayment.outgoingresponse', 'uses' => 'PaymentController@outgoingresponse']);
	Route::get('account/payments/payment_incomingresponse', ['as' => 'payment.payment_incomingresponse', 'uses' => 'PaymentController@payment_incomingresponse']);
	Route::get('account/payments/payment_outgoingresponse', ['as' => 'payment.payment_outgoingresponse', 'uses' => 'PaymentController@payment_outgoingresponse']);
	Route::get('account/payments/paidpayment_response', ['as' => 'payment.paidpayment_response', 'uses' => 'PaymentController@paidpayment_response']);
	Route::get('payments/paidtransactionresponse', ['as' => 'payment.paidtransactionresponse', 'uses' => 'PaymentController@paidtransactionresponse']);
	Route::get('payment/paidtransaction/{id}', ['as' => 'payment.paidtransaction/{id}', 'uses' => 'PaymentController@paidtransaction']);

	Route::get('account/payment-types/paymentresponse', 'PaymentTypeController@paymentresponse');
	Route::get('payment/payment_daily_report', ['as' => 'payment.payment_daily_report', 'uses' => 'PaymentController@payment_daily_report']);
	Route::get('payment/daily_report_response', 'PaymentController@daily_report_response');

/*Payment response*/
/*Account & Payment Route  End*/

/*Showroom route start*/
	Route::get('showroom/testdata', 'ShowroomController@testdata');
	Route::get('showroom/insertfrncode', 'ShowroomController@insertfrncode');
	Route::post('showroom/calculateproductprice', 'ShowroomController@calculateproductprice');
	Route::get('showroom/removeoldorders', 'ShowroomController@removeoldorders');
	Route::post('showroom/removeprocessedproductfromsession', 'ShowroomController@removeprocessedproductfromsession');
	Route::get('showroom/viewdetail', 'ShowroomController@viewdetail');

	Route::get('showroom/movetomemohistory', array('as' => 'movetomemohistory', 'uses' => 'ShowroomController@movetomemohistory'));
	Route::post('showroom/searchcustomer', array('as' => 'searchcustomer', 'uses' => 'ShowroomController@searchcustomer'));
	Route::get('showroom/showroom_response', ['as' => 'showroom.showroom_response', 'uses' => 'ShowroomController@showroom_response']);
	Route::post('showroom/storeproductids', 'ShowroomController@storeproductids');
	Route::get('showroom/generatecreditsalenote/{id}', 'ShowroomController@generatecreditsalenote')->name('generatecreditsalenote');
	Route::get('showroom/viewcreditpurchasenote/{id}', 'ShowroomController@viewcreditpurchasenote')->name('viewcreditpurchasenote');
	Route::get('showroom/viewcreditsalenote/{id}', 'ShowroomController@viewcreditsalenote')->name('viewcreditsalenote');
	Route::get('showroom/viewdebitpurchasenote/{id}', 'ShowroomController@viewdebitpurchasenote')->name('viewdebitpurchasenote');
	Route::get('showroom/viewdebitsalenote/{id}', 'ShowroomController@viewdebitsalenote')->name('viewdebitsalenote');

	Route::post('showroom/cancelbulkinvoice', 'ShowroomController@cancelbulkinvoice');
	Route::get('showroom/bulksalesreturn', ['as' => 'showroom.bulksalesreturn', 'uses' => 'ShowroomController@bulksalesreturn']);
	Route::post('showroom/generatebulksalesreturn', 'ShowroomController@generatebulksalesreturn');
	Route::get('showroom/salesreturnlist', ['as' => 'showroom.salesreturnlist', 'uses' => 'ShowroomController@salesreturnlist']);
	Route::post('showroom/generatesalesreturn', 'ShowroomController@generatesalesreturn');
	Route::get('showroom/salesreturn/{id}', 'ShowroomController@salesreturn')->name('salesreturn');
	Route::get('showroom/cancelinvoice/{id}', 'ShowroomController@cancelinvoice')->name('cancelinvoice');
	Route::post('showroom/soldinventoryajaxlist', 'ShowroomController@soldinventoryajaxlist');
	Route::post('showroom/approvalinventoryajaxlist', 'ShowroomController@approvalinventoryajaxlist');

	Route::get('showroom/allstock', ['as' => 'showroom.allstock', 'uses' => 'ShowroomController@allstock']);
	Route::get('showroom/soldinventory', ['as' => 'showroom.soldinventory', 'uses' => 'ShowroomController@soldinventory']);
	Route::get('showroom/approvalinventory', ['as' => 'showroom.approvalinventory', 'uses' => 'ShowroomController@approvalinventory']);
	Route::get('showroom/inventoryquery', ['as' => 'showroom.inventoryquery', 'uses' => 'ShowroomController@inventoryquery']);
	Route::get('showroom/product_list', ['as' => 'showroom.product_list', 'uses' => 'ShowroomController@product_list']);
	Route::post('showroom/showroomproductlist', ['as' => 'showroom.showroomproductlist', 'uses' => 'ShowroomController@showroomproductlist']);
	Route::get('showroom/pendingstock', ['as' => 'showroom.pendingstock', 'uses' => 'ShowroomController@pendingstock']);
	Route::get('showroom/showroominventory', ['as' => 'showroom.showroominventory', 'uses' => 'ShowroomController@showroominventory']);
	Route::post('showroom/pendingstockajaxlist', 'ShowroomController@pendingstockajaxlist');
	Route::post('showroom/showroominventoryajaxlist', 'ShowroomController@showroominventoryajaxlist');
	Route::post('showroom/getprominentfilter', 'ShowroomController@getprominentfilter');
	Route::post('showroom/ajaxlist', 'ShowroomController@ajaxlist');
	Route::post('showroom/processorder', array('as' => 'showroom.processorder', 'uses' => 'ShowroomController@processorder'));
	Route::post('showroom/placeorder', array('as' => 'showroom.placeorder', 'uses' => 'ShowroomController@placeorder'));
	Route::get('showroom/exportcsv', 'ShowroomController@exportcsv');
	Route::get('showroom/orderhistory', array('as' => 'showroom.orderhistory', 'uses' => 'ShowroomController@orderhistory'));
	Route::put('showroom/changeorderstatus/{id}', array('as' => 'showroom.changeorderstatus', 'uses' => 'ShowroomController@changeorderstatus'));
	Route::get('showroom/orderview/{id}', array('as' => 'showroom.orderview', 'uses' => 'ShowroomController@orderview'));
	Route::get('showroom/getCsv', 'ShowroomController@getCsv');
	Route::get('showroom/getSoldInventoryCsv', 'ShowroomController@getSoldInventoryCsv');

	Route::get('showroom/qrcodescanning', ['as' => 'showroom.qrcodescanning', 'uses' => 'ShowroomController@qrcodescanning']);
	Route::post('showroom/qrcodescanningajax', 'ShowroomController@qrcodescanningajax');
	Route::post('showroom/deletecertfromscanning', 'ShowroomController@deletecertfromscanning');
	Route::post('showroom/bulkdeletecertfromscanning', 'ShowroomController@bulkdeletecertfromscanning');
	Route::post('showroom/addtoscanninglist', array('as' => 'showroom.addtoscanninglist', 'uses' => 'ShowroomController@addtoscanninglist'));

/* General Settings */
	Route::get('settings', array('as' => 'settings', 'uses' => 'SettingController@index'));
	Route::post('settings/save', array('as' => 'settings.save', 'uses' => 'SettingController@setsettings'));

/* Customers route*/
	Route::post('customers/createnewcustomer', 'CustomersController@createnewcustomer');
	Route::post('customers/createpayment', 'CustomersController@createpayment');
	Route::post('customers/getpaymentparentheader', 'CustomersController@getpaymentparentheader');
	Route::post('customers/paymenttransactionajaxlist', 'CustomersController@paymenttransactionajaxlist');
	Route::post('customers/paymentajaxlist', 'CustomersController@paymentajaxlist');
	Route::post('customers/walletajaxlist', 'CustomersController@walletajaxlist');
	Route::post('customers/creditnoteajaxlist', 'CustomersController@creditnoteajaxlist');
	Route::post('customers/salesreturnajaxlist', 'CustomersController@salesreturnajaxlist');
	Route::get('customers/payment_detailresponse', array('as' => 'customers.payment_detailresponse', 'uses' => 'CustomersController@payment_detailresponse'));
	Route::post('customers/productdiscountstore', array('as' => 'customers.productdiscountstore', 'uses' => 'CustomersController@productdiscountstore'));

	Route::post('customers/invoicediscountstore', array('as' => 'customers.invoicediscountstore', 'uses' => 'CustomersController@invoicediscountstore'));

	Route::post('customers/discountstore', array('as' => 'customers.discountstore', 'uses' => 'CustomersController@discountstore'));
	Route::post('customers/getquotationcount', 'CustomersController@getquotationcount');
	Route::get('customers/view/{id}', array('as' => 'customers.view', 'uses' => 'CustomersController@view'));
	Route::get('customers/delete/{id}', array('as' => 'customers.delete', 'uses' => 'CustomersController@delete'));
	Route::post('customers/approvalmemoajaxlist', 'CustomersController@approvalmemoajaxlist');
	Route::post('customers/approvalproductsajaxlist', 'CustomersController@approvalproductsajaxlist');
	Route::post('customers/invoiceajaxlist', 'CustomersController@invoiceajaxlist');
	Route::post('customers/returnedproductajaxlist', 'CustomersController@returnedproductajaxlist');
	Route::post('customers/customerinventoryajaxlist', 'CustomersController@customerinventoryajaxlist');
	Route::post('customers/getdefaultbillingaddress', 'CustomersController@getdefaultbillingaddress');
	Route::post('customers/updatecustomeraddress', 'CustomersController@updatecustomeraddress');
	Route::post('customers/refreshdefaultaddress', 'CustomersController@refreshdefaultaddress');
	Route::post('customers/getcustomerattachment', 'CustomersController@getcustomerattachment');
	Route::post('customers/addcustomerattachment', 'CustomersController@addcustomerattachment');
	Route::post('customers/addcustomerpangstin', 'CustomersController@addcustomerpangstin');
	Route::post('customers/editgstinpancard', 'CustomersController@editgstinpancard');
	Route::post('customers/refreshgstinpancard', 'CustomersController@refreshgstinpancard');
	Route::post('customers/editpersonalinfo', 'CustomersController@editpersonalinfo');
	Route::post('customers/updatepersonalinfo', 'CustomersController@updatepersonalinfo');
	Route::post('customers/refreshpersonalinfo', 'CustomersController@refreshpersonalinfo');
	Route::post('customers/storepricemarkup', 'CustomersController@storepricemarkup');
/*Inventory route*/

	Route::post('inventory/updateinvoiceaddress', 'InventoryController@updateinvoiceaddress');
	Route::get('inventory/updateInvoiceProductStoneWeight', 'InventoryController@updateInvoiceProductStoneWeight');
	Route::get('inventory/insertsalesreturnmemoproducts', 'InventoryController@insertsalesreturnmemoproducts');
	Route::get('inventory/insertreturnmemoproducts', 'InventoryController@insertreturnmemoproducts');
	Route::get('inventory/insertreturnmemoproducts', 'InventoryController@insertreturnmemoproducts');
	Route::get('inventory/insertinvoiceproducts', 'InventoryController@insertinvoiceproducts');
	Route::post('inventory/updateinvoice', 'InventoryController@updateinvoice');
	Route::post('inventory/storeqrproductids', 'InventoryController@storeqrproductids');
	Route::post('inventory/checkinvoicenumber', 'InventoryController@checkinvoicenumber');
	Route::post('inventory/allstockajaxlist', 'InventoryController@allstockajaxlist');
	Route::get('inventory/getFranchisee', 'InventoryController@getFranchisee');
	Route::post('inventory/removememoproduct', 'InventoryController@removememoproduct');
	Route::post('inventory/removeinvoiceproduct', 'InventoryController@removeinvoiceproduct');
	Route::post('inventory/updateinvoicememo', 'InventoryController@updateinvoicememo');
	Route::get('inventory/deleteproduct/{id}', 'InventoryController@deleteproduct')->name('deleteproduct');
	Route::get('inventory/getcompletedinvoice', 'InventoryController@getcompletedinvoice');
	Route::get('inventory/getcanceledinvoice', 'InventoryController@getcanceledinvoice');
	Route::get('inventory/exportcsv', 'InventoryController@exportcsv');
	Route::get('inventory/exportmemoproductscsv', 'InventoryController@exportmemoproductscsv');

	Route::get('inventory/exportreturnmemoproductcsv', 'InventoryController@exportreturnmemoproductcsv')->name('exportreturnmemoproductcsv');
	Route::post('inventory/getapprovalidbyproduct', 'InventoryController@getapprovalidbyproduct');
	Route::post('inventory/getproductidsbyorder', 'InventoryController@getproductidsbyorder');
	Route::get('inventory/downloadexcel/{id}', 'InventoryController@downloadexcel')->name('downloadexcel');
	Route::get('inventory/downloadmemoproductexcel/{id}', 'InventoryController@downloadmemoproductexcel')->name('downloadmemoproductexcel');

	Route::get('inventory/downloadreturnmemoproduct/{id}', 'InventoryController@downloadreturnmemoproduct')->name('downloadreturnmemoproduct');

	Route::get('inventory/deliverychallan/{id}', 'InventoryController@deliverystatus')->name('deliverystatus');
	Route::get('inventory/viewinvoice/{id}', 'InventoryController@viewinvoice')->name('viewinvoice');
	Route::get('inventory/editinvoice/{id}', ['as' => 'inventory.editinvoice', 'uses' => 'InventoryController@editinvoice']);
	Route::get('inventory/cancelinvoice/{id}', 'InventoryController@cancelinvoice')->name('cancelinvoice');
	Route::get('inventory/downloadinvoiceexcel/{id}', 'InventoryController@downloadinvoiceexcel')->name('downloadinvoiceexcel');

	Route::get('inventory/viewreturnmemo/{id}', 'InventoryController@viewreturnmemo')->name('viewreturnmemo');
	Route::get('inventory/downloadproductexcel/{id}', 'InventoryController@downloadproductexcel')->name('downloadproductexcel');
	Route::get('inventory/inventoryproductlist', ['as' => 'inventory.inventoryproductlist', 'uses' => 'InventoryController@inventoryproductlist']);
	Route::post('inventory/generateapprovalmemo', 'InventoryController@generateapprovalmemo');
	Route::post('inventory/cancelapprovalmemo', 'InventoryController@cancelapprovalmemo');
	Route::post('inventory/deliverapprovalmemo', 'InventoryController@deliverapprovalmemo');

	Route::get('inventory/viewmemo/{id}', 'InventoryController@viewmemo')->name('viewmemo');
	//Route::get('inventory/editinvoice/{id}', 'InventoryController@editinvoice')->name('editinvoice');
	//Route::get('inventory/editmemo/{id}', 'InventoryController@editmemo')->name('editmemo');
	Route::get('inventory/editmemo/{id}', ['as' => 'inventory.editmemo', 'uses' => 'InventoryController@editmemo']);
	Route::get('inventory/stocktally', ['as' => 'inventory.stocktally', 'uses' => 'InventoryController@stocktally']);
	Route::get('inventory/stocktmp', ['as' => 'inventory.stocktmp', 'uses' => 'InventoryController@stocktmp']);
	Route::get('inventory/returnmemolist', ['as' => 'inventory.returnmemolist', 'uses' => 'InventoryController@returnmemolist']);
	Route::get('inventory/invoicelist', ['as' => 'inventory.invoicelist', 'uses' => 'InventoryController@invoicelist']);
	Route::get('inventory/memolist', ['as' => 'inventory.memolist', 'uses' => 'InventoryController@memolist']);

	Route::post('inventory/bulkrateupload', 'InventoryController@bulkrateupload');
	Route::post('inventory/getdefaultstoneprice', 'InventoryController@getdefaultstoneprice');
	Route::post('inventory/addproduct', 'InventoryController@addproduct');
	Route::get('inventory/getproductcertificate', 'InventoryController@getproductcertificate');
	Route::post('inventory/refreshstoneinfo', 'InventoryController@refreshstoneinfo');
	Route::post('inventory/getproductidsfornewproduct', 'InventoryController@getproductidsfornewproduct');
	Route::post('inventory/createcustomer', 'InventoryController@createcustomer');
	Route::post('inventory/getcustomerquotation', 'InventoryController@getcustomerquotation');
	Route::post('inventory/getproductids', 'InventoryController@getproductids');
	Route::post('inventory/storeeditproductids', 'InventoryController@storeeditproductids');

	Route::get('inventory/exportquotationexcel/{id}', 'InventoryController@exportquotationexcel')->name('exportquotationexcel');
	Route::get('customers/viewcustomerquotation/{id}', 'CustomersController@viewcustomerquotation')->name('viewcustomerquotation');
	Route::get('inventory/viewquotation/{id}', ['as' => 'inventory.viewquotation', 'uses' => 'InventoryController@viewquotation']);
	Route::get('inventory/deletequotation/{id}', 'InventoryController@deletequotation')->name('deletequotation');
	Route::get('inventory/index/{id}', 'InventoryController@index')->name('index');

	Route::post('inventory/storeproductids', 'InventoryController@storeproductids');
	Route::get('inventory/exhibitionlist', ['as' => 'inventory.exhibitionlist', 'uses' => 'InventoryController@exhibitionlist']);
	Route::get('inventory/quotationlist', ['as' => 'inventory.quotationlist', 'uses' => 'InventoryController@quotationlist']);
	Route::get('inventory/exportquotationexcel', 'InventoryController@exportquotationexcel');
	Route::post('inventory/getstatelist', 'InventoryController@getstatelist');
	Route::get('inventory/generatequotation', ['as' => 'inventory.generatequotation', 'uses' => 'InventoryController@generatequotation']);
	Route::post('inventory/checkcertificatelimit', 'InventoryController@checkcertificatelimit');
	Route::post('inventory/generatereturnmemo', 'InventoryController@generatereturnmemo');
	Route::post('inventory/checkcustomerexist', 'InventoryController@checkcustomerexist');
	Route::post('inventory/getproductidsforreturnmemo', 'InventoryController@getproductidsforreturnmemo');
	Route::post('inventory/getproductidsbyapproval', 'InventoryController@getproductidsbyapproval');
	Route::post('inventory/generateinvoicememo', 'InventoryController@generateinvoicememo');
	Route::post('inventory/processpreviewmemo', 'InventoryController@processpreviewmemo');
	Route::get('inventory/previewmemo', 'InventoryController@previewmemo');
	Route::post('inventory/getinvoicememomodalcontent', 'InventoryController@getinvoicememomodalcontent');
	Route::post('inventory/getexhibitionmodalcontent', 'InventoryController@getexhibitionmodalcontent');
	Route::get('inventory/editexhibition/{id}', 'InventoryController@editexhibition')->name('editexhibition');
	Route::get('inventory/viewexhibition/{id}', 'InventoryController@viewexhibition')->name('viewexhibition');
	Route::post('inventory/getexhibitiondetail', 'InventoryController@getexhibitiondetail');
	Route::post('inventory/updateexhibitiondata', 'InventoryController@updateexhibitiondata');
	Route::post('inventory/exhibitionproductajaxlist', 'InventoryController@exhibitionproductajaxlist');
	Route::post('inventory/storeexhibitiondata', 'InventoryController@storeexhibitiondata');
	Route::get('inventory/viewexhibitionproducts/{id}', 'InventoryController@viewexhibitionproducts')->name('viewexhibitionproducts');
	Route::get('inventory/generateexhibitionexcel/{id}', 'InventoryController@generateexhibitionexcel')->name('generateexhibitionexcel');
	Route::get('inventory/exportproductexcel', 'InventoryController@exportproductexcel');
	Route::get('inventory/exportproductpdf', 'InventoryController@exportproductpdf');
	Route::get('inventory/exportpdf', ['as' => 'inventory.exportpdf', 'uses' => 'InventoryController@exportpdf']);
	Route::get('inventory/exportproductcsv', 'InventoryController@exportproductcsv');
	Route::get('inventory/generateqrcode', 'InventoryController@generateqrcode');
	Route::post('inventory/printqrcode', ['as' => 'inventory.printqrcode', 'uses' => 'InventoryController@printqrcode']);
	Route::get('inventory/getinventoryproductcount', 'InventoryController@getinventoryproductcount');
	Route::post('inventory/refreshexhibitiondetail', 'InventoryController@refreshexhibitiondetail');
	Route::post('inventory/changeinventorystatus', 'InventoryController@changeinventorystatus');
	Route::post('inventory/changeinventorystatusandremovefromexhibition', 'InventoryController@changeinventorystatusandremovefromexhibition');
	Route::post('inventory/changeinventorystatusandremovefromorder', 'InventoryController@changeinventorystatusandremovefromorder');
	Route::post('inventory/ajaxlist', 'InventoryController@ajaxlist');
	Route::post('inventory/invoiceajaxlist', 'InventoryController@invoiceajaxlist');
	Route::post('inventory/getprominentfilter', 'InventoryController@getprominentfilter');
	Route::get('inventory/index', 'InventoryController@index');
	Route::get('inventory/qrinventorymanagement', 'InventoryController@qrinventorymanagement');
	Route::get('inventory/downloadapprovalcertificate', 'InventoryController@downloadapprovalcertificate');
	Route::post('inventory/storeapprovalproductids', 'InventoryController@storeapprovalproductids');
	Route::post('inventory/updateinvoiceprice', 'InventoryController@updateEditInvoiceProductPrice');
	Route::get('inventory/categorypdf', 'InventoryController@categorypdf');
	Route::post('customers/ajaxlist', 'CustomersController@ajaxlist');

	Route::resource('users', 'UserController');
/* General */
	Route::resource('demo', 'HomeController');
/* General */

/* Vendor  Managecharges Metalrates Producttype and Diamondtype */
	Route::get('vendor_details', ['as' => 'vendor.vendor_details', 'uses' => 'VendorController@vendor_details']);
	Route::post('vendor_details/vendor_detailresponse', 'VendorController@vendor_detailresponse');

	Route::post('vendor/vendoresponse', 'VendorController@vendoresponse');
	Route::post('metalrates/getmetaldata', 'MetalratesController@getmetaldata');
	Route::post('vendor-diamond-type/diamondrespose', 'DiamondTypeController@diamondrespose');
	Route::post('vendor-product-type/productresponse', 'ProductTypeController@productresponse');
	Route::post('managecharges/managechargesresponse', 'ManagechargesController@managechargesresponse');
	Route::post('metalrates/metalresponse', 'MetalratesController@metalresponse');
	Route::resource('vendor', 'VendorController');
	Route::resource('managecharges', 'ManagechargesController');
	Route::resource('vendor-product-type', 'ProductTypeController');
	Route::resource('vendor-diamond-type', 'DiamondTypeController');
	Route::resource('metalrates', 'MetalratesController');
/* Vendor  Managecharges Metalrates Producttype and Diamondtype */
	Route::get('roles/roleresponse', 'RoleController@roleresponse');
	Route::get('vendor/view/{id}', array('as' => 'vendor.view', 'uses' => 'VendorController@view'));
	Route::post('vendor/addvendorattachment', 'VendorController@addvendorattachment');
	Route::post('vendor/getvendorattachment', 'VendorController@getvendorattachment');
	Route::post('vendor/editgstinpancard', 'VendorController@editgstinpancard');
	Route::post('vendor/getaddress', 'VendorController@getaddress');
	Route::post('vendor/editpersonalinfo', 'VendorController@editpersonalinfo');
	Route::post('vendor/updatepersonalinfo', 'VendorController@updatepersonalinfo');
	Route::post('vendor/refreshpersonalinfo', 'VendorController@refreshpersonalinfo');
	Route::post('vendor/refreshdefaultaddress', 'VendorController@refreshdefaultaddress');
	Route::post('vendor/updatevendoraddress', 'VendorController@updatevendoraddress');
	Route::post('vendor/addvendorgstin', 'VendorController@addvendorgstin');
	Route::post('vendor/refreshgstin', 'VendorController@refreshgstin');
	Route::post('vendor/diamondissuelist', 'VendorController@diamondissuelist');
	Route::post('vendor/vendordiamondgivenajaxlist', 'VendorController@vendordiamondgivenajaxlist');
	Route::post('vendor/vendordiamondreturnajaxlist', 'VendorController@vendordiamondreturnajaxlist');
	Route::post('vendor/vendorgoldgivenajaxlist', 'VendorController@vendorgoldgivenajaxlist');
	Route::post('vendor/vendorgoldreturnajaxlist', 'VendorController@vendorgoldreturnajaxlist');
	Route::post('vendor/costingaacceptedajaxlist', 'VendorController@costingaacceptedajaxlist');
	Route::post('vendor/costingrejectedajaxlist', 'VendorController@costingrejectedajaxlist');
	Route::get('vendor/paidshow/{id}', ['as' => 'vendor.paidshow', 'uses' => 'VendorController@paidshow']);
	Route::post('vendor/vendorpaidtransactionresponse', 'VendorController@vendorpaidtransactionresponse');
	Route::get('vendor/vendorpaidtransaction', ['as' => 'vendor.vendorpaidtransaction', 'uses' => 'VendorController@vendor_paid_transaction']);
	Route::post('vendor/vendorpaidpayment_response', ['as' => 'vendor.vendorpaidpayment_response', 'uses' => 'VendorController@vendorpaidpayment_response']);
	Route::post('vendor/vendor_unpaidresponse', 'VendorController@vendor_unpaidresponse');
	Route::get('vendor/unpaidshow/{id}', ['as' => 'vendor.unpaidshow', 'uses' => 'VendorController@unpaidshow']);
	Route::get('vendor/vendorunpaidtransaction', ['as' => 'vendor.vendorunpaidtransaction', 'uses' => 'VendorController@vendor_paid_transaction']);
	Route::post('vendor/vendorunpaidtransactionresponse', 'VendorController@vendorunpaidtransactionresponse');
	Route::post('vendor/vendor_paymenthistresponse', 'VendorController@vendor_paymenthistresponse');
	Route::resource('roles', 'RoleController', ['names' => [
		'index' => 'roles.index',
		'create' => 'roles.create',
		'update' => 'roles.update',
		'edit' => 'roles.edit',
		'store' => 'roles.store',
		'show' => 'roles.show',
		'destroy' => 'roles.destroy',
	]]);

	Route::resource('permissions', 'PermissionController', ['names' => [
		'index' => 'permissions.index',
		'create' => 'permissions.create',
		'update' => 'permissions.update',
		'edit' => 'permissions.edit',
		'store' => 'permissions.store',
		'show' => 'permissions.show',
		'destroy' => 'permissions.destroy',
	]]);

	Route::resource('metaltransaction', 'MetalTransactionController', ['names' => [
		'update' => 'metaltransaction.update',
	]]);
	Route::get('gold-inventory/metalresponse', 'MetalController@goldresponse');
	Route::get('gold-inventory/filter_metal', 'MetalController@filter_metal');
	Route::get('gold-inventory/goldissue', ['as' => 'metals.goldissue', 'uses' => 'MetalController@goldissue']);
	Route::post('gold-inventory/goldissuestore', ['as' => 'metals.goldissuestore', 'uses' => 'MetalController@goldissuestore']);

	Route::resource('gold-inventory', 'MetalController', ['names' => [
		'index' => 'metals.index',
		'create' => 'metals.create',
		'store' => 'metals.store',
		'show' => 'metals.transactions',
		'destroy' => 'metals.destroy',
	]]);
	Route::get('diamondraw/filter_issue_voucher', 'DiamondRawController@filter_issue_voucher');

	Route::get('metals/returnGoldIssue', ['as' => 'metals.returnGoldIssue', 'uses' => 'MetalController@returnGoldIssue']);

	Route::get('diamond/returnDiamondIssue', ['as' => 'diamond.returnDiamondIssue', 'uses' => 'DiamondController@returnDiamondIssue']);

	Route::get('diamond/returnDiamondIssue', ['as' => 'diamond.returnDiamondIssue', 'uses' => 'DiamondController@returnDiamondIssue']);

	Route::post('diamond/returndiamondIssueStore', 'DiamondController@returndiamondIssueStore');
	Route::get('diamond/voucherPreview', 'DiamondController@voucherPreview');
	Route::get('diamond/handover', 'DiamondController@handover');
	Route::get('metal/goldhandover', 'MetalController@goldhandover');
	Route::get('diamond/deleteVoucher', 'DiamondController@deleteVoucher');

	Route::get('metals/goldPreview', 'MetalController@goldPreview');
	Route::get('metal/deleteGoldVoucher', 'MetalController@deleteGoldVoucher');
	Route::post('metals/returngoldIssueStore', ['as' => 'metals.returngoldIssueStore', 'uses' => 'MetalController@returngoldIssueStore']);

	Route::get('metals/updategoldissuevoucher', ['as' => 'metals.updategoldissuevoucher', 'uses' => 'MetalController@updategoldissuevoucher']);

	Route::get('diamond/edit_issue_voucher/', ['as' => 'diamond.edit_issue_voucher', 'uses' => 'DiamondController@edit_issue_voucher']);
	Route::get('diamondraw/voucher_download', array('as' => 'diamondraw.voucher_download', 'uses' => 'DiamondRawController@voucher_download'));
	Route::get('metals/edit_gold_issue_voucher/', ['as' => 'metals.edit_gold_issue_voucher', 'uses' => 'MetalController@edit_gold_issue_voucher']);
	Route::get('diamondraw/issue_voucher_list', 'DiamondRawController@issue_voucher_list');
	Route::get('diamondraw/downloadmemo', 'DiamondRawController@downloadmemo');
	Route::get('diamondraw/Return-voucher', 'DiamondRawController@viewinvoice');
	Route::get('diamondraw/returnmemo', 'DiamondRawController@returnmemo');
	Route::post('diamondraw/sizing_response', 'DiamondRawController@sizing_response');
	Route::get('diamondraw/sizinglist', array('as' => 'diamondraw.sizinglist', 'uses' => 'DiamondRawController@sizing_list'));
	Route::get('diamondraw/sizing', 'DiamondRawController@Sizing');
	Route::post('diamondraw/sizing_transaction', 'DiamondRawController@sizing_transaction');
	Route::post('diamondraw/assorting_response', 'DiamondRawController@assorting_response');
	Route::get('diamondraw/assortinglist', array('as' => 'diamondraw.assortinglist', 'uses' => 'DiamondRawController@assorting_list'));
	Route::get('diamondraw/assorting', 'DiamondRawController@assorting');
	Route::post('diamondraw/assorting_transaction', 'DiamondRawController@assorting_transaction');
	Route::post('diamondraw/cvd_response', 'DiamondRawController@cvd_response');
	Route::get('diamondraw/cvd-list', array('as' => 'diamondraw.cvd-list', 'uses' => 'DiamondRawController@cvd_list'));
	Route::get('diamondraw/cvdcvdcalculation', 'DiamondRawController@cvdcalculation');
	Route::post('diamondraw/cvd_transaction', 'DiamondRawController@cvd_transaction');
	Route::post('diamondraw/diamondrawrespose', 'DiamondRawController@diamondrawrespose');
	Route::get('diamondraw/CVD', 'DiamondRawController@CVD');
	Route::get('diamond/generateVoucherno', 'DiamondController@generateVoucherno');
	Route::get('metal/generateGoldVoucherno', 'MetalController@generateGoldVoucherno');

	Route::resource('diamondraw', 'DiamondRawController', ['names' => [
		'index' => 'diamondraw.index',
		'create' => 'diamondraw.create',
		'edit' => 'diamondraw.edit',
		'update' => 'diamondraw.update',
		'store' => 'diamondraw.store',
		'destroy' => 'diamondraw.destroy',

	]]);

/*Route::get('diamond-inventory/diamondresponse', ['as' => 'diamond.diamondresponse', 'uses' => 'DiamondController@diamondresponse']);*/

	Route::get('diamond-inventory/diamondinvoicelist', ['as' => 'diamond.diamondinvoicelist', 'uses' => 'DiamondController@diamondinvoicelist']);
	Route::get('diamond-inventory/diamondinvoicedata', ['as' => 'diamond.diamondinvoicedata', 'uses' => 'DiamondController@diamondinvoicedata']);
	Route::get('diamond-inventory/filter_diamond', ['as' => 'diamond.filter_diamond', 'uses' => 'DiamondController@filter_diamond']);
/*Route::get('diamond/create/{id}', array('as' => 'diamond.create', 'uses' => 'DiamondController@create'));*/
	Route::get('diamond-inventory/reset', ['as' => 'diamond.reset', 'uses' => 'DiamondController@reset']);
	Route::get('diamond-inventory/filter_search', ['as' => 'diamond.filter_search', 'uses' => 'DiamondController@filter_search']);
	Route::get('diamond-inventory/diamond_issue_vaucher', ['as' => 'diamond.diamond_issue_vaucher', 'uses' => 'DiamondController@diamond_issue_vaucher']);
	Route::get('diamond-inventory/diamond_invoice', ['as' => 'diamond.diamond_invoice', 'uses' => 'DiamondController@diamond_invoice']);
	Route::get('diamond-inventory/create/{id}', ['as' => 'diamondmovetoinventory.create', 'uses' => 'DiamondController@create']);

	Route::get('diamond-inventory/creatediamonds', ['as' => 'diamondinventory.createnew', 'uses' => 'DiamondController@createnew']);
	Route::post('diamond-inventory/creatediamonds', ['as' => 'diamond.createnew', 'uses' => 'DiamondController@createnew']);

	Route::post('diamond-inventory/storediamonds', ['as' => 'diamond.storediamonds', 'uses' => 'DiamondController@storediamonds']);

	Route::resource('diamond-inventory', 'DiamondController', ['names' => [
		'index' => 'diamond.index',
		'create' => 'diamond.create',
		'edit' => 'diamond.edit',
		'update' => 'diamond.update',
		'store' => 'diamond.store',
		'show' => 'diamond.transactions',
		'destroy' => 'diamond.destroy',
		//'diamondmiscloss' => 'diamond.diamondmiscloss',

	]]);

	Route::get('diamond/generatediamondinvoice/', ['as' => 'diamond.generatediamondinvoice', 'uses' => 'DiamondController@generatediamondinvoice']);
	Route::get('diamond/diamondinvoice/', ['as' => 'diamond.diamondinvoice', 'uses' => 'DiamondController@diamondinvoice']);
	Route::get('diamond/diamond_invoice_download/', ['as' => 'diamond.diamond_invoice_download', 'uses' => 'DiamondController@diamond_invoice_download']);

	Route::get('diamond/diamondinvoicepdf', ['as' => 'diamond.diamondinvoicepdf', 'uses' => 'DiamondController@diamondinvoicepdf']);

	Route::post('diamond/createcustomer', ['as' => 'diamond.createcustomer', 'uses' => 'DiamondController@createcustomer']);

	Route::post('diamond-inventory/generatediamondinvoicestore', ['as' => 'diamond.generatediamondinvoicestore', 'uses' => 'DiamondController@generatediamondinvoicestore']);
	Route::post('diamond/getdiamondprice', 'DiamondController@getdiamondprice');

	Route::post('diamond/editissuevoucher', ['as' => 'diamond.editissuevoucher', 'uses' => 'DiamondController@editissuevoucher']);
	Route::get('diamond/diamondissue/weightsearch', 'DiamondController@searchweight');

	Route::get('diamond/diamondmiscloss/{id}', ['as' => 'diamond.diamondmiscloss', 'uses' => 'DiamondController@diamondmiscloss']);
	Route::post('diamond/diamondmisclossstore', ['as' => 'diamond.diamondmisclossstore', 'uses' => 'DiamondController@diamondmisclossstore']);
//Route::post('diamond/diamondmisclossstore/', 'DiamondController@diamondmisclossstore');
	//Route::post('costing', 'CostingController@store');
	Route::post('diamond/diamondissuestore', ['as' => 'diamond.diamondissuestore', 'uses' => 'DiamondController@diamondissuestore']);
	Route::get('diamond/diamondIssueCheck', ['as' => 'diamond.diamondIssueCheck', 'uses' => 'DiamondController@diamondIssueCheck']);
	Route::get('diamond/diamondissue', ['as' => 'diamond.diamondissue', 'uses' => 'DiamondController@diamondissue']);

	Route::get('diamond/importexcel', ['as' => 'diamond.importexcel', 'uses' => 'DiamondController@importexcel']);
	Route::get('diamond/invoiceattachment', ['as' => 'diamond.invoiceattachment', 'uses' => 'DiamondController@invoiceattachment']);
	Route::post('diamond/importexceldata', ['as' => 'diamond.importexceldata', 'uses' => 'DiamondController@importexceldata']);
	Route::post('diamond/multiplefileupload', 'DiamondController@multiplefileupload');
	Route::post('diamond/invoiceattachmentResponse', 'DiamondController@invoiceattachmentResponse');

	Route::get('purchase-history/filter_history', 'PurchaseHistoryController@filter_history');
	Route::get('purchase-history-gold/{id}', array('as' => 'purchasehistory.metaldetails', 'uses' => 'PurchaseHistoryController@golddetails'));
	Route::get('purchase-history-diamond/{id}', array('as' => 'purchasehistory.diamonddetails', 'uses' => 'PurchaseHistoryController@diamonddetails'));
	Route::get('purchase-history/{id}/editgold', array('as' => 'purchasehistory.editgold', 'uses' => 'PurchaseHistoryController@editgold'));

	Route::patch('purchase-history/{id}/updategold', array('as' => 'purchasehistory.updategold', 'uses' => 'PurchaseHistoryController@updategold'));

	Route::patch('purchase-history/{id}/updatediamond', array('as' => 'purchasehistory.updatediamond', 'uses' => 'PurchaseHistoryController@updatediamond'));

	Route::get('purchase-history/{id}/editdiamond', array('as' => 'purchasehistory.editdiamond', 'uses' => 'PurchaseHistoryController@editdiamond'));

	Route::resource('purchase-history', 'PurchaseHistoryController', ['names' => [
		'index' => 'purchasehistory.index',
		'show' => 'purchasehistory.show',
	]]);
	//Route::get('purchase-history/filter_history', ['as' => 'purchasehistory', 'uses' => 'PurchaseHistoryController@filter_history']);

	Route::post('diamond/transactiontype_filter', 'TransactionTypeController@transactiontyperesponse');
	Route::get('diamond/diamond_statistics_by_mm', ['as' => 'diamond.diamond_statistics_by_mm', 'uses' => 'DiamondController@diamondstatisticsbymm']);
	Route::get('diamond/filter_diamond_statistics', ['as' => 'diamond.filter_diamond_statistics', 'uses' => 'DiamondController@filter_diamond_statistics']);
	Route::resource('transaction-type', 'TransactionTypeController', ['names' => [
		'index' => 'transactiontype.index',
		'create' => 'transactiontype.create',
		'edit' => 'transactiontype.edit',
		'update' => 'transactiontype.update',
		'store' => 'transactiontype.store',
		'show' => 'transactiontype.transactions',
		'destroy' => 'transactiontype.destroy',
	]]);
/* Gold & Diamond Inventory */

/* Virtual Box Manager */
	Route::get('virtualboxmanager/{id}/editvb', array('as' => 'virtualboxmanager.editvb', 'uses' => 'VirtualBoxManagerController@editvb'));
	Route::post('virtualboxmanager/ajaxgetrange', 'VirtualBoxManagerController@ajaxgetrange');
	Route::get('virtualboxmanager/showDetail', 'VirtualBoxManagerController@showDetail');
	Route::post('virtualboxmanager/ajaxvb', 'VirtualBoxManagerController@ajaxvb');
	Route::post('virtualboxmanager/storemoveproducts', ['as' => 'virtualboxmanager.storemoveproducts', 'uses' => 'VirtualBoxManagerController@storemoveproducts']);
	Route::get('virtualboxmanager/moveproducts', ['as' => 'virtualboxmanager.moveproducts', 'uses' => 'VirtualBoxManagerController@moveproducts']);
	Route::get('virtualboxmanager/vbboxlist', ['as' => 'virtualboxmanager.vbboxlist', 'uses' => 'VirtualBoxManagerController@vbboxlist']);
	Route::resource('virtualboxmanager', 'VirtualBoxManagerController', ['names' => [
		'create' => 'virtualboxmanager.create',
		'store' => 'virtualboxmanager.store',
		'index' => 'virtualboxmanager.index',
		'edit' => 'virtualboxmanager.edit',
		'update' => 'virtualboxmanager.update',
		'show' => 'virtualboxmanager.show',
		'destroy' => 'virtualboxmanager.destroy',
	]]);

/* Virtual Box Manager - End */

// * Product upload */
	Route::get('productupload/create', ['as' => 'productupload.create', 'uses' => 'ProductuploadController@create']);
	Route::post('productupload', 'ProductuploadController@store');
	Route::get('productupload/index', ['as' => 'productupload.index', 'uses' => 'ProductuploadController@index']);
	Route::get('productupload/indexResponse', 'ProductuploadController@indexResponse');
	//Route::get('productupload/deleteproductmultiple','ProductuploadController@deleteproduct')
	//* Costing */
	Route::get('costing/costingproductlist', ['as' => 'costing.costingproductlist', 'uses' => 'CostingController@costingproductlist']);
	Route::get('costing/product_list', ['as' => 'costing.product_list', 'uses' => 'CostingController@product_list']);
	Route::get('costing/changeIGIStatus', 'CostingController@changeIGIStatus');
	Route::get('costing/create', 'CostingController@create');
	Route::post('costing', 'CostingController@store');
	Route::get('costing/getmsg', 'CostingController@index');
	Route::get('costing/loadvendorstonehtml', 'CostingController@loadvendorstonehtml');
	Route::get('costing/loadvendor_others_stonehtml', 'CostingController@loadvendor_others_stonehtml');
	Route::get('costing/getStonePrice', 'CostingController@getStonePrice');
	Route::get('costing/exportexcel', 'CostingController@exportexcel');
	Route::get('costing/exportpdf', 'CostingController@exportpdf');
	Route::get('costing/changeQcStatus', 'CostingController@changeQcStatus');
	Route::get('costing/addProducts', 'CostingController@addProducts');
	Route::get('costing/changeQcStatusMultiple', 'CostingController@changeQcStatusMultiple');
/*Route::get('costing/qcaccept', 'CostingController@qcaccept');*/
	Route::get('costing/qcreject', 'CostingController@qcreject');
	Route::get('costing/showDetail', 'CostingController@showDetail');
	Route::get('costing/generateIGI', 'CostingController@generateIGI');
	Route::get('costing/generateCertificate', 'CostingController@generateCertificate');
/*Route::get('costing/IGIlist','CostingController@IGIlist');*/
	Route::get('costing/requestinvoiceByQc', 'CostingController@requestinvoiceByQc');
	Route::get('costing/returnmemoByQc', 'CostingController@returnmemoByQc');
	Route::get('costing/getHandligCharges', 'CostingController@getHandligCharges');
	Route::get('costing/loadvendorhandlingcharges', 'CostingController@loadvendorhandlingcharges');
	Route::get('costing/invoicepdf', 'CostingController@invoicepdf');
	Route::get('costing/memopdf', 'CostingController@memopdf');
	Route::get('costing/qcacceptResponse', 'CostingController@qcacceptResponse');
	Route::get('costing/qcrejectResponse', 'CostingController@qcrejectResponse');
	Route::get('costing/qcigiResponse', 'CostingController@qcigiResponse');
	Route::get('costing/costinglistResponse', 'CostingController@costinglistResponse');
	Route::get('costing/costinglogResponse', 'CostingController@costinglogResponse');
	Route::get('costing/qcrequestinvoiceResponse', 'CostingController@qcrequestinvoiceResponse');
	Route::get('costing/qcreturnmemoResponse', 'CostingController@qcreturnmemoResponse');
	Route::get('costing/qccount', 'CostingController@qccount');
	Route::post('costing/productlistResponse', 'CostingController@productlistResponse');

	Route::get('productupload/updateproduct', ['as' => 'productupload.updateproduct', 'uses' => 'ProductuploadController@updateproduct']);
	Route::get('productupload/deleteproduct', ['as' => 'productupload.deleteproduct', 'uses' => 'ProductuploadController@deleteproduct']);
	//Route::post('productupload/updateproductstore', 'ProductuploadController@updateproductstore');
	Route::post('productupload/updateproductstore', ['as' => 'productupload.updateproductstore', 'uses' => 'ProductuploadController@updateproductstore']);
	Route::get('costing/costinglist', ['as' => 'costing.costinglist', 'uses' => 'CostingController@costinglist']);
	Route::get('costing/costinglog', ['as' => 'costing.costinglog', 'uses' => 'CostingController@costinglog']);
	Route::get('costing/qcrequestinvoice', ['as' => 'costing.qcrequestinvoice', 'uses' => 'CostingController@qcrequestinvoice']);
	Route::get('costing/qcreturnmemo', ['as' => 'costing.qcreturnmemo', 'uses' => 'CostingController@qcreturnmemo']);
	Route::get('costing/IGIlist', ['as' => 'costing.IGIlist', 'uses' => 'CostingController@IGIlist']);
	Route::get('costing/qcaccept', ['as' => 'costing.qcaccept', 'uses' => 'CostingController@qcaccept']);
	Route::get('costing/acceptedProductExcel', 'CostingController@acceptedProductExcel');
	Route::resource('costing', 'CostingController', ['names' => [
		'create' => 'costing.create',
	]]);
/* Costing */

/* Add Payment Type  */

	Route::resource('account/payment-types', 'PaymentTypeController', ['names' => [
		'index' => 'paymenttype.index',
		'create' => 'paymenttype.create',
		'edit' => 'paymenttype.edit',
		'store' => 'paymenttype.store',
		'show' => 'paymenttype.show',
		'destroy' => 'paymenttype.destroy',
	]]);
/* Add Payment Type  */

/* Account & Payment */
/*	Route::post('account/payments#past_due', ['as' => 'accountpayment.past_due', 'uses' => 'PaymentController@index']);
Route::post('account/payments#future_due', ['as' => 'accountpayment.future_due', 'uses' => 'PaymentController@index']);*/
	Route::get('account/payments/pdflisting', ['as' => 'accountpayment.pdflisting', 'uses' => 'PaymentController@pdflisting']);
	Route::resource('account/payments', 'PaymentController', ['names' => [
		'index' => 'accountpayment.index',
		'create' => 'accountpayment.create',
		'edit' => 'accountpayment.edit',
		'update' => 'accountpayment.update',
		'store' => 'accountpayment.store',
		'show' => 'accountpayment.show',
		'destroy' => 'accountpayment.destroy',
		'outgoing' => 'accountpayment.outgoing',

	]]);
/* Account & Payment */

// [[ACCOUNT DEPT]] ///

/* showroom */
	Route::resource('showroom', 'ShowroomController', ['names' => [
		'index' => 'showroom.index',
		'create' => 'showroom.create',
		'edit' => 'showroom.edit',
		'update' => 'showroom.update',
		'store' => 'showroom.store',
		'show' => 'showroom.show',
		'destroy' => 'showroom.destroy',
	]]);

/*Showroom route end*/

/*Invntory route start*/
	Route::resource('inventory', 'InventoryController', ['names' => [
		'store' => 'inventory.store',
		'edit' => 'inventory.editquotation',
		'destroy' => 'inventory.deletequotation',
		//'show' => 'inventory.viewquotation',
		'quotationlist' => 'inventory.quotationlist',
	]]);
/*Invntory route end*/

/*Customers route start*/
	Route::resource('customers', 'CustomersController');
/*Customers route end*/

	Route::post('users/ajaxlist', 'UserController@ajaxlist');

});

/*

Photoshop Product COntroller 
*/

Route::get('Photoshop/Product/list','PhotoshopProductController@list_of_product')->name('product_list');
Route::get('Photoshop/Product/add','PhotoshopProductController@add_of_product')->name('product_add');
Route::post('Photoshop/Product/list','PhotoshopProductController@list_of_product_filter')->name('product_list');
Route::post('Photoshop/Product/upload','PhotoshopProductController@upload_csv_product')->name('upload_csv');
Route::get('Photoshop/Product/delete','PhotoshopProductController@delete_product')->name('photography.product.delete');
Route::get('Photoshop/Product/view/{id}','PhotoshopProductController@get_product_detail')->name('product.view');
/*
Photoshop department
*/
Route::get('Photoshop/Photography','PhotoshopController@index')->name('photography.index');
Route::post('Photoshop/Photography/pending','PhotoshopController@pending_list_submit');
Route::get('Photoshop/Photography/pending','PhotoshopController@get_pending_list')->name('photography.pending');

Route::get('Photoshop/Photography/done','PhotoshopController@get_done_list')->name('photography.done');
Route::post('Photoshop/Photography/done','PhotoshopController@submit_done_list')->name('photography.done');
Route::get('Photoshop/Photography/rework','PhotoshopController@get_rework_list')->name('photography.rework');
Route::post('Photoshop/Photography/rework','PhotoshopController@submit_done_list')->name('photography.rework');



/*
psd department Routing
*/
Route::get('Photoshop/psd','PsdController@index')->name('psd.index');
Route::post('Photoshop/psd/pending','PsdController@get_data_from_psd_pending_list')->name('psd.pending');
Route::get('Photoshop/psd/pending','PsdController@get_psd_pending_list')->name('psd.pending');
Route::get('Photoshop/psd/done','PsdController@get_psd_done_list')->name('psd.done');
Route::post('Photoshop/psd/done','PsdController@submit_done_list')->name('psd.done');
Route::get('Photoshop/psd/rework','PsdController@get_psd_rework_list')->name('psd.rework');
Route::post('Photoshop/psd/rework','PsdController@submit_done_list')->name('psd.rework');

//Placement Routing

Route::get('Photoshop/Placement/pending','PlacementController@get_placement_pending_list')->name('placement_pending');
Route::get('Photoshop/Placement/done','PlacementController@get_placement_done_list')->name('placement_done');
Route::get('Photoshop/Placement/rework','PlacementController@get_placement_rework_list')->name('placement_rework');
Route::post('Photoshop/Placement/pending','PlacementController@get_pending_list_data_submit')->name('placement_pending_submit');
Route::post('Photoshop/Placement/done','PlacementController@submit_done_list')->name('placement_done');
Route::post('Photoshop/Placement/rework','PlacementController@submit_done_list')->name('placement_done');
/*
Editing Department Routing
*/
Route::get('Photoshop/Editing/pending','EditingController@get_pending_list_editing')->name('editing.pending');
Route::get('Photoshop/Editing/done','EditingController@get_done_list_editng')->name('editing.done');
Route::get('Photoshop/Editing/rework','EditingController@get_rework_list_editing')->name('editing.rework');
Route::post('Photoshop/Editing/pending','EditingController@get_pending_submit_editing')->name('editing.pending');
Route::post('Photoshop/Editing/done','EditingController@submit_done_list_editng')->name('editing.done');
Route::post('Photoshop/Editing/rework','EditingController@submit_done_list_editng')->name('editing.rework');
/*
JPEG Department Routing
*/
Route::get('Photoshop/JPEG/pending','JpegController@get_pending_list_jpeg')->name('jpeg.pending');
Route::post('Photoshop/JPEG/pending','JpegController@submit_pending_list_jpeg')->name('jpeg.pending');
Route::get('Photoshop/JPEG/done','JpegController@get_done_list_jpeg')->name('jpeg.done');
Route::post('Photoshop/JPEG/done','JpegController@submit_done_list_jpeg')->name('jpeg.done');
Route::get('Photoshop/JPEG/rework','JpegController@get_rework_list_jpeg')->name('jpeg.rework');
Route::post('Photoshop/JPEG/rework','JpegController@submit_done_list_jpeg')->name('jpeg.rework');


