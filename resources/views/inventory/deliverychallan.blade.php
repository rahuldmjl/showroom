<?php
use App\Helpers\CustomersHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
use App\Setting;
?>
  <head>
    <!-- Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i,900,900i" rel="stylesheet" />

    <title>Delivery Challan</title>
	<style type="text/css">
    body{letter-spacing: 0.6px;font-size: 16px;color:#000;font-family:aparaj;line-height: 22px;}
    .m-lr-auto{margin-left:auto;margin-right: auto;}
    .col-two > div{width: 50%;box-sizing: border-box;}
    .col-three > div{width: 33.33%;box-sizing: border-box;}
    .width-100{width: 100%;}
    .width-80{width:80%;}
    .width-70{width:70%;}
    .width-60{width:60%;}
    .width-200px{width: 200px;}
    .h-200px{height: 200px;}
    .text-center{text-align: center;}
    .text-left{text-align: left !important;}
    .text-right{text-align: right;}
    .fs-fourteen{font-size: 14px;}
    .fs-fifteen{font-size: 15px;}
    .fs-sixteen{font-size: 16px;}
    .fs-seventeen{font-size: 16px;}
    .fw-600{font-weight: 600;}
    .margin-top-2{margin-top: 2%;}
    .margin-top-3{margin-top: 3%;}
    .margin-tb-10{margin-top: 10px;margin-bottom: 10px;}
    .margin-zero{margin:0;}
    .text-uppercase{text-transform: uppercase;}
    .padding-all{padding:15px;}
    .padding-all-ten{padding: 10px;}
    .padding-b-one{padding-bottom: 1%;}
    .padding-zero{padding: 0;}
    .border{border:1px solid #040404;}
    .border-right{border-right:1px solid #040404;}
    .border-bottom-none{border-bottom: none;}
    .border-extra-light{border:1px solid #f1efef;}
    .bg-extra-light{background-color: #fbfbfb;}
    .flex-justify-space-between{display: flex;flex-wrap: wrap;justify-content: space-between;}
    .display-flex{display: flex;flex-wrap: wrap;}
    .table{border-collapse: collapse;width: 100%;}
    .table thead th{font-weight: 500;text-align: left;padding: 5px;vertical-align: top;}
    .table, .table thead th, .table tbody td{border:1px solid #040404;}
    .table tbody td{padding:5px;}
    .table tbody th{position: relative;}
    .table tbody tr:last-of-type th{padding:5px;}
    .align-top{vertical-align: top;}
    .align-fit-bottom{position: absolute;right: 5px;bottom: 5px;}
    .list-unstyle li{list-style-type: none;
    letter-spacing: 0.6px;font-size: 16px;color:#000;font-family:aparaj;line-height: 22px;}
    .m-lr-auto{margin-left:auto;margin-right: auto;}
    .width-100{width: 100%;}
    .width-80{width:80%;}
    .width-70{width:70%;}
    .width-60{width:60%;}
    .col-two > div{width: 50%;box-sizing: border-box;}
    .width-200{width: 200px;}
    .h-100px{height: 100px;}
    .h-200px{height: 200px;}
    .text-red{color: #ff0000;}
    .text-black{color: #000;}
    .text-center{text-align: center;}
    .text-left{text-align: left !important;}
    .text-right{text-align: right;}
    .fs-thirteen{font-size: 13px;}
    .fs-fourteen{font-size: 14px;}
    .fs-fifteen{font-size: 15px;}
    .fs-sixteen{font-size: 16px;}
    .fs-seventeen{font-size: 16px;}
    .fs-eighteen{font-size: 18px;}
    .fw-600{font-weight: 600;}
    .margin-top-2{margin-top: 2%;}
    .margin-top-3{margin-top: 3%;}
    .margin-tb-10{margin-top: 10px;margin-bottom: 10px;}
    .margin-zero{margin:0;}
    .text-uppercase{text-transform: uppercase;}
    .padding-all{padding:15px;}
    .padding-all-ten{padding: 10px;}
    .position-relative{position: relative;}
    .padding-b-one{padding-bottom: 1%;}
    .border{border:1px solid #040404;}
    .border-right{border-right:1px solid #040404;}
    .border-bottom-none{border-bottom: none;}
    .border-bottom{border-bottom:1px solid #040404;}
    .border-extra-light{border:1px solid #f1efef;}
    .bg-extra-light{background-color: #fbfbfb;}
    .flex-justify-space-between{display: flex;flex-wrap: wrap;justify-content: space-between;}
    .display-flex{display: flex;flex-wrap: wrap;}
    .align-top{vertical-align: top;}
    .align-fit-bottom{position: absolute;right: 10px;bottom: 5px;}
    .border-top-none{border-top: none;}
    .productstr .border-bottom-none{border-bottom-color:#fff !important;}
    .margin-0{margin:0px;}
    .margin-t-10{margin-top:10px;}
    .margin-b-0{margin-bottom: 0px;}
    /*.invoice-table .productstr:last-child .border-bottom-none{border-bottom-color:#040404 !important;}*/
  </style>
  </head>
  <?php
//$invoiceCollection = InventoryHelper::getInvoiceByOrder($order->entity_id);
$customerId = isset($invoiceData->customer_id) ? $invoiceData->customer_id : '';
$shippingCharge = isset($invoiceData->invoice_shipping_charge) ? $invoiceData->invoice_shipping_charge : 0;
$customerGroup = InventoryHelper::getCustomerGroup($customerId);
$customerGroupId = isset($customerGroup->customer_group_id) ? $customerGroup->customer_group_id : '';
if (isset($invoiceData->gst_percentage) && !empty($invoiceData->gst_percentage)) {
  $invoiceGstPercentage = $invoiceData->gst_percentage;
} else {
  $invoiceGstPercentageData = Setting::where('key', config('constants.settings.keys.igst_percentage'))->first('value');

  $invoiceGstPercentage = $invoiceGstPercentageData->value;
  // ->first('value')
  //var_dump($invoiceGstPercentage);exit;
}
$gstData = InventoryHelper::getInvoiceGst($invoiceData->invoice_ent_id);
$sgstPercentage = isset($gstData['sgst_percentage']) ? (float) $gstData['sgst_percentage'] : 0;
$cgstPercentage = isset($gstData['cgst_percentage']) ? (float) $gstData['cgst_percentage'] : 0;
$customerShippingAddress = isset($invoiceData->shipping_address_id) ? InventoryHelper::getAddressById($invoiceData->shipping_address_id) : '';
$shippingCustomerName = $customerShippingAddress->firstname . "  " . $customerShippingAddress->lastname;
$shippingStreet = isset($customerShippingAddress->street) ? $customerShippingAddress->street : '';
$shippingCity = isset($customerShippingAddress->city) ? $customerShippingAddress->city : '';
$shippingRegion = isset($customerShippingAddress->region) ? $customerShippingAddress->region : '';
$shippingEmail = isset($customerShippingAddress->email) ? $customerShippingAddress->email : '';
$shippingTelephone = isset($customerShippingAddress->telephone) ? $customerShippingAddress->telephone : '';
$shippingCountryId = isset($customerShippingAddress->country_id) ? $customerShippingAddress->country_id : $customerBillingAddress->country_id;
$shippingAddress = $shippingStreet . " " . $shippingCity;
$customerBillingAddress = InventoryHelper::getDefaultBillingAddressByCustId($customerId);
$stateCode = isset($customerBillingAddress['stateCode']) ? $customerBillingAddress['stateCode'] : '';
$billingStreet = isset($customerBillingAddress['street']) ? $customerBillingAddress['street'] : '';
$billingCity = isset($customerBillingAddress['city']) ? $customerBillingAddress['city'] : '';
$billingRegion = isset($customerBillingAddress['region']) ? $customerBillingAddress['region'] : '';
$billingEmail = isset($customerBillingAddress['email']) ? $customerBillingAddress['email'] : '';
$billingTelephone = isset($customerBillingAddress['telephone']) ? $customerBillingAddress['telephone'] : '';
$billingCountryId = isset($customerBillingAddress['country_id']) ? $customerBillingAddress['country_id'] : '';
$billingCustomerName = $customerBillingAddress['firstname'] . " " . $customerBillingAddress['lastname'];
$billingAddress = $billingStreet . " " . $billingCity;
//$shippingCharge = isset($order->shipping_amount) ? $order->shipping_amount : '';
$subTotal = isset($order->subtotal) ? $order->subtotal : '';
$currentFranchiseeId = isset($order->customer_id) ? $order->customer_id : '';
$gstinNumber = CustomersHelper::getCustomerAttrValue($customerId, 'gstin');
$gstinNumber = !empty($gstinNumber) ? $gstinNumber : '';
$shippingGstinNumber = CustomersHelper::getCustomerAttrValue($customerId, 'gstin');
$shippingGstinNumber = !empty($shippingGstinNumber) ? $shippingGstinNumber : '';
$logoname = Config::get('constants.pdf_logo.name');
?>

<div class="Tax-invoice width-60 m-lr-auto margin-top-2 padding-all border-extra-light bg-extra-light">
      <!-- header start -->
      <header>
        <div class="logo text-center padding-b-one">
          <img src="{{ URL::to('/') }}/images/logo.png" class="logo width-200px" alt="">
          <p><?php echo '<b>'.$logoname.'</b>'; ?></p>
        </div>
        <div class="text-center">
          <p>2307, Floor-23 Panchratna, Opera House, Charni Road, Mumbai-400004 Ph: 022-49664999 </p>
          <h5 class="text-uppercase fw-600 fs-seventeen margin-tb-10"><?php echo ('DELIVERY CHALLAN') ?></h5>
          <p>Delivery Challan issued under Rule 55 of Goods and Services Tax Rules, 2017</p>
        </div>
      </header>
      <!-- header end -->
  <section>

    <div class="tax-invoice margin-top-3">
      <div class="flex-justify-space-between">
        <div class="label-type">
         <label class="fw-600 text-uppercase">cin:</label>
         <span class="text-uppercase">U74999Mh2014plc2603299</span>
        </div>
        <div class="label-type">
         <label class="fw-600 text-uppercase">gstin:</label>
         <span class="text-uppercase">27aafcd2233a1zb</span>
        </div>
      </div>
      <!-- detail start -->
      <div class="table-content">
        <!-- table start -->
        <table class="table border invoice-table">
          <thead>
            <tr>
              <th colspan="3">
                  <div class="col">
                    <label>
                      <?php
if (isset($invoiceData->child_customer_name) && !empty($invoiceData->child_customer_name)) {
	$billingCustomerName = $invoiceData->child_customer_name;
	$billingAddress = $invoiceData->child_customer_address;
	$billingTelephone = '';
	$billingRegion = '';
	$gstinNumber = '';
}
?>
                      <p class="margin-0">Bill To: <?=$billingCustomerName?></p>
                      <p class="margin-0"><?=$billingAddress?></p>
                      <?php if (!empty($billingTelephone)): ?>
                        <p class="margin-0"><?=$billingTelephone?></p>
                      <?php endif;?>
                      <?php if (!empty($billingRegion)): ?>
                        <p class="margin-0"><?=$billingRegion?></p>
                      <?php endif;?>
                      <p class="margin-0">GSTIN:<?=$gstinNumber?></p>
                  </label>
                  <span class="text-uppercase"></span>
                </div>
                  <div class="col">
                    <label class="margin-t-10">
                      <?php
if (isset($invoiceData->child_customer_name) && !empty($invoiceData->child_customer_name)) {
	$shippingCustomerName = $invoiceData->child_customer_name;
	$shippingAddress = $invoiceData->child_customer_address;
	$shippingTelephone = '';
	$shippingRegion = '';
	$shippingGstinNumber = '';
}
?>
                      <p class="margin-b-0 margin-t-10">Ship To: <?=$shippingCustomerName?></p>
                      <p class="margin-0"><?=$shippingAddress?></p>
                      <?php if (!empty($shippingTelephone)): ?>
                        <p class="margin-0"><?=$shippingTelephone?></p>
                      <?php endif;?>
                      <?php if (!empty($shippingRegion)): ?>
                        <p class="margin-0"><?=$shippingRegion?></p>
                      <?php endif;?>
                      <p class="margin-0">GSTIN:<?=$shippingGstinNumber?></p>
                      </label>
                    <span class="text-uppercase"></span>
                  </div>
                </div>
              </th>
              <th colspan="5">

               <div class="col">
                 <label>Delivery Challan No: <?php echo $new_voucher; ?></label>
                 <span class="text-uppercase"></span>
                </div>
                <div class="col">
                 <label>Delivery Challan Date: <?php echo date('d-m-Y') ?></label>
                 <span class="text-uppercase"></span>
                </div>
                  <div class="col">
                  <label>Invoice No: <?php echo $invoiceData->invoice_inc_id; ?></label>
                  <span class="text-uppercase"></span>
              </div>
                <div class="col">
                 <label>Invoice Date: <?php echo date('d-m-Y', strtotime($invoiceData->invoice_created_date)) ?></label>
                 <span class="text-uppercase"></span>
                </div>

               <div class="col">
                 <label>Place of Supply: <?php echo $billingRegion; ?></label>
                 <span class="text-uppercase"></span>
                </div>



              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text-center">Sr.No</td>
              <td>Description of good</td>
              <td>HSN/SAC Code</td>
              <td>Qty (in pcs)</td>
              <td>Unit price</td>
              <td>Discount (%/Rs)</td>
              <td>Shipping Charges</td>
              <td>Total</td>
            </tr>

             <?php
$arrayitem = array();
$finalDiscount = 0;
$totalQty = 0;
$totalStoneWeight = 0;
$totalMetalWeight = 0;
$grandTotalPrice = 0;
$finaRawTotal = 0;
$finalGrandTotalPrice = 0;
$totalTaxAmount = 0;
$tmpArr = array();
$count = 0;
$productQty = 0;
//$orderItems = InventoryHelper::getOrderItems($order->entity_id);
$invoiceItems = InventoryHelper::getInvoiceItems($invoiceData->invoice_ent_id);
/*echo "<pre>";
print_r($invoiceItems);exit;exit;*/

foreach ($invoiceItems as $key => $item) {

	//$product = InventoryHelper::getProductData($item->product_id);
	$product = InventoryHelper::getInvoiceProductData($item->product_id, $item->parent_id);
	/*echo "<pre>";
print_r($product);exit;*/
	if (empty($product)) {
		continue;
	}
	$productQty = isset($item->qty) ? $item->qty : 0;

	$price = isset($item->price) ? $item->price : '-';
	$discountAmount = isset($item->discount_amount) ? $item->discount_amount : 0;
	
  $finalDiscount += $discountAmount;
	$metalWeight = isset($product->metal_weight) ? number_format($product->metal_weight, 3) : 0;
	$stoneWeight = isset($product->stone_weight) ? number_format($product->stone_weight, 2) : 0;
	$totalQty += $productQty;

	$totalStoneWeight += $stoneWeight;
	$totalMetalWeight += $metalWeight;
	$grandTotalPrice += $price;
	$finaRawTotal = $grandTotalPrice - $finalDiscount;
	$rawTotal = round($finaRawTotal);
	$grandTotalPricePerProduct = $price;
	$finalDiscountPerProduct = round($discountAmount, 0);
	$finaRawTotalPerProduct = $grandTotalPricePerProduct - $finalDiscountPerProduct;
	$rawTotalPerProduct = ShowroomHelper::currencyFormat(round($finaRawTotalPerProduct));
	$totalTaxAmount += isset($item->tax_amount) ? $item->tax_amount : 0;
	$finalGrandTotalPrice = round($grandTotalPrice);

	$arrayitem[$productQty]['qty'] = round($productQty, 0) + intval(isset($arrayitem[$productQty]['qty']) ? $arrayitem[$productQty]['qty'] : 0);
	$arrayitem[$productQty]['swt'] = floatval($stoneWeight) + floatval(isset($arrayitem[$productQty]['swt']) ? $arrayitem[$productQty]['swt'] : 0);
	$arrayitem[$productQty]['mswt'] = floatval($metalWeight) + floatval(isset($arrayitem[$productQty]['mswt']) ? $arrayitem[$productQty]['mswt'] : 0);
	$arrayitem[$productQty]['subtotal'] = intval($price) + intval(isset($arrayitem[$productQty]['subtotal']) ? $arrayitem[$productQty]['subtotal'] : 0);

  $arrayitem[$productQty]['shipping_charge'] = $shippingCharge;

	$tmpArr[$count]['sku'] = isset($product->sku) ? $product->sku : '';
	$tmpArr[$count]['mswt'] = $metalWeight;
	$tmpArr[$count]['swt'] = $stoneWeight;
  $tmpArr[$count]['shipping_charge'] = $shippingCharge;

	$tmpArr[$count]['qty'] = round($item->qty);
	$tmpArr[$count]['subtotal'] = intval($price);
	$tmpArr[$count]['finalDiscount_PerOne'] = round($discountAmount, 0);
	$tmpArr[$count]['rawTotalPerProduct'] = round($finaRawTotalPerProduct);

	$count++;
}
$counter = 1;
$totalItemsCount = count($tmpArr);

if ($arrayitem[$productQty]['qty'] <= 3) {

	$total_taxable_value = 0;
	foreach ($tmpArr as $key => $tmpArrVal) {
    $shipping_charge = ($key == 0) ? $tmpArrVal['shipping_charge'] : 0;
		?>
   <tr class="<?php echo ($totalItemsCount != $key || $totalItemsCount != 1) ? 'productstr' : '' ?>">
      <td class="text-center border-bottom-none"><?php echo $counter; ?></td>
      <td class="border-bottom-none">Diamond Studded Gold/Platinum Jewellery <br/><br/><?php echo $tmpArrVal['sku']; ?><br/><br/>Metal Weight <?php echo preg_replace('{/$}', '', $tmpArrVal['mswt']); ?> <br/><br/> Diamond Weight <?php echo $tmpArrVal['swt']; ?></td>
      <td class="border-bottom-none">71131930</td>
      <td class="border-bottom-none"><?php echo $tmpArrVal['qty']; ?></td>
      <td class="border-bottom-none"><?php echo ShowroomHelper::currencyFormat($tmpArrVal['subtotal']); ?></td>
      <td class="border-bottom-none"><?php echo CommonHelper::covertToCurrency(round($tmpArrVal['finalDiscount_PerOne'])); ?></td>
      <td class="border-bottom-none"><?php echo CommonHelper::covertToCurrency($shipping_charge); ?></td>
      <td class="border-bottom-none"><?php echo CommonHelper::covertToCurrency(round((float) $tmpArrVal['rawTotalPerProduct'] + $shipping_charge)); ?></td>

  </tr>
<?php
$counter++;
		$total_taxable_value = $total_taxable_value + $tmpArrVal['rawTotalPerProduct'] + $shipping_charge;
	}}
$itemIndex = 0;
$totalItemsCount = count($arrayitem);
if ($arrayitem[$productQty]['qty'] > 3) {

	$total_taxable_value = 0;

	foreach ($arrayitem as $key => $invoiceVal) {
    $shippingcharge = (round($key) <= 1) ? $invoiceVal['shipping_charge'] : 0;
		$subTotal = isset($invoiceVal['subtotal']) ? ShowroomHelper::currencyFormat($invoiceVal['subtotal']) : 0;
		?>
            <tr class="<?php echo ($totalItemsCount != $key && $totalItemsCount != 1) ? 'productstr' : '' ?>">
            <td class="text-center border-bottom-none">1</td>
            <td class="border-bottom-none">Diamond Studded Gold/Platinum Jewellery<br/> <br/>Metal Weight <?php echo preg_replace('{/$}', '', $invoiceVal['mswt']); ?> <br/><br/> Diamond Weight <?php echo $invoiceVal['swt']; ?></td>
            <td class="border-bottom-none">71131930</td>
            <td class="border-bottom-none"><?php echo isset($invoiceVal['qty']) ? $invoiceVal['qty'] : '-'; ?></td>
            <td class="border-bottom-none"><?php echo ShowroomHelper::currencyFormat($invoiceVal['subtotal']) ?></td>
            <td class="border-bottom-none"><?php echo CommonHelper::covertToCurrency(round($finalDiscount)); ?></td>
            <td class="border-bottom-none"><?php echo CommonHelper::covertToCurrency(round($shippingcharge)); ?></td>
            <td class="border-bottom-none WebRupee"><?php echo ShowroomHelper::currencyFormat(round($rawTotal + $shippingcharge)); ?></td>
            </tr>
        <?php $itemIndex++;
		$total_taxable_value = (float) $total_taxable_value + (float) $rawTotal + (float) $shippingcharge;
	}}?>

            <tr style="border-top: 2px solid;">
              <td colspan="2" class="text-left">
              	<span>Shipping Delivery Through:<?php echo $invoiceData->transportation_mode; ?></span><br>
                <span>Invoice/Memo Reference No. <br/> <?php echo $invoiceData->invoice_inc_id; ?></span><br>


              </td>
              <td colspan="3" class="text-left">
	                <span>If G.R./Vehicle/A.W No.</span><br>
	                <span>Remarks (If any</span><br>
              </td>
              <td colspan="3">
                 <span>Total Taxable Value: <?php echo CommonHelper::covertToCurrency(round($total_taxable_value)); ?></span><br>
                 <?php
$igst = round($total_taxable_value) * ($invoiceGstPercentage / 100);
$finalGrandTotalPrice = round($total_taxable_value) + round($igst, 2);
?>
                 <?php if (empty($sgstPercentage) && empty($cgstPercentage)): ?>
                  <span>IGST <?=$invoiceGstPercentage?>% : <?php echo round($igst, 2); ?></span><br>
                 <?php else: ?>
                  <?php
        $sgst = round($total_taxable_value) * ($sgstPercentage / 100);
        $cgst = round($total_taxable_value) * ($cgstPercentage / 100);
        ?>
                  <span>SGST <?=$sgstPercentage?>% : <?php echo round($sgst, 2); ?></span><br>
                  <span>CGST <?=$cgstPercentage?>% : <?php echo round($cgst, 2); ?></span><br>
                 <?php endif;?>
                 <?php $roundingValue = round($finalGrandTotalPrice) - $finalGrandTotalPrice;?>
                 <?php
$exactFinalGrandTotalPrice = $total_taxable_value + $igst;
$finalGrandTotalPrice = round($total_taxable_value + $igst);
$rounding_gap = $finalGrandTotalPrice - $exactFinalGrandTotalPrice;
?>
                 <span>Rounding : <?php
$roundingSign = ($roundingValue > 0) ? '+' : '';
echo $roundingSign . round($roundingValue, 2);?></span><br>
<?php
$grandTotal = CommonHelper::covertToCurrency(round($finalGrandTotalPrice));
?>
                 <span>Grand Total: <?php echo $grandTotal; ?></span>
              </td>
            </tr>
            <tr>
              <td colspan="8">
                <p class="margin-zero">Total Amount In Words :- <?php echo ucfirst(InventoryHelper::convertNumberToWords($finalGrandTotalPrice)) ?></p>
              </td>
            </tr>
            <tr>
             <td colspan="8" class="width-80 m-lr-auto text-center text-black width-80 m-lr-auto fs-thirteen ">
              No E-way bill is required to be generated as the goods covered under this Invoice/Challan/Notes
              are exempted as per serial number 4/5 of Annexure to rule 138(14) of the CGST rules 2017.
            </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>


    <div class="tax-invoice">
   		<div class="tax-invoice-detail border border-top-none">
       		<div class="display-flex col-two position-relative">
          		<div class="col padding-all-ten h-200px border-right">
            		<h4 class="margin-zero">Receiver Sign & Seal</h4>
          		</div>
          		<div class="col padding-all-ten h-200px">
            		<h4 class="margin-zero text-right">For Diamond Mela Jewels Limited</h4>
            		<p class="align-fit-bottom fw-600">Authorised Signatory</p>
          		</div>
        	</div>
      	</div>
    </div>
  </section>
</div>