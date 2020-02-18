<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">

    <title>Tax invoice</title>
  </head>

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
  <body>
  <?php
//$invoiceCollection = InventoryHelper::getInvoiceByOrder($order->entity_id);
$customerId = isset($invoiceData->customer_id) ? $invoiceData->customer_id : '';
$customerGroup = InventoryHelper::getCustomerGroup($customerId);
$customerGroupId = isset($customerGroup->customer_group_id) ? $customerGroup->customer_group_id : '';

$orderNo = '';
if ($customerGroup->customer_group_code == 'DML Group') {
	$orderNo = isset($invoiceData->dmlstore_order_increment_id) ? $invoiceData->dmlstore_order_increment_id : '';
} else {
	$orderNo = isset($invoiceData->franchise_order_increment_id) ? $invoiceData->franchise_order_increment_id : '';
}

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
$shippingCharge = isset($order->shipping_amount) ? $order->shipping_amount : '';
$subTotal = isset($order->subtotal) ? $order->subtotal : '';
$currentFranchiseeId = isset($order->customer_id) ? $order->customer_id : '';
$gstinNumber = isset($customerBillingAddress['gstin']) ? $customerBillingAddress['gstin'] : '';
$shippingGstinNumber = isset($customerShippingAddress->gstin) ? $customerShippingAddress->gstin : '';
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
          <h5 class="text-uppercase fw-600 fs-seventeen margin-tb-10"><?php echo ('TAX INVOICE') ?></h5>
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
                      if(isset($invoiceData->child_customer_name) && !empty($invoiceData->child_customer_name))
                      {
                        $billingCustomerName = $invoiceData->child_customer_name;
                        $billingAddress = $invoiceData->child_customer_address;
                        $billingTelephone = '';
                        $billingRegion = '';
                        $gstinNumber = '';
                      }
                      ?>
                      <p class="margin-0">Bill To: <?= $billingCustomerName?></p>
                      <p class="margin-0"><?= $billingAddress?></p>
                      <?php if(!empty($billingTelephone)):?>
                        <p class="margin-0"><?= $billingTelephone?></p>
                      <?php endif;?>
                      <?php if(!empty($billingRegion)):?>
                        <p class="margin-0"><?= $billingRegion?></p>
                      <?php endif;?>
                      <p class="margin-0">GSTIN:<?= $gstinNumber?></p>
                  </label>
                  <span class="text-uppercase"></span>
                </div>
                  <div class="col">
                    <label class="margin-t-10">
                      <?php
                      if(isset($invoiceData->child_customer_name) && !empty($invoiceData->child_customer_name))
                      {
                          $shippingCustomerName = $invoiceData->child_customer_name;
                          $shippingAddress = $invoiceData->child_customer_address;
                          $shippingTelephone = '';
                          $shippingRegion = '';
                          $shippingGstinNumber = '';
                      }
                      ?>
                      <p class="margin-b-0 margin-t-10">Ship To: <?= $shippingCustomerName?></p>
                      <p class="margin-0"><?= $shippingAddress?></p>
                      <?php if(!empty($shippingTelephone)):?>
                        <p class="margin-0"><?= $shippingTelephone?></p>
                      <?php endif;?>
                      <?php if(!empty($shippingRegion)):?>
                        <p class="margin-0"><?= $shippingRegion?></p>
                      <?php endif;?>
                      <p class="margin-0">GSTIN:<?= $shippingGstinNumber?></p>
                      </label>
                    <span class="text-uppercase"></span>
                  </div>
                </div>
              </th>
              <th colspan="5">
                <div class="col">
                  <label>Invoice No: DMOF/18-19/31649</label>
                  <span class="text-uppercase"></span>
                </div>
                <div class="col">
                 <label>Invoice Date: 19-12-2018</label>
                 <span class="text-uppercase"></span>
                </div>
                <div class="col">
                 <label>Place of Supply: <?php echo $billingRegion; ?></label>
                 <span class="text-uppercase"></span>
                </div>
                <div class="col">
                 <label>Delivery Challan Ref No:</label>
                 <span class="text-uppercase"></span>
                </div>
                <div class="col">
                 <?php if ($invoiceData->isfranchisee == '0') {?>
                <label>Order No./Approval Memo No.(if any): <?php echo isset($invoiceData->real_order_id) ? $invoiceData->real_order_id : '' ?></label>
                <?php }if ($invoiceData->isfranchisee == '1') {?>
                <label>Order No./Approval Memo No.(if any): <?php echo $orderNo; ?></label>
                <?php }?>

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
//$orderItems = InventoryHelper::getOrderItems($order->entity_id);
$invoiceItems = InventoryHelper::getInvoiceItems($invoiceData->invoice_ent_id);
foreach ($invoiceItems as $key => $item) {
  //echo "<pre>";
  //print_r($item);exit;
	$product = InventoryHelper::getProductData($item->product_id);
	if(empty($product))
		continue;
	$metalData = ShowroomHelper::getMetalData($product->entity_id, $product);
	$metalQuality = isset($metalData['quality']) ? $metalData['quality'] : '-';
	$productQty = isset($item->qty_ordered) ? $item->qty_ordered : 0;
	$price = isset($item->price) ? $item->price : '-';
	$discountAmount = isset($item->discount_amount) ? $item->discount_amount : 0;
	if (floatval($invoiceData->custom_discount_percent) != 0) {
		$finalDiscount = round($discountAmount, 0);
	} else {
		$finalDiscount += $discountAmount;
	}
	$stone = isset($product->rts_stone_quality) ? $product->rts_stone_quality : '';
	$stoneData = ShowroomHelper::getSideStoneData($product->entity_id, $stone);
	$gemStoneData = InventoryHelper::getGemStoneData($product->entity_id);
	$stoneWeight = isset($stoneData['totalcts'][0]) ? $stoneData['totalcts'][0] : 0;
	$metalWeight = isset($metalData->metal_weight) ? $metalData->metal_weight : 0;
	$totalQty += $productQty;

	$totalStoneWeight += $stoneWeight;
	$totalMetalWeight += $metalWeight;
	$grandTotalPrice += $price;
	$finaRawTotal = $grandTotalPrice - $finalDiscount;
	$rawTotal = ShowroomHelper::currencyFormat(round($finaRawTotal));
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

	$tmpArr[$count]['sku'] = $product->sku;
	$tmpArr[$count]['mswt'] = $metalWeight;
	$tmpArr[$count]['swt'] = $stoneWeight;

	$tmpArr[$count]['qty'] = round($item->qty);
	$tmpArr[$count]['subtotal'] = intval($price);
	$tmpArr[$count]['finalDiscount_PerOne'] = round($discountAmount, 0);
	$tmpArr[$count]['rawTotalPerProduct'] = ShowroomHelper::currencyFormat(round($finaRawTotalPerProduct));

	$count++;
}
$counter = 1;
$totalItemsCount = count($tmpArr);
if ($arrayitem[$productQty]['qty'] <= 3) {
	foreach ($tmpArr as $key => $tmpArrVal) {
		?>
   <tr class="<?php echo ($totalItemsCount != $key || $totalItemsCount !=1) ? 'productstr' : ''?>">
      <td class="text-center border-bottom-none"><?php echo $counter; ?></td>
      <td class="border-bottom-none">Diamond Studded Gold/Platinum Jewellery <br/><?php echo $tmpArrVal['sku']; ?><br/>Metal Weight <?php echo preg_replace('{/$}', '', $tmpArrVal['mswt']); ?> <br/> Diamond Weight <?php echo $tmpArrVal['swt']; ?></td>
      <td class="border-bottom-none">71131930</td>
      <td class="border-bottom-none"><?php echo $tmpArrVal['qty']; ?></td>
      <td class="border-bottom-none"><?php echo $tmpArrVal['subtotal']; ?></td>
      <td class="border-bottom-none"><?php echo $tmpArrVal['finalDiscount_PerOne']; ?></td>
      <td class="border-bottom-none">0</td>
      <td class="border-bottom-none"><?php echo $tmpArrVal['rawTotalPerProduct']; ?></td>

  </tr>
<?php
$counter++;
	}}
