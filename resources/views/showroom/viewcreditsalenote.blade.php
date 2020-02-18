<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
    <title>Credit Note</title>
  </head>
  <style type="text/css">
    body{letter-spacing: 0.6px;font-size: 16px;color:#000;font-family:aparaj;line-height: 22px;}
    .m-lr-auto{margin-left:auto;margin-right: auto;}
    .width-100{width: 100%;}
    .rupees-icon{font-family: DejaVu Sans;font-weight: 400;}
    .width-80{width:80%;}
    .width-70{width:70%;}
    .width-60{width:60%;}
    .width-50{width:50%;}
    .width-200px{width: 200px;}
    .h-200px{height: 200px;}
    .text-center{text-align: center;}
    .text-left{text-align: left !important;}
    .text-right{text-align: right;}
    .text-uppercase{text-transform: uppercase;}
    .fs-fourteen{font-size: 14px;}
    .fs-fifteen{font-size: 15px;}
    .fs-sixteen{font-size: 16px;}
    .fs-seventeen{font-size: 16px;}
    .fw-600{font-weight: 600;}
    .margin-top-2{margin-top: 2%;}
    .margin-top-3{margin-top: 3%;}
    .margin-tb-10{margin-top: 10px;margin-bottom: 10px;}
    .margin-zero{margin: 0;}
    .margin-top{margin-top: 0;}
    .text-uppercase{text-transform: uppercase;}
    .padding-all-five{padding: 5px;}
    .padding-all{padding:15px;}
    .padding-all-ten{padding: 10px;}
    .padding-b-one{padding-bottom: 1%;}
    .padding-zero{padding:0 !important;}
    .border{border:1px solid #040404;}
    .border-right{border-right:1px solid #040404;}
    .border-bottom-none{border-bottom: none;}
    .border-extra-light{border:1px solid #f1efef;}
    .bg-extra-light{background-color: #fbfbfb;}
    .list-unstyled li{list-style-type: none;}
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
    .border-top-0{border-top: 0px !important;}
    p{margin:5px 0px;}
  </style>
<body>
<?php 
$customerId = isset($salesReturnData->customer_id) ? $salesReturnData->customer_id : '';
$customerName = isset($customerId) ? InventoryHelper::getCustomerName($customerId) : '';
DB::setTablePrefix('');
$customer = DB::table('customer_entity')->select('email')->where('entity_id','=',DB::raw("$customerId"))->get()->first();
$customerEmail  = isset($customer->email) ? $customer->email : '';
$returnNumber = isset($salesReturnData->sales_return_no) ? $salesReturnData->sales_return_no : '';
$date = isset($salesReturnData->created_at) ? $salesReturnData->created_at : '';
$invoiceNumber = isset($salesReturnData->invoice_no) ? $salesReturnData->invoice_no : '';
$supplyPlace = isset($salesReturnData->supply_place) ? $salesReturnData->supply_place : '';
$paymentMode = isset($salesReturnData->payment_mode) ? $salesReturnData->payment_mode : '';

$productData = isset($salesReturnData->product_data) ? json_decode($salesReturnData->product_data) : '';
$metalType = array();
foreach ($productData as $key => $product) {
  $metalType[] = $product->metal_type;
}
$metalType = array_unique($metalType);
$metalType = implode('/',$metalType);
$gstData = isset($salesReturnData->gst_data) ? json_decode($salesReturnData->gst_data) : '';
$roundingValue = number_format((float)$salesReturnData->total_invoice_value - (float)round($salesReturnData->total_invoice_value),2);
if($roundingValue > 0)
{
    $roundingValue= '+'.$roundingValue;
}
$logoname = Config::get('constants.pdf_logo.name');
?>
<div class="Sales-return-cumcredit width-100 m-lr-auto margin-top-2  border-extra-light bg-extra-light">
  <!-- header start -->
  <header>
    <div class="logo text-center padding-b-one">
      <img src="<?=public_path('/img/sales_return_logo.png');?>" class="width-200px"/>
      <p><?php echo '<b>'.$logoname.'</b>'; ?></p>
    </div>
    <div class="text-center">
      <p>2307, Floor-23 Panchratna, Opera House, Charni Road, Mumbai-400004 Ph: 022-49664999 </p>
      <h5 class="fw-600 fs-seventeen margin-tb-10">Credit Note</h5>
      <p>Invoice issued under section 31 of the CGST Act, 2017 read with Rule 46 of Central Goods and Services Tax Rules,2017 </p>
    </div>
  </header>
  <!-- header end -->
  <section>
    <div class="delivery-challan-content margin-top-3">
      <table class="width-100">
        <tr>
          <td colspan="2"><label class="fw-600 text-uppercase">cin:</label> U74999MH2014PLC2603299</td>
          <td colspan="2" align="right"><label class="fw-600 text-uppercase">gstin:</label> 27AAFCD2233A1ZB</td>
        </tr>
      </table>
      <!-- detail start -->
      <div class="table-content">
        <!-- table start -->
        <table class="table border width-100">
          <tbody>
            <tr>
              <td colspan="3">
                  <p class="margin-zero">To,</p>
                  <p class="margin-zero">Name: <?= $customerName?></p>
                  <p class="margin-zero">Email: <?= $customerEmail?></p>
              </td>
              <td colspan="3">
                <p>Return No: <?= $returnNumber?></p>
                <p>Date: <?= $date?></p>
                <p>Reference Sale Invoice No: <?= $invoiceNumber?></p>
              </td>
            </tr>
            <tr>
              <td align="left" colspan="3" style="">
                <p>Narration : Being Diamond Studded <?= $metalType?> 
                jewellery returned against</p>
                <p>Tax invoice Number: <?= $invoiceNumber?> Date: <?= $date?></p>
                <p style="font-size: 13px;font-weight: 600;margin: 0px !important;">*Making and handling Charges</p>
                <p style="font-size: 13px;font-weight: 600;margin: 0px !important;">*Repair charges</p>
                <p style="font-size: 13px;font-weight: 600;margin: 0px !important;">*Gold Loss/Broken Diamond</p>
              </td>
              <td align="left" colspan="3" style="vertical-align: baseline;">
                  <p>Place of Supply: <?= ucfirst(strtolower($supplyPlace))?></p>
                  <p>Mode of payment: <?= ucfirst($paymentMode)?></p>
              </td>
            </tr>
            <tr class="text-center">
                <td class="text-center border-top-0" style="width:1%">Sr.No</td>
                <td class="text-center border-top-0">Description of good</td>
                <td class="text-center border-top-0" style="width:8% !important;">HSN/SAC Code</td>
                <td class="text-center border-top-0" style="width:8%;">Qty (in pcs)</td>
                <td class="text-center border-top-0" style="width:10%;">Unit Price</td>
                <td class="text-center border-top-0" style="width:10%;">Total</td>
            </tr>
            <?php ?>
            <?php foreach($productData as $key => $product):?>
            <tr>
                <td class="text-center"><?= $key + 1;?></td>
                <td>
                    <p>Diamond Studded <?= $product->metal_type?> Jewellery</p>
                    <p><?= isset($product->sku) ? $product->sku : '';?></p>
                    <p>
                      <?= isset($product->diamond_clarity) ? 'Diamond Clarity: '.$product->diamond_clarity : ''?>
                      Metal Weight: <?= isset($product->metal_weight) ? $product->metal_weight : 0?>
                      Diamond Weight: <?= isset($product->diamond_weight) ? $product->diamond_weight : 0?>
                    </p>
                </td>
                <td class="text-center "><?= isset($product->hsn_code) ? $product->hsn_code : ''?></td>
                <td class="text-center"><?= isset($product->qty) ? $product->qty : '';?></td>
                <td class="text-center rupees-icon"><?= isset($product->unit_price) ? ShowroomHelper::currencyFormat(round($product->unit_price)) : ''?></td>
                <td class="text-center rupees-icon"><?= isset($product->total) ? ShowroomHelper::currencyFormat(round($product->total)) : ''?></td>
            </tr>
            <?php endforeach;?>
            <tr>
              <td colspan="6" align="right" class="rupees-icon"> Grand Total: <?= isset($salesReturnData->total_taxable_value) ? ShowroomHelper::currencyFormat(round($salesReturnData->total_taxable_value)) : 0?></td>
            </tr>
            <tr>
              <td class="border-top-0" colspan="6">
                <p class="rupees-icon">Total Taxable Value: <?= isset($salesReturnData->total_taxable_value) ? ShowroomHelper::currencyFormat(round($salesReturnData->total_taxable_value)) : 0?></p>
                <?php if(isset($gstData->igst)):?>
                <p class= "rupees-icon">IGST 3%:<?= ShowroomHelper::currencyFormat(round($gstData->igst))?></p>
                <?php elseif(isset($gstData->cgst)):?>
                <p class= "rupees-icon">CGST 3%: <?= ShowroomHelper::currencyFormat(round($gstData->cgst))?></p>
                <?php elseif(isset($gstData->sgst)):?>
                <p class= "rupees-icon">SGST 3%: <?= ShowroomHelper::currencyFormat(round($gstData->sgst))?></p>
                <?php endif;?>
                <p>Rounding : <?= $roundingValue?></p>
                <p class= "rupees-icon">Invoice Value: <?= isset($salesReturnData->total_invoice_value) ? ShowroomHelper::currencyFormat(round($salesReturnData->total_invoice_value)) : 0?></p>
              </td>
            </tr>
            <tr>
              <td colspan="6" style="border-top:0px;">
                <p class="margin-zero">Total Amount In Words :- <?php echo ucfirst(InventoryHelper::convertNumberToWords(round($salesReturnData->total_invoice_value)))?> only</p>
              </td>    
            </tr>
            <tr>
                <td colspan="6">
                  <ul class=" list-unstyled margin-zero padding-zero">
                    <li>Terms and Conditions:</li>
                    <li>1) Objection if any to this bill should be raised within 7 days of this receipt or other wise it will be considered as accepted by you.</li>
                    <li>2) No claim for damage, breakage and/or pilferage after delivery is given or bill is prepared and/or in transit shall be entertained.</li>
                    <li>3) This is a computer generated Sales Return and does not require Signature.</li>
                    <li>4) Returns of goods as per Return Policy and the sole discretion of the Company.</li>
                    <li>5) Goods Delivery at Mumbai</li>
                    <li>6) E.& O.E.</li>
                  </ul>
                </td>
            </tr>
            <tr>
              <td colspan="3" class="text-left align-top border-right" style="font-weight: 600;">Receiver Sign & Seal</td>
              <td colspan="3" class="align-top border-right" style="font-weight: 600;">
                <p class="text-right margin-zero">For Diamond Mela Jewels Limited</p>
                <p class="text-right margin-zero" style="margin-top: 100px;">Authorised Signatory</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
</body>
</html>