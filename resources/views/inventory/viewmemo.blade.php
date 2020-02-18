<?php
use App\Helpers\CustomersHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
?>
<head>
	<title>Print Memo | Dealermela</title>
	<link rel="stylesheet" href="http://123.108.51.11/skin/frontend/rwdnew/default/css/styles.css" />
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i,900,900i" rel="stylesheet" />
</head>
<?php
$customerId = isset($memoData->customer_id) ? $memoData->customer_id : '';
$customerGroup = InventoryHelper::getCustomerGroup($customerId);
$customerGroupId = isset($customerGroup->customer_group_id) ? $customerGroup->customer_group_id : '';
$customerName = InventoryHelper::getCustomerName($memoData->customer_id);
$customerBillingAddress = InventoryHelper::getDefaultBillingAddressByCustId($customerId);
$street = isset($customerBillingAddress['street']) ? $customerBillingAddress['street'] : '';
$city = isset($customerBillingAddress['city']) ? $customerBillingAddress['city'] : '';
$region = isset($customerBillingAddress['region']) ? $customerBillingAddress['region'] : '';
$postcode = isset($customerBillingAddress['postcode']) ? $customerBillingAddress['postcode'] : '';
$telephone = isset($customerBillingAddress['telephone']) ? $customerBillingAddress['telephone'] : '';
$panCardNumber = isset($customerBillingAddress['pancard_number']) ? $customerBillingAddress['pancard_number'] : '';
$gstinNumber = CustomersHelper::getCustomerAttrValue($customerId, 'gstin');
$gstinNumber = !empty($gstinNumber) ? $gstinNumber : '';
$currentYear = date('y', strtotime($memoData->created_at));
if (date('m') > 6) {
	$fin_year = date('y') . '-' . (date('y') + 1);
} else {
	$fin_year = (date('y') - 1) . '-' . date('y');
}
$approvalNumber = $fin_year . '/' . $memoData->approval_no;

if (isset($memoData->is_for_old_data) && $memoData->is_for_old_data == 'yes') {
	$approvalNumber = $memoData->approval_no;
}
if (empty($memoData->approval_no)) {
	$approvalNumber = 'N/A';
}
$memoProductsIds = DB::table('approval_memo_histroy')->select('product_id')->where('approval_memo_id', '=', DB::raw("'$memoData->id'"))->get();
foreach ($memoProductsIds as $productId) {
	$productIds[] = $productId->product_id;
}
$logoname = Config::get('constants.pdf_logo.name');
$totalProductsCount = count($productIds);
?>
<div class="print-container">
	<section class="franchises-portal">
		<div class="print-head">
	        <img src="{{ URL::to('/') }}/img/memo_logo.png" class="logo" alt="">
	        <p><?php echo '<b>' . $logoname . '</b>'; ?></p>
	    </div>
	    <section class="memo">
	    	<div class="memo-head">
		       <h1>DIAMOND MELA JEWELS LTD</h1>

		       <h1>Regd: 2307 PANCHRATNA BUILDING OPERA HOUSE,MUMBAI-400004.</h1>
		       <a>GSTIN: 27AAFCD2233A1ZB</a>
		       <a>TEL:022-49664966</a>
		    </div>
		    <div class="memo-delivery">
		    	<h2>DELIVERY CUM APPROVAL MEMO</h2>
			    <h4>(Returnable goods Not for Sale)</h4>
			    <div class="address">
			        <p class="margin-bottom-0">To,</p>

			    </div>
			    <div class="address2">
			    	<?php
//$invoice = InventoryHelper::getInvoiceData($order->entity_id);

