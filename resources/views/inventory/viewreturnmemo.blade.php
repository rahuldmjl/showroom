<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
?>
<head>
	<title>Print Return Memo | Diamondmela</title>
	<link rel="stylesheet" href="http://123.108.51.11/skin/frontend/rwdnew/default/css/styles.css" />
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i,900,900i" rel="stylesheet" />
</head>
<?php if ($returnMemo->count() > 0): ?>
<div class="print-container">
	<section class="franchises-portal">
		<section class="memo">
			<div class="memo-head">
				<?php if (!empty($returnMemo->franchise_id)): ?>
					<?php
$customerBillingAddress = InventoryHelper::getDefaultBillingAddressById($returnMemo->franchise_address);
$street = isset($customerBillingAddress['street']) ? $customerBillingAddress['street'] : '';
$city = isset($customerBillingAddress['city']) ? $customerBillingAddress['city'] : '';
$region = isset($customerBillingAddress['region']) ? $customerBillingAddress['region'] : '';
$postcode = isset($customerBillingAddress['postcode']) ? $customerBillingAddress['postcode'] : '';
$telephone = isset($customerBillingAddress['telephone']) ? $customerBillingAddress['telephone'] : '';
$panCardNumber = isset($customerBillingAddress['pancard_number']) ? $customerBillingAddress['pancard_number'] : '';
?>
					<h1><?=$customerBillingAddress['firstname'] . " " . $customerBillingAddress['lastname']?></h1>
					<h1><?=$street . " " . $city . " " . $postcode . " " . strtoupper($region) . " " . $telephone?></h1>
				<?php else: ?>
					<?php
$customerBillingAddress = InventoryHelper::getDefaultBillingAddressByCustId($returnMemo->customer_id);
$street = isset($customerBillingAddress['street']) ? $customerBillingAddress['street'] : '';
$city = isset($customerBillingAddress['city']) ? $customerBillingAddress['city'] : '';
$region = isset($customerBillingAddress['region']) ? $customerBillingAddress['region'] : '';
$postcode = isset($customerBillingAddress['postcode']) ? $customerBillingAddress['postcode'] : '';
$telephone = isset($customerBillingAddress['telephone']) ? $customerBillingAddress['telephone'] : '';
$panCardNumber = isset($customerBillingAddress['pancard_number']) ? $customerBillingAddress['pancard_number'] : '';
?>
					<h1><?=$customerBillingAddress['firstname'] . " " . $customerBillingAddress['lastname']?></h1>
					<h1><?=$street . " " . $city . " " . $postcode . " " . strtoupper($region) . " " . $telephone?></h1>
				<?php endif;?>
			</div>
			<div class="memo-delivery">
				<h2>DELIVERY CHALLAN</h2>
		        <h4>(Return of goods taken on Approval)</h4>
		        <div class="address">
            		<p class="margin-bottom-0">To,</p>
		        </div>
		        <div class="address2"></div>
		        <table class="Print_memo_left">
		            <tr>
		              <th>Name: </th>
		              <td><?php echo Config::get('constants.message.returnmemo_company_name'); ?></td>
		            </tr>
		            <tr>
		              <th>Address:</th>
		              <td><?php echo Config::get('constants.message.returnmemo_address'); ?></td>
		            </tr>
		            <tr>
		              <th>City:</th>
		              <td><?php echo Config::get('constants.message.returnmemo_city'); ?></td>
		            </tr>
		            <tr>
		              <th>Pin code:</th>
		              <td><?php echo Config::get('constants.message.returnmemo_pincode'); ?></td>
		            </tr>
		            <tr>
		              <th>State:</th>
		              <td><?php echo Config::get('constants.message.returnmemo_state'); ?></td>
		            </tr>
		            <tr>
		              <th>Pan:</th>
		              <td><?php echo Config::get('constants.message.returnmemo_pan'); ?></td>
		            </tr>
		            <tr>
		              <th>Contact No:</th>
		              <td><?php echo Config::get('constants.message.returnmemo_contactno'); ?></td>
		            </tr>
		            <tr>
		              <th>GSTIN :</th>
		              <td><?php echo Config::get('constants.message.returnmemo_gstin'); ?></td>
		            </tr>
		        </table>
		        <table class="Print_memo_right">
		        	<tr>
		              <th>Approval No:</th>
		              <td>
		              	<?php
$currentYear = date('y', strtotime($returnMemo->created_at));
$returnMemoNumber = isset($returnMemo->return_number) ? $returnMemo->return_number : '';
if (date('m') > 6) {
	$fin_year = date('y') . '-' . (date('y') + 1);
} else {
	$fin_year = (date('y') - 1) . '-' . date('y');
}
$returnMemoNumber = $fin_year . '/' . $returnMemoNumber;
echo $returnMemoNumber;?></td>
		            </tr>
		            <tr>
		            	<?php
