<table class="table table-small category_pdf_popup">
<?php foreach ($datas as $key => $data) {
	$ids = implode(",",$data);
 ?>
	<tr class="popup_elem">
		<th><?php  echo $key; ?></th>
		<td><a href="#" onclick="generateCategoryPDF('<?php echo $ids; ?>')" >Download Pdf</a></td> 
	</div>
<?php } ?>
</table>
<input type="hidden" id="exportProductPdfAction" value="<?=URL::to('/inventory/exportProductPdf');?>">
<script type="text/javascript">
	function generateCategoryPDF(productIds) {
		price = '<?= $price ?>';
		var url = $("#exportProductPdfAction").val()+'?productIds='+productIds+'&&price='+price;
		window.location.href = url;
	}
</script>