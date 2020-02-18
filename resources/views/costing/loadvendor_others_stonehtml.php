 <?php $count = $data['cnt'];?>
 <div id="MainDivForVendorStone_<?php echo $count; ?>" class="CostingVendorSheetData border-t-light-1 pt-3">
 <div class="row mb-3 position-relative">
   <div class="col-md-3 py-2">
   	<label for="diamond_quality">Stone Clarity :</label>
    <select class="form-control diamond_quality required" id="diamond_quality_<?php echo $count; ?>" data-elemnumber="<?php echo $count; ?>" name="diamond_quality[]">
      <option value="" class="dropdon">Select Stone Clarity</option>
      <?php
foreach ($data['stone_clarity'] as $row) {?>
          <option value="<?php echo $row->option_id; ?>"><?php echo $row->value; ?></option>
      <?php }?>
  	</select>
    </div>
    <div class="col-md-3 py-2">
  	<label for="stone_carat">Shape Carat :</label>
   	<select class="form-control stone_carat required" id="stone_carat_<?php echo $count; ?>" name="stone_carat[]">
    	<option value="">Select Shape Carat</option>
      <?php
foreach ($data['stone_carat'] as $row) {?>
        <option value="<?php echo $row->stone_carat_from . '-' . $row->stone_carat_to; ?>"><?php echo $row->stone_carat_from . '-' . $row->stone_carat_to; ?></option>
      <?php }?>
    </select>
    </div>
    <div class="col-md-3 py-2">
    <label for="stone_shape">Stone Shape :</label>
    <select class="form-control stone_shape required" id="stone_shape_<?php echo $count; ?>" data-shapeid="<?php echo $count; ?>" name="stone_shape[]">
      <option value="">Select Stone Shape</option>
      <?php
foreach ($data['stone_shape'] as $row) {?>
        <option value="<?php echo $row->option_id; ?>"><?php echo $row->stone_shape; ?></option>
      <?php }?>
    </select>
    </div>
    <div class="col-md-3 py-2 form-group py-2">
    	<label for="diamond_gold_price">Price :</label>
    	<input class="form-control vdr_price required" name="diamond_gold_price[]" type="text" value="" id="diamond_gold_price_<?php echo $count; ?>" placeholder="00">
    </div>
    <button type='button' class="btn_remove_<?php echo $count; ?> removebtn-style pointer" id='removebtn_stonedetail' data-id="<?php echo $count; ?>"><i class="fs-16 material-icons">close</i></button>
  </div>
  </div>

