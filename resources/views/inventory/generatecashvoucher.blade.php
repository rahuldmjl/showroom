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
    <title>Cash Voucher</title>
  </head>
  <style type="text/css">
    body{letter-spacing: 0.6px;font-size: 16px;color:#000;font-family:aparaj;line-height: 22px;}
    .width-100{width: 100%;}
    .rupees-icon{font-family: DejaVu Sans;font-weight: 400;}
    
    .text-center{text-align: center;}
    .text-left{text-align: left !important;}
    .text-right{text-align: right;}
    .text-uppercase{text-transform: uppercase;}
    .fs-seventeen{font-size: 16px;}
    .fw-600{font-weight: 600;}
    .margin-top-2{margin-top: 2%;}
    .margin-top-3{margin-top: 3%;}
    .margin-tb-10{margin-top: 10px;margin-bottom: 10px;}
    .border{border:1px solid #040404;}
    .border-extra-light{border:1px solid #f1efef;}
    .bg-extra-light{background-color: #fbfbfb;}
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
    .border-0{border:0px !important;}
  </style>
<body>
<?php 
$customerId = isset($voucherData->customer_id) ? $voucherData->customer_id : '';
$customerName = isset($customerId) ? InventoryHelper::getCustomerName($customerId) : '';
$invoiceNumber = isset($voucherData->invoice_id) ? InventoryHelper::getInvoiceNumber($voucherData->invoice_id) : '';
$currentYear = date('Y');
$nextYear = (date('y') + 1);
$voucherNumber = isset($voucherData->voucher_number) ? $voucherData->voucher_number : '';
$amount = isset($voucherData->invoice_amount) ? $voucherData->invoice_amount : 0;
$status = isset($voucherData->status) ? ucfirst($voucherData->status) : '';

?>
<div class="Sales-return-cumcredit width-100 m-lr-auto margin-top-2">
  <!-- header start -->
  <header>
    <div class="text-center">
      <h5 class="fw-600 fs-seventeen margin-tb-10">Diamond Mela Jewels Ltd - (<?= $currentYear?>-<?= $nextYear?>)</h5>
      <p>2307, 23rd,Floor - 21,</p>
      <p>Panchratna,Mama Parmanand Marg,Opera House,</p>
      <p>Girgaon,Mumbai - 400004</p>
      <p>U74999MH2014PLC260329</p>
      <p>State Name : Maharashtra, Code : 27</p>
      <p>CIN: U74999MH2014PLC260329</p>
    </div>
  </header>
  <!-- header end -->
  <section>
    <div class="delivery-challan-content margin-top-3">
      <table class="width-100">
        <tr>
          <td colspan="4" align="center"><label class="fw-600 text-uppercase">Payment Voucher</label></td>
        </tr>
        <tr>
          <td colspan="2"><label class="fw-600 text-uppercase">No:</label> <?= $voucherNumber?></td>
          <td colspan="2" align="right"><label class="fw-600 text-uppercase">Dated:</label><?= isset($voucherData->created_at) ? date('d-M-Y', strtotime($voucherData->created_at)) : ''?></td>
        </tr>
      </table>
      
      <!-- detail start -->
      <div class="table-content">
          <table class="table border width-100">
              <tr>
                  <td align="center"><p>Particulars</p></td>
                  <td align="center"><p>Amount</p></td>
              </tr>  
              <tr>
                  <td>
                    <table class="table border-0 width-100" style="border:0px !important;">
                        <tr>
                          <td style="border:0px !important;">
                              <p class="fw-600">Account :</p>      
                          </td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;padding-left: 4%;"><p><?= $customerName?></p></td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;padding-left: 4%;"><p>Agst Ref &nbsp;<span class="fw-600"><?= $invoiceNumber?></span>&nbsp;&nbsp;<span class="rupees-icon"><?= ShowroomHelper::currencyFormat(round($amount))?></span> Dr</p></td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;">
                              <p class="fw-600">Through :</p>      
                          </td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;padding-left: 4%;"><p><?= $status?></p></td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;">
                              <p class="fw-600">On Account of :</p>      
                          </td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;padding-left: 4%;"><p>Being Cash Refund against Return of Product</p></td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;">
                              <p class="fw-600">Amount (in words) :</p>      
                          </td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;padding-left: 4%;"><p>INR  <?php echo ucfirst(InventoryHelper::convertNumberToWords(round($amount))) ?></p></td>
                        </tr>
                    </table>
                  </td>
                  <td>
                    <table class="table border-0 width-100" style="border:0px !important;">
                        <tr>
                          <td style="border:0px !important;"><p class="rupees-icon"><?= ShowroomHelper::currencyFormat(round($amount))?></p></td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;">
                                <p>&nbsp;&nbsp;</p>
                          </td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;">
                                <p>&nbsp;&nbsp;</p>
                          </td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;">
                                <p>&nbsp;&nbsp;</p>
                          </td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;">
                                <p>&nbsp;&nbsp;</p>
                          </td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;">
                                <p>&nbsp;&nbsp;</p>
                          </td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;">
                                <p>&nbsp;&nbsp;</p>
                          </td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;">
                                <p>&nbsp;&nbsp;</p>
                          </td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;">
                                <p>&nbsp;&nbsp;</p>
                          </td>
                        </tr>
                        <tr>
                          <td style="border:0px !important;">
                                <p class="rupees-icon"> <?= ShowroomHelper::currencyFormat(round($amount))?></p>
                          </td>
                        </tr>
                        
                    </table>
                  </td>
              </tr>
              <tr>
                  <td colspan="2" style="padding-top: 30px;padding-bottom: 30px;">&nbsp;</td>
              </tr>
              <tr>
                  <td>
                      <p>Receiver’s Signature :</p>
                  </td>
                  <td>
                      <p>Authorised Signatory</p>
                  </td>
              </tr>
          </table>
      </div>
    </div>
  </section>
</div>
</body>
</html>