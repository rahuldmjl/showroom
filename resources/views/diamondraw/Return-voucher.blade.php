<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">

    <title>Return voucher</title>
  </head>
  <style type="text/css">
    body{letter-spacing: 0.6px;font-size: 16px;color:#000;font-family:aparaj;line-height: 22px;}
    .m-lr-auto{margin-left:auto;margin-right: auto;}
    .width-100{width: 100%;}
    .width-80{width:80%;}
    .width-70{width:70%;}
    .width-60{width:60%;}
    .width-200px{width: 200px;}
    .h-200px{height: 200px;}
    .text-center{text-align: center;}
    .text-left{text-align: left !important;}
    .text-right{text-align: right;}
    .fs-sixteen{font-size: 16px;}
    .fs-seventeen{font-size: 16px;}
    .fw-600{font-weight: 600;}
    .margin-top-2{margin-top: 2%;}
    .margin-top-3{margin-top: 3%;}
    .margin-tb-10{margin-top: 10px;margin-bottom: 10px;}
    .margin-zero{margin: 0;}
    .margin-b-twentyfive{margin-bottom: 25px;}
    .text-uppercase{text-transform: uppercase;}
    .padding-zero{padding: 0;}
    .padding-all{padding:15px;}
    .padding-all-ten{padding: 10px;}
    .padding-b-one{padding-bottom: 1%;}
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
    .list-unstyle li{list-style-type: none;}
    .autorise-text{margin-top: 120px;}
  </style>
  <body>
<?php $logoname = Config::get('constants.pdf_logo.name');?>
<div class="Return-voucher width-100 m-lr-auto margin-top-2 padding-all border-extra-light bg-extra-light">
  <!-- header start -->
  <header>
    <div class="logo text-center padding-b-one">
      <img src="<?=public_path('/images/logo.png');?>" class="width-200px">
        <p><?php echo '<b>'.$logoname.'</b>'; ?></p>
    </div>
    <div class="text-center">
      <p>2307, Floor-23 Panchratna, Opera House, Charni Road, Mumbai-400004 Ph: 022-49664999 </p>
      <h5 class="fw-600 fs-seventeen margin-tb-10">Return Voucher</h5>
      <p>Invoice issued under section 31 of the CGST Act, 2017 read with Rule 46 of Central Goods and Services
         Tax Rules,2017 </p>
    </div>
  </header>
  <!-- header end -->
  <section>
    <div class="Return-voucher-content margin-top-3">
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
          <thead>
            <tr>
              <th colspan="2">
                <p class="margin-zero">To,</p>
                  <div class="col">
                    <span>

                      Name: <?php echo $diamondraw->vendor_name;?></span><br>
                      <span>Email: <?php echo $userdata[0]->email;?></span><br>
                      <span>Mob No: <?php echo $userdata[0]->phone;?></span><br>

                <span></span>
                </div>

              </th>
              <th colspan="2">
                <div class="col">
                  <label>Return No: <?php echo $returnno;?></label>
                  <span class="text-uppercase"></span>
                </div>
                 <div class="col">
                  <label>LOT No: <?php echo $diamondraw->packet_name;?></label>
                  <span class="text-uppercase"></span>
                </div>
                <div class="col">
                 <label>Date to: <?php echo $date;?></label>
                 <span class="text-uppercase"></span>
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="2">
                <div class="col">
                  <label>Place of Supply :</label>
                  <span class="text-uppercase"></span>
                </div>
                <div class="col">
                 <label>Mode of payment </label>
                 <span class="text-uppercase"></span>
                </div>
                <p class="margin-zero"></p>
              </td>
              <td colspan="2"></td>
            </tr>
            <tr class="text-center">
              <td>Sr.No</td>
              <td>Description of good</td>
              <td>HSN/SAC Code</td>
              <td>Qty (in cts)</td>
            </tr>
            <tr class="text-center">
              <td class="h-200px align-top">1</td>
              <td class="h-200px align-top"> cut & diamond polish </td>
              <td class="h-200px align-top">7102</td>
              <td class="h-200px align-top"><?php print_r($total_weight);?></td>
            </tr>
            <tr>
              <td colspan="4">
                <ul class="list-unstyle padding-zero margin-zero">
                  <li>Terms and Conditions:</li>
                  <li>1) Objection if any to this should be raised within 7 days of this receipt or other wise it will be considered as
                      accepted by you.</li>
                  <li>2) No claim for damage, breakage and/or pilferage after delivery is given shall be entertained.</li>
                  <li>3) This is a computer generated return voucher and does not require Signature.</li>
                  <li>4) Returns of goods as per Return Policy and the sole discretion of the Company.</li>
                  <li>5) Goods Delivery at Mumbai</li>
                  <li>5) E.& O.E.</li>
                </ul>
              </td>
             </tr>
            <tr>
              <th colspan="2" class="text-left align-top border-right">Receiver Sign & Seal</th>
              <th colspan="2" class="align-top">
                <p class="text-right">For Diamond Mela Jewels Limited</p>
                <p class="text-right autorise-text">Authorised Signatory</p></th>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

    <script src="https://code.jquery.com/jquery-2.2.4.js"></script>

  </body>
</html>