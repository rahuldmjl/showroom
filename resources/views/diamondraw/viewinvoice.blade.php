<?php
 use App\Helpers\InventoryHelper;
?>
<!DOCTYPE html>
<html>

<head>
	<title>Print Invoice | Dealermela</title>
	<!-- <link rel="stylesheet" href="http://123.108.51.11/skin/frontend/rwdnew/default/css/franchisees.css" /> -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i,900,900i" rel="stylesheet" />
</head>
<body>
<div class="custom_container">
	<div class="header">
     	<h4><?php echo Config::get('constants.message.invoice_header_title'); ?></h4>
     	<h4><?php echo 'Regd: '.Config::get('constants.message.invoice_regid'); ?></h4>
     	<a href="mailto:<?= Config::get('constants.message.invoice_email')?>"><?php echo 'Email: '.Config::get('constants.message.invoice_email'); ?></a><br>
     	<a href="<?= Config::get('constants.message.invoice_web_info.url')?>"  target="_blank"><?= Config::get('constants.message.invoice_web_info.text')?></a>
    </div>
    <div class="main">
        <h4><?php echo ('Return Memo') ?></h4>                
    </div>
    
	<div class="invoice">
		<div class="Detail_middle">
			<div class="width-50 detail_left">
            	<h4 class="title">Details Of Receiver(Billed to)</h4>
                <ul>
                    <li>Name: <?php print_r($diamondraw->vendor_name); ?></li>
                    <li>Email: <?php  print_r($userdata[0]->email); ?></li>
                    <li>Mob No: <?php print_r($userdata[0]->phone); ?></li>
                    <li>DMCODE: <?php print_r($userdata[0]->vendor_dmcode); ?></li>
                </ul>
            </div>
		</div>
		<?php  exit;?>
		<div class="invoice_table_content">
			<table class="invoice_table">
				<tbody>
					<tr>
		                <th>Sr No</th>
		                <th>Description of Raw Diamond</th>
		                <th>HSN code(GST)</th>
		                <th>Qty</th>
		                <th>Unit Price</th>
		                <th>Discount</th>
		                <th>Shipping Charge</th>
		                <th>Total</th>
		            </tr>
		            
		            	<tr>
		            		<td>1</td>
		            		<td>Rejected Raw Diamonds  <?php print_r($total_weight);?> </td>
		            		<td>71131930</td>
		            		
		            		<td></td>
		            		<td></td>
		            		<td></td>
		            		<td>0</td>
		            		<td><?php print_r($total_weight); ?></td>
		            	</tr>
		            <tr>

		            	<td colspan="6">Raw Diamond Total Weight</td>
		            	<td colspan="2"><?php echo $total_weight;?></td>
		            	
		        	</tr>
		            <?php
		            	/*$cgst = $grandTotalPrice * 0.015;
		            	$sgst = $grandTotalPrice * 0.015;*/
		            	?>
		            	<tr>
			            	<td colspan="6">Tax(CGST)</td>
			            	<td colspan="2"><?php //echo round($cgst);?></td>
			            </tr>
			            <tr>
			            	<td colspan="6">Tax(SGST)</td>
			            	<td colspan="2"><?php //echo round($sgst);?></td>
			            </tr>
		            <tr>
		        		<td colspan="6">Tax(IGST)</td>
		        		<td colspan="2"><?php //echo round($igst);?></td>
		        	</tr>
		        	
		         
		            <tr>
		            	<td colspan="6">Total Raw Diamonds (in figures)</td>
		            	<td colspan="2"><?php echo $total_weight;?></td>
		            </tr>
		            <tr>
		            	<td colspan="6">Total Raw Diamonds  (in word)</td>
		            	<td colspan="2"><?php echo ucfirst(InventoryHelper::convertNumberToWords(round($total_weight,0)))?></td>
		            </tr>

		            <tr>
		            	<td colspan="6"></td>
		            	<td colspan="2"><p>For Diamond Mela Jewels Ltd.</p><br>Auth. Sign:<br>Name:Mr.Girish Kothari<br> Designation: Director</td>
		            </tr>
				</tbody>
			</table>
		</div>
		<div class="footer">
            <p class="new">Returns of goods are subject Terms & Condition</p>
            <p class="new">This is a computer generated Invoice and does not require Signature</p>
            <p class="new">Thanks you for Shopping at diamondmela.com ! For Your special memories we are just a click away.</p>
            <p class="new">For more information on your order visit us on <b><a href="<?= Config::get('constants.message.invoice_web_info.url')?>"  target="_blank"><?= Config::get('constants.message.invoice_web_info.text')?></a></b></p>
            <p class="new">For details contact us on our number :<?= Config::get('constants.message.invoice_telephone')?></p>
            <p class="new">E.& O.E</p>
            <p class="new">Subject to Mumbai Jurisdiction</p>

        </div>
	</div>
</div>
</body>
</html>