?>
			    </div>
			    <table class="Print_memo_left">
			    	<tr>
			          	<th>Name: </th>
			          	<td><?php echo strtoupper($customerName); ?></td>
			        </tr>
			        <tr>
			          	<th>Address:</th>
			          	<td><?php echo strtoupper($street); ?></td>
			        </tr>
			        <tr>
			          	<th>City:</th>
			          	<td><?php echo strtoupper($city); ?></td>
			        </tr>
			        <tr>
			          	<th>Pin code:</th>
			          	<td><?php echo $postcode; ?></td>
			        </tr>
			        <tr>
			          	<th>State:</th>
			          	<td><?php echo strtoupper($region); ?></td>
			        </tr>
			        <tr>
			          	<th>Pan:</th>
			          	<td><?php echo $panCardNumber; ?></td>
			        </tr>
			        <tr>
			          	<th>Contact No:</th>
			          	<td><?php echo $telephone; ?></td>
			        </tr>
			        <tr>
			          	<th>GSTIN :</th>
			          	<td><?php echo $gstinNumber; ?></td>
			        </tr>
			    </table>
			    <table class="Print_memo_right">
			    	<tr>
			          	<th>Approval No:</th>
			          	<td><?php echo $approvalNumber; ?></td>
			        </tr>
			        <tr>
			          	<th>Date:</th>
			          	<?php $orderDate = date("M d, Y", strtotime($memoData->created_at));?>
			          	<td><?php echo $orderDate ?></td>
			        </tr>
			        <tr>
			          	<th>HSN CODE: : </th>
			          	<td><?php echo "71131930"; ?></td>
			        </tr>
			    </table>
			    <div class="Print_memo_list">
			    	<ul>
				        <p><b>We hereby to entrust to you following goods for the inspection on approval basis with following condition:</b></p>
				        <li>1. The Goods remain our property.</li>
				        <li>2. You will not sell or pledge or mortgage the said goods or otherwise deal with them in
     any manner which is prejudicial to our right</li>
				        <li>3. The Goods will be returned to us forthwith on Demand.</li>
				        <li>4. The Goods will be held while in your custody at your risk in all respects.</li>
				        <li>5. You will be responsible for the return of this said goods in the same condition in which it was given to you</li>
				    </ul>
			    </div>
			    <table class="Print_memo_middle">
			    	<tr>
			          	<th>Product</th>
			          	<th>Kt</th>
			          	<th>Qty</th>
			          	<th>Diamond wt</th>
			          	<th>Metal wt</th>
			          	<th>Price</th>
			        </tr>
			        <?php

$arrayitem = array();
$totalQty = 0;
$productMetalQuality = '';
$totalStoneWeight = 0;
$totalGrandTotalPrice = 0;
$totalMetalWeight = 0;
//$orderItems = InventoryHelper::getOrderItems($order->entity_id);
//$productIds = explode(',',$memoData->product_ids);
/* echo "<pre>";
print_r($productIds);exit; */

