<div class="row">
<?php $is_match = "col-md-6"; ?>
<div class=<?= $is_match ?> >
<?php 	
	$costingCollection = App\Costingdata::where('id',$id)->get(); 
	foreach ($costingCollection as $key => $costingColl) {
	$certificate_no = $costingColl->certificate_no; 
	if(in_array($certificate_no,$certificateArr)) {
	$showroomProducts = App\ShowroomOrderProducts::where('certificate',$certificate_no)->first(); ?>
	<table class="table table-striped table-bordered table-responsive" data-toggle="datatables">
		<tbody>
			<tr><th>Metal detail</th></tr>
			<tr>
				<td><?php echo 'Metal Weight :'.$showroomProducts->metal_weight .'<br/>'. 'Metal Quality :'?> </td>
			</tr>
			<tr><th>Diamond detail</th></tr>
			<tr>
				<td><?php echo '<strong>Certificate :</strong>'. $showroomProducts->certificate ."<br/>". 'Diamond Quality :'.$showroomProducts->diamond_quality."<br/>".'Total Diamond Pcs : '."<br/>". 'Total Diamond Wt :<p class="showroomWt">'.$showroomProducts->diamond_weight."</p><br/>". 'Gross Weight :'."<br/>"; ?></td>
			</tr>
		</tbody>
	</table>
	<?php  } else {
		$is_match = "col-md-12";
	}
	} ?>
</div>

<div class=<?= $is_match; ?> >
<table class="table table-striped table-bordered table-responsive" data-toggle="datatables">
<tbody>
	<?php 
	$costingdatas = App\Costingdata::where('id',$id)->get(); ?>
	@foreach ($costingdatas as $key => $costingdata) 
		<?php
		if(!empty($costingdata->seive_size)) {
		 $seive_size[$key] = explode(',',$costingdata->seive_size);
		}
		else {
			$seive_size[$key] = 0;
		}
		$material_mm_size[$key] = explode(',',$costingdata->material_mm_size);
		$material_pcs[$key] = explode(',',$costingdata->material_pcs);
		$material_weight[$key] = explode(',',$costingdata->material_weight);
		$material_type[$key] = explode(',',$costingdata->material_type);
		$maxVal = max(count($seive_size[$key]),count($material_mm_size[$key]),count($material_pcs[$key]),count($material_weight[$key]));?>
		<tr><th>Metal detail</th></tr>
		<tr>
		<td><?php echo 'Metal Weight :'.$costingdata->metal_weight .'<br/>'. 'Metal Quality :'.$costingdata->metal_karat; ?> </td>
		</tr>
		<tr><th>Diamond detail</th></tr>
		<tr>
		<td><?php echo '<strong>Certificate :</strong>'. $costingdata->certificate_no ."<br/>". 'Diamond Quality :'.$costingdata->material_quality."<br/>".'Total Diamond Pcs : '.$costingdata->diamond_pcs."<br/>". 'Total Diamond Wt :<p class="costingWt">'.$costingdata->diamond_weight."</p><br/>". 'Gross Weight :'.$costingdata->gross_weight."<br/>";
			for($count=0;$count<$maxVal;$count++) {
			echo '<strong>Diamond-'.($count+1).'</strong>'."<br>";
			echo 'Diamond Shape :' .$material_type[$key][$count]."<br/>";
			echo 'Seive Size :' .$seive_size[$key][$count]."<br/>";
			echo 'Material Size :' .$material_mm_size[$key][$count]."<br/>";echo 'Material Pcs :' .$material_pcs[$key][$count]."<br/>";
			echo 'Material Wt :' .$material_weight[$key][$count]."<br/>";
			} ?> 
		</td></tr>
	@endforeach     

</tbody>
</table>
</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		var costingWt = $('.costingWt').html();
		var showroomWt = $('.showroomWt').html();
		if(costingWt != showroomWt) {
			$('.costingWt').css("background","red");
			$('.showroomWt').css("background","red");
		}
		if(typeof(showroomWt)  === "undefined") {
			$('.costingWt').removeAttr('style');
			$('.costingWt').css("background","");
		}
	});
</script>