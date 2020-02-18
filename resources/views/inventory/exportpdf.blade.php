<style type="text/css">
	body{
		text-align: center !important;
	}
	@page { margin: 0; }
</style>
<?php 
foreach ($name as $namekey => $nameVal) { 
	//$path = public_path('images/');
	?>
	<img src="<?=$nameVal ?>" height="670" width="670" />
	
<?php } ?>