$date = isset($returnMemo->created_at) ? date('M d, Y', strtotime($returnMemo->created_at)) : '';
?>
		            	<th>Date:</th>
		            	<td><?=$date?></td>
		            </tr>
		            <tr>
		              <th>HSN CODE: : </th>
		              <td><?php echo Config::get('constants.message.returnmemo_hsn_code'); ?></td>
		            </tr>
		        </table>
		        <div class="Print_memo_list">
		        	<ul>
			            <p>Please be advised that we are electing to return the accompanying goods received from you on approval.Kindly accept the dispatched goods and acknowledge.</p>
			            <p>&nbsp;</p>
			            <p>The goods are in the same condition as received from you.</p>
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
$returnMemoData = array();
$product_metal = array();
$productData = isset($returnMemo->product_data) ? json_decode($returnMemo->product_data) : array();
foreach ($productData as $key => $product) {
	$kts1 = isset($product->kt) ? explode('/', $product->kt) : array();

	$kts1 = array_unique($kts1);
	$kts1 = array_filter($kts1);
	$kts1 = implode(',', $kts1);
	$kts1 = rtrim($kts1, ",");
	$kts1 = explode(' ', $kts1);
	$product_metal[$product->product_types][] = isset($kts1[0]) ? $kts1[0] : '';
	//print_r($product_metal);exit;
	$product_metal[$product->product_types] = array_unique($product_metal[$product->product_types]);
	$returnMemoData[$product->product_types]['kts'] = implode(',', $product_metal[$product->product_types]);

	$kt = isset($returnMemoData[$product->product_types]['kts']) ? $returnMemoData[$product->product_types]['kts'] : '';
	$kts = isset($kt) ? explode('/', $kt) : array();
	$kts = array_unique($kts);
	$kts = array_unique($kts);
	$kts = array_filter($kts);
	$kts = implode(',', $kts);
	$kts = rtrim($kts, ",");

	$qty = isset($returnMemoData[$product->product_types]['qty']) ? $returnMemoData[$product->product_types]['qty'] : 0;
	$returnMemoData[$product->product_types]['qty'] = (int) $qty + (int) $product->qty;

	$diaWeight = isset($returnMemoData[$product->product_types]['diamond_weight']) ? $returnMemoData[$product->product_types]['diamond_weight'] : 0;

	$returnMemoData[$product->product_types]['diamond_weight'] = (float) $diaWeight + (float) $product->diamond_weight;

	//$returnMemoData[$product->product_types]['metal_quality'] = $kts;

	$metalWeight = isset($returnMemoData[$product->product_types]['metal_weight']) ? number_format($returnMemoData[$product->product_types]['metal_weight'], 2) : 0;
	$returnMemoData[$product->product_types]['metal_weight'] = $metalWeight + $product->metal_weight;
	$price = isset($returnMemoData[$product->product_types]['price']) ? $returnMemoData[$product->product_types]['price'] : 0;
	$returnMemoData[$product->product_types]['price'] = (float) $price + (float) $product->price;
}

foreach ($returnMemoData as $key => $product) {
	//echo "<pre>";
	//print_r($product);exit;
	?>
		            	<tr>
		            		<th><?=$key;?></th>
		            		<th><?=isset($product['kts']) ? $product['kts'] : ''?></th>
		            		<th><?=isset($product['qty']) ? $product['qty'] : '';?></th>
		            		<th><?=isset($product['diamond_weight']) ? $product['diamond_weight'] : '';?></th>
		            		<th><?=isset($product['metal_weight']) ? number_format($product['metal_weight'], 2) : ''?></th>
			                <th><?=isset($product['price']) ? ShowroomHelper::currencyFormat(round($product['price'])) : ''?></th>
		            	</tr>
		            <?php
}
$grandTotalData = isset($returnMemo->grand_total_data) ? json_decode($returnMemo->grand_total_data) : array();
?>
		            <tr>
		            	<th colspan="2">Grand Total:</th>
			            <td class="text-align-center"><?php echo isset($grandTotalData->qty) ? round($grandTotalData->qty, 0) : ''; ?></td>
			            <td><?php echo isset($grandTotalData->diamond_weight) ? $grandTotalData->diamond_weight : ''; ?></td>
			            <td><?php echo isset($grandTotalData->metal_weight) ? number_format($grandTotalData->metal_weight, 2) : ''; ?></td>
			            <td><?php echo isset($grandTotalData->price) ? ShowroomHelper::currencyFormat(round($grandTotalData->price)) : ''; ?></td>
		            </tr>
		            <tr>
			            <td colspan="6">Subject to Mumbai Jurisdiction</th>
			        </tr>
		        </table>
		        <div class="memo-sign">
		            <div class="col margin-tb-zero">
		              <h6 class="margin-bottom-50">For Diamondmela Jewels Ltd.</h6>
		              <h6 class="margin-tb-zero margin-lr-4">Authorized Signature</h6>
		            </div>
		            <div class="col margin-tb-zero">
		              <h6 class="margin-bottom-50"><p>For</p><p>Name</p></h6>
		              <h6 class="margin-tb-zero margin-lr-4 font-weight-400 text-align-right">Authorized Signatory</h6>
		            </div>
		        </div>
			</div>
		</section>
	</section>
</div>
<?php endif;?>