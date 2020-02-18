<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
use App\QuotationData;

$shapeArr = config('constants.enum.diamond_shape');
foreach ($quotationData as $key => $quotation) {
	$labourChargeValue = isset($quotation->labour_charge) ? json_decode($quotation->labour_charge) : '';
	$productData = isset($quotation->product_data) ? json_decode($quotation->product_data) : '';
}?>

<ul class="nav nav-tabs">
	<?php $activeClass='active';foreach ($shapeArr as $key => $shape):?>
		<?php 
		$stringIndex = strcspn( $key , '0123456789' );
		list($start, $end) = preg_split('/(?<=.{'.$stringIndex.'})/', $key, 2);
		$endChar = !empty($end) ? '-'.$end : '';
		?>
        <?php if(isset($diamondShapeData[$key])):?>
		<li class="nav-item <?php echo $activeClass?>"><a class="nav-link" href="#<?php echo $key?>_shape" data-toggle="tab"><?php echo ucfirst($start).$endChar;?></a>
		</li>
    <?php endif;?>
	<?php $activeClass='';endforeach;?>
</ul>
<div class="tab-content p-3 border border-top-0">
	<?php $activeClass='active';
	foreach ($shapeArr as $shapekey => $shape):?>
		<div class="tab-pane <?php echo $activeClass;?>" id="<?php echo $shapekey;?>_shape">
			<?php if(isset($diamondShapeData[$shapekey])):?>
				<?php foreach ($diamondShapeData[$shapekey] as $diamond){
					$stoneQuality = '';
        			foreach ($diamond as $key => $value) {
        				$stoneQuality = $value['stone_quality'];
        				?>
        				<div class="form-group">
                            <div class="col-12 px-0 stone-data-container">
        					   <h6 class="w-100 shape-title"><?php echo isset($value['diamondShape']) ? ucfirst($value['diamondShape']) : ''?> (<?php echo isset($value['stone_quality']) ? $value['stone_quality'] : ''?>)</h6>
                               <div class="row m-0 py-3">
                                <!-- <label class="col-auto px-1 col-form-label"><?php //echo isset($value['stone_quality']) ? $value['stone_quality'] : ''?></label> -->
                                <?php
                                $rangeData = array(
                                    'stone_shape'=>isset($value['diamondShape']) ? $value['diamondShape'] : '',
                                    'stone_quality'=>isset($value['stone_quality']) ? $value['stone_quality'] : '',
                                    'stone_range_data'=> json_encode($stoneRangeData)
                                    );
                                ?>
                                <input type="hidden" name="defaultstoneinfo[]" class="stoneRangeData" value='<?php echo json_encode($rangeData);?>'>
                            <?php foreach($stoneRangeData as $index=>$stone_range_data):?>
									<?php foreach($stone_range_data as $stoneRange):?>
                            	<?php
                            	//$stone__shape = $stoneData[trim($stoneClarity)]['stone_shape'][$index];
                            	$diamond_shape = isset($value['diamondShape']) ? $value['diamondShape'] : '';
$quotationData = DB::table("quotation_data")->select("*")->where("quotation_id","=",DB::raw("$quotationId"))->where("stone_shape","=",DB::raw("'$diamond_shape'"))->where("stone_quality","=",DB::raw("'$stoneQuality'"))->get()->first();
$stone_range_data = isset($quotationData->stone_range_data) ? json_decode($quotationData->stone_range_data) :'';
$stone_range_data = isset($stone_range_data) ? $stone_range_data : array();
                            	?>
                            	<div class="w-15 col-md px-1">
                            		<label class="w-100 text-center" for="stone_range_<?= $stoneRange->stone_carat_from?>_<?= $stoneRange->stone_carat_to?>_<?= $value['stone_quality']?>"><?php echo isset($stoneRange->stone_carat_from) ? $stoneRange->stone_carat_from : ''?> - <?php echo isset($stoneRange->stone_carat_to) ? $stoneRange->stone_carat_to : ''?></label>
                            		<input type="hidden" name="stone_data[<?=$stoneQuality?>][<?= $value['diamondShape']?>][stone_range][]" value="<?php echo isset($stoneRange->stone_carat_from) ? $stoneRange->stone_carat_from : ''?> - <?php echo isset($stoneRange->stone_carat_to) ? $stoneRange->stone_carat_to : ''?>">
                            		<input type="number" min="1" class="form-control diamond-caret-val" name="stone_data[<?=$stoneQuality?>][<?= $value['diamondShape']?>][stone_price][]" id="stone_range_<?= isset($stoneRange->stone_carat_from) ? $stoneRange->stone_carat_from : ''?>_<?= isset($stoneRange->stone_carat_to) ? $stoneRange->stone_carat_to : ''?>_<?= isset($value['stone_quality']) ? $value['stone_quality'] : ''?>" value="<?php echo isset($stone_range_data->stone_price[$index]) ? $stone_range_data->stone_price[$index] : ''?>" step="0.1">
                                </div>
									<?php endforeach;?>
                                <?php endforeach;?>
                                </div>
                            </div>
                        </div>
        			<?php
        			}
        		}
        		?>
        		<div class="form-group row p-3">
					<div class="w-15">
            		<label class="text-center" for="txtlabourcharge_<?= $shapekey?>">Labour Charge: </label>
            		<input type="number" class="form-control labour-charge-val" id="txtlabourcharge_<?= $shapekey?>" name="txtlabourcharge[<?= $shapekey?>][]" value="<?php echo isset($labourChargeValue->$shapekey[0]) ? $labourChargeValue->$shapekey[0] : ''?>">
	            </div>
			</div>
    		<?php else:?>
        		<p>No products!</p>	
			<?php endif;?>
			
			<div class="row">
				<p class="diamond-data-error"></p>
			</div>
		</div>
	<?php $activeClass='';endforeach;?>
	<!-- <div class="row">
		<div class="checkbox checkbox-primary">
            <label class="<?php echo ($isDefaultQuotation=='1') ? 'checkbox-checked' : ''?>">
                <input type="checkbox" name="chkDefaultQuotation" id="chkDefaultQuotation" <?php echo ($isDefaultQuotation=='1') ? 'checked' : ''?>> <span class="label-text">Set default quotation for this customer?</span>
            </label>
        </div>
	</div> -->
</div>