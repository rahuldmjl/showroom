<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
?>
<head>
    <!-- Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i,900,900i" rel="stylesheet" />

    <title>Tax invoice</title>
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
    .d-table{display: table;}
    .d-table-cell{display: table-cell;}
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
    .col-two > div{width: 50%;box-sizing: border-box;}
    .h-100px{height: 100px;}
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
	.d-table{display:table;}
	.d-table-cell{display:table-cell;}
    .align-top{vertical-align: top;}
    .align-bottom{vertical-align: bottom;}
    .align-fit-bottom{position: absolute;right: 10px;bottom: 5px;}
    .border-top-none{border-top: none;}
    .productstr .border-bottom-none{border-bottom-color:#fff !important;}
    .margin-0{margin:0px;}
    .top-12{top: 12%;}
    .margin-t-10{margin-top:10px;}
    .margin-b-0{margin-bottom: 0px;}
    /*.invoice-table .productstr:last-child .border-bottom-none{border-bottom-color:#040404 !important;}*/
  </style>
  </head>
  <div class="Tax-invoice width-100 m-lr-auto margin-top-2 padding-all border-extra-light bg-extra-light">
      <!-- header start -->
      <header>
        <div class="logo text-center padding-b-one">
          <img src="<?=public_path('/img/logo.png');?>" class="logo width-200px" alt="">
        </div>
        <div class="text-center">
          <p>2307, Floor-23 Panchratna, Opera House, Charni Road, Mumbai-400004 Ph: 022-49664999 </p>
          <h5 class="text-uppercase fw-600 fs-seventeen margin-tb-10">TAX INVOICE</h5>
          <p>Delivery Challan issued under Rule 55 of Goods and Services Tax Rules, 2017</p>
        </div>
      </header>
      <!-- header end -->
      <?php 
      /*print_r($invoiceData);exit;*/
           $customerId = isset($invoiceData->customer_id) ? $invoiceData->customer_id : '';
           //echo "hi".$customerId;
           $customerBillingAddress = InventoryHelper::getDefaultBillingAddressByCustId($customerId);
           //echo "<pre>"; print_r($customerBillingAddress);exit;
           $stateCode = isset($customerBillingAddress['stateCode']) ? $customerBillingAddress['stateCode'] : '';
           $billingStreet = isset($customerBillingAddress['street']) ? $customerBillingAddress['street'] : '';
           $billingCity = isset($customerBillingAddress['city']) ? $customerBillingAddress['city'] : '';
           $billingRegion = isset($customerBillingAddress['region']) ? $customerBillingAddress['region'] : '';
           $billingEmail = isset($customerBillingAddress['email']) ? $customerBillingAddress['email'] : '';
           $billingTelephone = isset($customerBillingAddress['telephone']) ? $customerBillingAddress['telephone'] : '';
           $billingCountryId = isset($customerBillingAddress['country_id']) ? $customerBillingAddress['country_id'] : '';
           $billingCustomerName = $customerBillingAddress['firstname'] . " " . $customerBillingAddress['lastname'];
           $billingAddress = $billingStreet . $billingCity;
           $gstinNumber = isset($customerBillingAddress['gstin']) ? $customerBillingAddress['gstin'] : '';
        ?>
  <section>
    
    <div class="tax-invoice margin-top-3">
      <div class="d-table width-100">
        <div class="label-type d-table-cell">
         <label class="fw-600 text-uppercase">cin:</label>
         <span class="text-uppercase">U74999Mh2014plc2603299</span>
        </div>
        <div class="label-type d-table-cell text-right">
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
                  <!-- <div class="col">
                    <label class="margin-t-10">
                         <p class="margin-b-0 margin-t-10">Ship To: Chintan  grapes</p>
                         <p class="margin-0">test address, test address 2 Ahmedabad</p>
                         <p class="margin-0">9876543210</p>
                         <p class="margin-0">Gujarat</p>
                         <p class="margin-0">GSTIN:<?=$gstinNumber?></p>
                      </label>
                    <span class="text-uppercase"></span>
                  </div> -->
                <!-- </div> -->
              </th>
              <th colspan="3">
                <div class="col">
                  <label>Invoice No: <?=$invoiceData->invoice_number;?> </label>
                  <span class="text-uppercase"></span>
                </div>
                <div class="col">
                 <label>Invoice Date:<?=$invoiceData->created_at;?></label>
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
                                 <label>Order No./Approval Memo No.(if any): - </label>
                
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
              <td>Total Wt(Carat)</td>
              <td>Rate(Per Carat)</td>
              <td>Final Price</td>
            </tr>

      <?php 
      $HSN = Config::get('constants.enum.inventory_HSN.diamond_HSN');
      $price = 0;
       foreach($diamondColl as $invoice) { 
        $price = $price + $invoice->final_price;  
      }
      $counter = 1;
      foreach($diamondColl as $invoiceElem) { ?>
      <tr class="">
      <td class="text-center border-bottom-none"><?=$counter?></td>
      <td  class="border-bottom-none"><?='Cut & polish diamond'?></td> 
      <td class="border-bottom-none"><?php echo $HSN; ?></td>
      <td class="border-bottom-none"><?=$invoiceElem->diamond_weight?></td>
      <td class="border-bottom-none"><?=$invoiceElem->final_price?></td>
      
      <td class="border-bottom-none"><?=($invoiceElem->final_price*$invoiceElem->diamond_weight)?></td>
     
     </tr>
   <?php  $counter++; 
}
 if (count($diamondColl) < 10) {

  $remaining_row_count = 10 - count($diamondColl);
  for ($i = 0; $i < $remaining_row_count; $i++) {
    ?>
      <tr class="height-big1 text-center">
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
       </tr>
      <?php
}

}
    ?>

            <tr style="border-top: 2px solid;">
              <td colspan="2" class="text-left">
                <span>Shipping Delivery Through:</span><br>
                <span>If G.R./Vehicle/A.W No.</span><br>
                <span>Remarks (If any)</span>
              </td>
              <td colspan="2" class="align-top text-center">
                 <span>Payment Details of Our Bank : </span><br/>
                 DIAMOND MELA JEWELS LTD. KOTAK MAHINDRA BANK, OPERA HOUSE, AC NO. 7212019981, IFSC KKBK0001414 Diamond Mela Jewels LTD. HDFC Bank , Charni Road Branch, A/C No 50200010367715 , IFSC HDFC0000356<br>
              </td>
              <td colspan="2">
                <?php $total_taxable_value = $invoiceData->final_price; ?>
                 <span>Total Taxable Value: <?php echo InventoryHelper::convertNumberToCurrency($total_taxable_value); ?></span><br>
                 <?php $igst = $total_taxable_value * 0.03;?>
                 <span>IGST 3% : <?php echo InventoryHelper::convertNumberToCurrency($igst); ?></span><br>
                 <?php  $withGstValue  = $total_taxable_value + $igst;?>
                 <?php $roundingValue = round($withGstValue) - $withGstValue; ?>
                 <span>Rounding : <?php echo round($roundingValue, 2); ?></span><br>
                 <span>Grand Total: <?php echo InventoryHelper::convertNumberToCurrency(round($withGstValue)); ?></span>
              </td>
            </tr>
            <tr>
              <td colspan="6">
                <p class="margin-zero">Total Amount In Words :- <?php echo ucfirst(InventoryHelper::convertNumberToWords(round($withGstValue))) ?> </p>
              </td>
            </tr>
            <tr>
             <td colspan="6">
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
    <!-- </div> -->


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
        <div class="d-table width-100 col-two position-relative">
          <div class="col padding-all-ten h-200px border-right d-table-cell">
            <h4 class="margin-zero">Receiver Sign &amp; Seal</h4>
          </div>

        <div class="col padding-all-ten h-200px position-relative d-table-cell">
            <h4 class="margin-zero text-right">For Diamond Mela Jewels Limited</h4>
            <p class="position-relative top-12 text-right fw-600">Authorised Signatory</p>
          </div>
        </div>
      </div>
    </div>
  </section>

</div>
<?php //exit; ?>