$itemIndex = 0;
$totalItemsCount = count($arrayitem);
if ($arrayitem[$productQty]['qty'] > 3) {
	foreach ($arrayitem as $key => $invoiceVal) {

		$subTotal = isset($invoiceVal['subtotal']) ? ShowroomHelper::currencyFormat($invoiceVal['subtotal']) : 0;
		?>
            <tr class="<?php echo ($totalItemsCount != $key && $totalItemsCount !=1) ? 'productstr' : ''?>">
            <td class="text-center border-bottom-none">1</td>
            <td class="border-bottom-none">Diamond Studded Gold/Platinum Jewellery <br/>Metal Weight <?php echo preg_replace('{/$}', '', $invoiceVal['mswt']); ?> <br/> Diamond Weight <?php echo $invoiceVal['swt']; ?></td>
            <td class="border-bottom-none">71131930</td>
            <td class="border-bottom-none"><?php echo isset($invoiceVal['qty']) ? $invoiceVal['qty'] : '-'; ?></td>
            <td class="border-bottom-none"><?php echo $invoiceVal['subtotal'] ?></td>
            <td class="border-bottom-none"><?php echo $finalDiscount; ?></td>
            <td class="border-bottom-none">0</td>
            <td class="border-bottom-none WebRupee"><?php echo $rawTotal; ?></td>
            </tr>
        <?php $itemIndex++;}}?>

            <tr style="border-top: 2px solid;">
              <td colspan="3" class="text-left">
                <span>Shipping Delivery Through:</span><br>
                <span>If G.R./Vehicle/A.W No.</span><br>
                <span>Remarks (If any</span>
              </td>
              <td colspan="3" class="align-top text-center">
                 <span>Payment Details of Our Bank : </span><br/><?php echo "Payment Information
DIAMOND MELA JEWELS LTD. KOTAK MAHINDRA BANK, OPERA
HOUSE, AC NO. 7212019981, IFSC KKBK0001414
Diamond Mela Jewels LTD. HDFC Bank , Charni Road Branch, A/C No
50200010367715 , IFSC HDFC0000356" ?><br>
              </td>
              <td colspan="2">
                 <span>Total Taxable Value: <?php echo $rawTotal; ?></span><br>
                 <?php $igst = $grandTotalPrice * 0.03;?>
                 <span>IGST 3% : <?php echo round($igst); ?></span><br>
                 <?php $roundingValue = $finaRawTotal - round($finaRawTotal);?>
                 <span>Rounding : <?php echo round($roundingValue, 2); ?></span><br>
                 <?php
$finalGrandTotalPrice = round($totalTaxAmount, 0) + $finaRawTotal;
$grandTotal = ShowroomHelper::currencyFormat($finalGrandTotalPrice);
?>
                 <span>Grand Total: <?php echo $grandTotal; ?></span>
              </td>
            </tr>
            <tr>
              <td colspan="8">
                <p class="margin-zero">Total Amount In Words :- <?php echo ucfirst(InventoryHelper::convertNumberToWords(round($finalGrandTotalPrice, 0))) ?> only</p>
              </td>
            </tr>
            <tr>
             <td colspan="8">
              <label>Terms of Conditions:</label>
              <ul class="list-unstyle padding-zero margin-zero">
                <li>1) Objection if any to this bill should be raised within 7 days of this receipt or other wise it will be considered as accepted by you.</li>
                <li>2) No claim for damage, breakage and/or pilferage after delivery is given or bill is prepared and/or in transit shall be entertained.</li>
                <li>3) This is a computer generated Invoice and does not require Signature.</li>
                <li>4) Returns of goods as per Return Policy and the sole discretion of the Company.</li>
                <li>5) Goods Delivery at Mumbai</li>
                <li>6) E.& O.E.</li>
              </ul>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>


    <div class="tax-invoice">
   <div class="tax-invoice-detail border border-top-none">
        <div class="col padding-all-ten border-bottom h-100px">
          <h4 class="margin-zero">Subject for Mumbai Jurisdiction</h4>
        </div>
        <div class="col padding-all-ten text-red border-bottom fs-eighteen fw-600">
          “The diamonds herein invoiced have been purchased from legitimate sources not
            involved in funding conflict, in compliance
            with United Nations Resolutions and corresponding national laws.
            The seller hereby guarantees that these diamonds are
            conflict free and confirms adherence to the World Diamond Council System of
            Warranties Guidelines.”
            <p class="width-80 m-lr-auto text-center text-black width-80 m-lr-auto fs-thirteen">
              No E-way bill is required to be generated as the goods covered under this Invoice/Challan/Notes
              are exempted as per serial number 4/5 of Annexure to rule 138(14) of the CGST rules 2017.
            </p>
        </div>
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

    <script src="https://code.jquery.com/jquery-2.2.4.js"></script>


  </body>
</html>