$productIds = array_filter($productIds);
foreach ($productIds as $key => $productId) {
	$diamondweight = 0;
	DB::setTablePrefix('');

	$product = DB::table("catalog_product_flat_1")->select("*")->where("entity_id", "=", DB::raw("$productId"))->get()->first();

	if (!empty($product->entity_id)) {
		$attributeSetId = $product->attribute_set_id;
		$attributeSetData = DB::table("eav_attribute_set")->select("attribute_set_name")->where("attribute_set_id", "=", DB::raw("$attributeSetId"))->get()->first();
		$attributeSetName = isset($attributeSetData->attribute_set_name) ? $attributeSetData->attribute_set_name : '';
		$metalQuality = DB::table("grp_metal_quality")->select("metal_quality")->where("grp_metal_quality_id", "=", $product->metal_quality)->get()->first();
		$metalQuality = $metalQuality->metal_quality;
		$productQty = 1;
		$price = isset($product->custom_price) ? $product->custom_price : '';
		$stone = isset($product->rts_stone_quality) ? $product->rts_stone_quality : '';
		$stoneData = InventoryHelper::getStoneData($product->entity_id, $stone);
		$gemStoneData = InventoryHelper::getGemStoneData($product->entity_id);
		$diaweight = isset($stoneData['totalcts']) ? $stoneData['totalcts'] : '';
		foreach ($diaweight as $weight) {
			$diamondweight += $weight;
		}
		/* echo "<pre>";
							print_r($stoneData);exit; */
		$stoneWeight = $diamondweight;
		$metalData = ShowroomHelper::getMetalData($product->entity_id, $product);
		$metalWeight = isset($metalData['weight']) ? $metalData['weight'] : '';
		$totalQty += $productQty;
		$productMetalQuality = isset($metalData['quality']) ? $metalData['quality'] : '';
		$totalStoneWeight += $stoneWeight;
		$totalMetalWeight += $metalWeight;
		$totalGrandTotalPrice += $price;
		$finalGrandTotalPrice = ShowroomHelper::currencyFormat(round($totalGrandTotalPrice));
		$arrayitem[$attributeSetName]['attr'] = $attributeSetName;
		$arrayitem[$attributeSetName]['met'] = $productMetalQuality;
		$qty = isset($arrayitem[$attributeSetName]['qty']) ? intval($arrayitem[$attributeSetName]['qty']) : 0;
		$arrayitem[$attributeSetName]['qty'] = $qty + round($productQty, 0);
		$totalProductsCount++;
		$arrayitem[$attributeSetName]['swt'] = floatval($stoneWeight) + floatval(isset($arrayitem[$attributeSetName]['swt']) ? $arrayitem[$attributeSetName]['swt'] : 0);
		$arrayitem[$attributeSetName]['mswt'] = floatval($metalWeight) + floatval(isset($arrayitem[$attributeSetName]['mswt']) ? $arrayitem[$attributeSetName]['mswt'] : 0);
		$subtotal = isset($arrayitem[$attributeSetName]['subtotal']) ? intval($arrayitem[$attributeSetName]['subtotal']) : 0;
		$arrayitem[$attributeSetName]['subtotal'] = $subtotal + intval($price);
	}

}
$totalProdQty = 0;
foreach ($arrayitem as $key => $memoval) {
	$subTotal = ShowroomHelper::currencyFormat($memoval['subtotal']);
	$kts = explode('/', $memoval['met']);
	$kts = array_unique($kts);
	$kts = implode(',', $kts);
	$kts = rtrim($kts, ",");
	$totalProdQty += $memoval['qty'];
	?>
							<tr>
								<td><?php echo $key; ?></td>
								<td><?php echo preg_replace('{/$}', '', $kts); ?></td>
								<td><?php echo $memoval['qty']; ?></td>
								<td><?php echo $memoval['swt']; ?></td>
								<td><?php echo number_format($memoval['mswt'], 2); ?></td>
								<td><?php echo $subTotal; ?></td>
							</tr>
			        	<?php
}
?>
			        <tr>
			        	<th colspan="2">Grand Total:</th>
			        	<td class="text-align-center"><?php echo $totalProdQty; ?></td>
			        	<td><?php echo $totalStoneWeight; ?></td>
			        	<td><?php echo number_format($totalMetalWeight, 2); ?></td>
			        	<td><?php echo $finalGrandTotalPrice; ?></td>
			        </tr>
			    </table>
			    <div class="Print_memo_list">
			    	<ul>
				        <p><b>Terms and Conditions:</b></p>
				        <li>1) Objection if any to this should be raised within 7 days of this receipt or other wise it will be considered as accepted by you.</li>
				        <li>2) No claim for damage, breakage and/or pilferage after delivery is given shall be entertained.</li>
				        <li>3) This is a computer generated Approval memo and does not require Signature.</li>
				        <li>4) Returns/Exchange of goods as per Return/Exchange Policy and the sole discretion of the Company.</li>
				        <li>5) Goods Delivery at Mumbai</li>
				        <li>6) E.& O.E.</li>
				        <li>Subject to Mumbai Jurisdiction</li>
				    </ul>
			    </div>
			    <div class="Print_memo_footer padding-bottom-0" style="display: none;">
			    	<p class="foot-content">
				        The diamonds here in invoiced have been purchased from legitimate sources not involved in funding conflicts and in compliance with United Nations Resolutions. The seller hereby guarantees that these diamonds are conflict free, based on personal knowledge, and /or written guarantees provided by the supplier of these Diamonds.
				    </p>
			    </div>
			    <div class="Print_memo_footer padding-top-0 padding-bottom-0">
			    	<p class="foot-content" style="color:red !important;">
				        "The diamonds herein invoiced have been purchased from legitimate sources not involved in funding conflict, in compliance with United Nations Resolutions and corresponding national laws. The seller hereby guarantees that these diamonds are conflict free and confirms adherence to the World Diamond Council System of Warranties Guidelines."
				    </p>
			    </div>
			    <div class="Print_memo_footer padding-top-0 padding-bottom-0">
			    	<p class="foot-content">
				        No E-way bill is required to be generated as the goods covered under this Invoice/Challan/Approval memo/Notes are exempted as per serial number 4/5 of Annexure to rule 138(14) of the CGST rules 2017.
				    </p>
			    </div>
			    <div class="memo-sign">
			        <div class="col margin-tb-zero">
			          <h6 class="margin-bottom-50">Receiver Signature</h6><!-- Subject to Mumbai Jurisdiction -->
			          <h6 class="margin-tb-zero margin-lr-4"></h6>
			        </div>
			        <div class="col margin-tb-zero">
			          <h6 class="margin-bottom-50">For Diamond Mela Jewels Ltd.</h6>
			          <h6 class="margin-tb-zero margin-lr-4 font-weight-400 text-align-right">Authorized Signature</h6>
			        </div>
			    </div>
		    </div>
	    </section>
	</section>
</div>