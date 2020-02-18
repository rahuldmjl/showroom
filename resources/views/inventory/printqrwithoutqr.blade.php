<?php
//echo "<pre>";
//print_r($qrData);
//exit;
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Print QR</title>
		<link rel="stylesheet" type="text/css" href="http://diamondmela.com/skin/adminhtml/default/default/qrcode/css/form.css" media="all">
		<link rel="stylesheet" type="text/css" href="http://diamondmela.com/skin/adminhtml/default/default/print.css" media="print">
	</head>
	<body id="html-body" class=" qrcode-adminhtml-qrcode-customprint">
		<div class="wrapper">
            <div class="header">
            	<div class="clear"></div>
            </div>
            <div class="middle" id="anchor-content">
            	<div id="page:main-container">
            		<div id="messages"></div>
            		<style type="text/css">
					@page
					{
					    size: auto;   /* auto is the initial value */
					    /* this affects the margin in the printer settings */
					    margin:0.4px 3px 0 3px; /*top right bottom left*/
					}
					body{font-family: Verdana,Geneva, sans-serif!important;}
					  .print-qr{width:45cm;height: 1.7cm;padding: 0.2cm;overflow: hidden;margin-bottom:8px;font-weight: 700;}
					  .print-qr td{border:0;}
					  .print-qr td p{margin: 0;width: auto;font-size: 9px;display: inline;margin-right: 0.2cm;line-height: 8px;}
					  .print-qr img{width: 100%;height: 100%;}
					  .qrcode-print1, .qrcode-print2{width: 4.85cm;float: left;}
					  .qrcode-print1, .qrcode-print2 .st1{position: relative;top: 0px;}
					  .qrcode-print1 p, .qrcode-print2 p{font-size: 8pt;letter-spacing: 0.5pt;font-weight:bold;line-height: 14px;}
					  .qrcode-print2 .st1{width: 60%;float: left;}
					  .qrcode-print1 p.pricetag, .qrcode-print2 p.pricetag{font-size: 8pt;}
					  .qrcode-print2 .st1 p{text-align:left !important;padding-left: 2px;font-size:5.9pt;}
					  .qrcode-print2 span{width: 40%;display: inline-block;position: relative;top: -8px;}
					  .letter-spacing-inherit{letter-spacing:inherit !important;}
					  .font-size-sevenpt{font-size:7.7pt !important;}
					</style>
					<?php
foreach ($qrData as $qrDatakey => $qr) {

	$sku = explode(' ', $qr['sku']);
	$sku_to_print = $sku[0];
	if ($qr['metal_quality'] == "14K Rose Gold") {
		$Sortmquality = "14K(RG)";
	} elseif ($qr['metal_quality'] == "14K Yellow Gold") {
		$Sortmquality = "14K(YG)";
	} elseif ($qr['metal_quality'] == "14K White Gold") {
		$Sortmquality = "14K(WG)";
	} elseif ($qr['metal_quality'] == "14K Two Tone") {
		$Sortmquality = "14K(TT)";
	} elseif ($qr['metal_quality'] == "14K Three Tone") {
		$Sortmquality = "14K(THT)";
	} elseif ($qr['metal_quality'] == "18K Rose Gold") {
		$Sortmquality = "18K(RG)";
	} elseif ($qr['metal_quality'] == "18K Yellow Gold") {
		$Sortmquality = "18K(YG)";
	} elseif ($qr['metal_quality'] == "18K White Gold") {
		$Sortmquality = "18K(WG)";
	} elseif ($qr['metal_quality'] == "18K Two Tone") {
		$Sortmquality = "18K(TT)";
	} elseif ($qr['metal_quality'] == "18K Three Tone") {
		$Sortmquality = "18K(THT)";
	} elseif ($qr['metal_quality'] == "Platinum(950)") {
		$Sortmquality = "P(950)";
	}

	setlocale(LC_MONETARY, 'en_IN');

	$sortform = array('ROUND' => 'RD', 'MARQUISE' => 'MQ', 'PEAR' => 'PE', 'PRINCESS' => 'PRI', 'EMERALD' => 'EMD', 'OVAL' => 'OV', 'Cushion' => 'CUS', 'ASSCHER' => 'ASS', 'RADIANT' => 'RAD', 'HEART' => 'HRT', 'TRILLION' => 'TRIN', 'BAGUETTE' => 'BAG', 'TRIANGULAR' => 'TRI', 'SQUARE' => 'SQR', 'TAPER' => 'TAP', 'TAPER BAGUETTE' => 'TAB');
	?>
					<div class="print-qr">
						<div class="qrcode-print1">
							<p class="pricetag letter-spacing-inherit font-size-sevenpt">SKU: <?=$sku_to_print?></p>
							<p class="pricetag">C.No: <?=$qr['certificate_no']?></p>
							<p class="pricetag">D.WT: <?php if (!empty($qr['diamond_quality'])) {echo $qr['diamond_quality'] . ' | ';}?><?=$qr['diamond_total_weight']?></p>
							<p class="pricetag">M.WT: <?=$Sortmquality?> | <?=round($qr['metal_weight'], 2)?></p>
							<p class="pricetag">Price: <?=money_format('%!i', $qr['price'])?></p>
						</div>
						<div class="qrcode-print2">
							<div class="st1">
								<?php
foreach ($qr['diamonds'] as $diamondKey => $diamond) {
		?>
		<p><?=$diamondKey + 1?>:<?=$sortform[$diamond['shape']]?><?php if (!empty($diamond['totalcts'] && $diamond['totalcts'] > 0)) {echo '|' . round($diamond['totalcts'] * $diamond['stone_use'], 3);}if (!empty($diamond['mm_size'])) {echo '(' . $diamond['mm_size'] . ')';}?></p>
									<?php
}
	?>
							</div>
						</div>
					</div>
					<?php
}
?>
				</div>
			</div>
			<div class="footer"></div>
		</div>
		<div id="loading-mask" style="display:none">
			<p class="loader" id="loading_mask_loader"><img src="http://diamondmela.com/skin/adminhtml/default/default/images/ajax-loader-tr.gif" alt="Loading..."><br>Please wait...</p>
		</div>
	</body>
</html>