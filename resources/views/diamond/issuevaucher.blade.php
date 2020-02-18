<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">

    <title>Issue Voucher</title>
  </head>
  <style type="text/css">
    body{letter-spacing: 0.6px;font-size: 16px;color:#000;font-family:aparaj;line-height: 22px;}
    .m-lr-auto{margin-left:auto;margin-right: auto;}
    .width-100{width: 100%;}
    .width-80{width:80%;}
    .width-70{width:70%;}
    .width-60{width:60%;}
    .col-two > div{width: 50%;box-sizing: border-box;}
    .width-200{width: 200px;}
    .h-100px{height: 100px;}
    .h-200px{height: 200px;}
    .height-big td{height: 200px;}
    .height-small td{height: 24px;}
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
    .padding-zero{padding: 0;}
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
    .table{border-collapse: collapse;width: 100%;}
    .table thead th{font-weight: 500;text-align: left;padding: 5px;vertical-align: top;}
    .table, .table thead th, .table tbody td{border:1px solid #040404;}
    .table tbody td{padding-left:2px;padding-right:2px;}
    .table tbody th{position: relative;}
    .table tbody tr:last-of-type th{padding:5px;}
    .align-top{vertical-align: top;}
    .align-fit-bottom{position: relative;right: 5px;top: 30px;}
    .list-unstyle li{list-style-type: none;}
    thead {
        display: table-header-group;
    }
    tfoot {
        display: table-row-group;
    }
    tr {
        page-break-inside: avoid;
    }
    .name-td{vertical-align: baseline;}
</style>
  </style>
  <body>
<div class="issue-voucher-recovered width-100 m-lr-auto margin-top-2 padding-all border-extra-light bg-extra-light">
<?php $logoname = Config::get('constants.pdf_logo.name');?>
  <!-- header start -->
  <div>
    <div class="logo text-center">
      <img src="<?=public_path('/img/logo.png');?>" class="width-200">
       <p><?php echo '<b>' . $logoname . '</b>'; ?></p>
    </div>
    <div class="text-center">
      <p>2307, Floor-23 Panchratna, Opera House, Charni Road, Mumbai-400004 Ph: 022-49664989 </p>
      <h5 class="text-uppercase fw-600 fs-seventeen margin-tb-10">Issue Voucher</h5>
      <p>Invoice issued under section 31 of the CGST Act, 2017 read with Rule 46 of Central Goods and Services Tax Rules, 2107 </p>
    </div>
  </div>
  <!-- header end -->
  <section>
    <div class="tax-invoice margin-top-3">
       <div class="flex-justify-space-between">
        <div class="label-type">
        <table class="width-100">
        <tr>
          <td colspan="2"><label class="fw-600 text-uppercase">cin:</label> U74999MH2014PLC2603299</td>
          <td colspan="2" align="right"><label class="fw-600 text-uppercase">gstin:</label> 27AAFCD2233A1ZB</td>
        </tr>
      </table>
        </div>
      </div>


      <!-- detail start -->
      <div class="table-content">
        <!-- table start -->

        <table class="table border">
          <tbody>
            <tr>

              <td colspan="4" class="name-td">

                <div class="col">
                  <p class="margin-zero">Name: <?php echo $name; ?></p>
                  <?php
if (!empty($address)) {
	?><p class="margin-zero">Address: <?php echo nl2br($address); ?></p><?php
}
?>
<?php
if (!empty($state)) {
	?><p class="margin-zero"><?php echo $state; ?></p><?php
}
?>
<?php
if (!empty($gstin)) {
	?><p class="margin-zero">GSTIN: <?php echo $gstin; ?></p><?php
}
?>
                </div>
              </td>
              <td colspan="6">

                <?php if ($data['data'][0]->is_voucher_no_generated == 1) {?>
                <div class="col">
                <label>Voucher No: <?php echo $issue_voucher_no; ?></label>
                  <span class="text-uppercase"></span>
                </div>
              <?php }?>

                <div class="col">
                 <label>Date: <?php echo $date; ?></label>
                 <span class="text-uppercase"></span>
                </div>
                <div class="col">
                 <label>Order No: <?php echo $data['data'][0]->order_no; ?></label>
                 <span class="text-uppercase"></span>
                </div>
                <div class="col">
                 <label>PO No: <?php echo $data['data'][0]->po_number; ?></label>
                 <span class="text-uppercase"></span>
                </div>
              </td>
            </tr>
            <tr>
              <td colspan="1" rowspan="2" class="text-center">Sr.No</td>
              <td colspan="9" class="text-center fw-600">Description</td>
            </tr>
            <tr class="text-center">
              <td colspan="2">&nbsp;</td>
              <td>MM/Seive</td>
              <td>Quality</td>
              <td>Pieces</td>
              <td>HSN</td>
              <td>Diamond. Wt.</td>
              <td>Shape</td>
              <td>AMT</td>
            </tr>
          <?php
$sr_no = 1;
$description = $description = Config::get('constants.enum.description_of_voucher.diamond');

$pieces = 1;
$units = "-";
$total_amount_paid = 0;
$total_diamond_weight = 0;
$total_diamond_pieces = 0;
$i = 0;
$HSN = Config::get('constants.enum.inventory_HSN.diamond_HSN');
if (count($data['data']) > 0) {
	?>
                    <?php foreach ($data['data'] as $value) {
		?>
            <tr class="height-big1 text-center">
              <td><?php echo ++$i; ?></td>
              <td colspan="2"><?php echo $description; ?></td>
              <td>
                <?php
if ($value->is_adjustable == 1) {
			echo $value->custom_mm_size;
		} else if ($value->is_adjustable == 0) {
			if (!empty($value->mm_size)) {
				echo $value->mm_size;
			} else {
				echo "-";
			}
		}

		echo "/";

		if ($value->is_adjustable == 1) {
			echo $value->custom_sieve_size;
		} else if ($value->is_adjustable == 0) {
			if (!empty($value->sieve_size)) {
				echo $value->sieve_size;
			} else {
				echo "-";
			}
		}?>
              </td>
              <td>
              <?php if ($value->is_adjustable == 0) {echo $value->diamond_quality;} else {echo $value->custom_stone_quality;}?>
              </td>
              <td><?php echo $value->pieces; ?></td>
              <td><?php echo $HSN; ?></td>
              <td><?php printf("%.3f", $value->diamond_weight);?></td>
              <td><?php echo $value->stone_shape; ?></td>
              <td class="text-right"><?php $value_amount_paid = (float) $value->amount_paid;?><?php printf("%.2f", $value_amount_paid);?></td>
               </tr>
            <?php
$amount_paid = $value->amount_paid;
		$total_amount_paid += round((float) $amount_paid, 2);
		$total_diamond_weight += round($value->diamond_weight, 3);
		$total_diamond_pieces += $value->pieces;
	}
}
if (count($data['data']) < 27) {
	$remaining_row_count = 27 - count($data['data']);
	for ($i = 0; $i < $remaining_row_count; $i++) {
		?>
      <tr class="height-big1 text-center">
        <td>&nbsp;</td>
        <td colspan="2">&nbsp;</td>
        <td>&nbsp;</td>
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
          <tr class="height-small">
              <td colspan="10"></td>
            </tr>
           <tr class="fw-600">
              <td colspan="3" class="text-right">&nbsp;</td>
              <td colspan="2" class="text-center">Total Assessable Value</td>
              <td class="text-center"><?=$total_diamond_pieces?></td>
              <td>&nbsp;</td>
              <td class="text-center"><?php printf("%.3f", $total_diamond_weight);?></td>
              <td>&nbsp;</td>
              <td class="text-right" colspan="1"><?php printf("%.2f", $total_amount_paid);?></td>
            </tr>
             <tr class="fw-600">
              <td colspan="5"></td>
              <td colspan="1"></td>
              <td colspan="1"></td>
              <td colspan="1"></td>
              <td colspan="1" class="text-center">Grand Total</td>
              <td colspan="1" class="text-right"><?php printf("%.2f", $total_amount_paid);?></td>
            </tr>
            <tr>
              <td colspan="10" class="fw-600">Terms and Conditions:</td>
            </tr>
             <tr>

              <td colspan="10">
                <ul class="list-unstyle padding-zero margin-zero">
                  <li>1) Objection if any to this should be raised within 7 days of this receipt or other wise it will be considered as
                      accepted by you.</li>
                  <li>2) No claim for damage, breakage and/or pilferage after delivery is given shall be entertained.</li>
                  <li>3) This is a computer generated Issue vaucher and does not require Signature.</li>
                  <li>4) Goods Delivery at Mumbai</li>
                  <li>5) E.& O.E.</li>
                  <li>Subject to Mumbai Jurisdiction</li>
                </ul>
              </td>
             </tr>
             <tr>
               <td colspan="10" class="text-center text-red fs-eighteen">
                 “The diamonds herein invoiced have been purchased from legitimate sources not
                  involved in funding conflict, in compliance<br>
                  with United Nations Resolutions and corresponding national laws.
                  The seller hereby guarantees that these diamonds are<br>
                  conflict free and confirms adherence to the World Diamond Council System of
                  Warranties Guidelines.”
               </td>
             </tr>
             <tr>
              <td colspan="10">
                <p class="margin-zero text-center text-black fw-600">No E-way bill is required to be generated as the goods covered under this Invoice/Challan/Approval
                   memo/Notes are exempted as per serial number 4/5 of Annexure to rule 138(14) of the CGST rules 2017. </p>
              </td>
            </tr>
            <tr>
              <th colspan="5" class="text-left h-100px align-top border-right">Job Work</th>
              <th colspan="5" class=" h-100px align-top">
                <p class="text-right margin-zero">For Diamond Mela Jewels Limited</p>
                <p class="text-right align-fit-bottom margin-zero1">Authorised Signatory</p>
              </th>
            </tr>
          </tbody>
        </table>
      </div>
  </section>
</div>

    <script src="https://code.jquery.com/jquery-2.2.4.js"></script>

  </body>
</html>