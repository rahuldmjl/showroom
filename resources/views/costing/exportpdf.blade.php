<?php 
  
  $rows_vendor = App\Costing::where('vendor_id',$vendors)->get();
  if(count($rows_vendor) > 0) {
  foreach ($rows_vendor as $rows_vendor) {

    $costing_ids[] = $rows_vendor['costing_id'];
  }
  }
  else {
    $costing_ids[] = 0;
  }

  if(!empty($chkcosting) &&  (empty($vendors) || $vendors == 0) ) {
      $collection = App\Costingdata::whereIn('id', $chkcosting )->get();
  }
  
  if(!empty($vendors) &&  empty($chkcosting)) {
      $collection = App\Costingdata::whereIn('costingdata_id', $costing_ids )->get();
  }

  if(!empty($chkcosting) &&  !empty($vendors)) {
      $collection = App\Costingdata::whereIn('costingdata_id', $costing_ids )->whereIn('id', $chkcosting )->get();
  }
 
  if(empty($chkcosting) &&  (empty($vendors) || $vendors == 0)) {
      $collection = App\Costingdata::all();
  }



if(count($collection) > 0 ) 
{
  if($estimationcatalogid  == 'catalog') 
  {
        $sheet = array();
	      $newSheetArr = array();
        $serialnumber=0;
        foreach ($collection as $sheetkey => $coll) {
          $newColumnsArr = array();
          $totalColumns = count($coll);
          $commaColumnsNum = 0;
          $commaColumnsArr = array();
          $tmparray =array();
          $serialnumber = $serialnumber + 1;
          array_push($tmparray,$serialnumber);
          $images =$coll->image;
          array_push($tmparray,$images);
          $item = $coll->item;
          array_push($tmparray,$item);
          $style = $coll->style;
          array_push($tmparray,$style);   
          $metalkarat = $coll->metal_karat;
          array_push($tmparray,$metalkarat);   
          $color = $coll->color;
          array_push($tmparray,$color); 
          $product_category = $coll->product_category;
          array_push($tmparray,$product_category);     
          $gross_weight = $coll->gross_weight;
          array_push($tmparray,$gross_weight);   
          $total_diamondpcs = $coll->diamond_pcs;
          array_push($tmparray,$total_diamondpcs);   
          $total_diamondwt = $coll->diamond_weight;
          array_push($tmparray,$total_diamondwt);   
          $colorstone_pcs = $coll->colorstone_pcs;
          array_push($tmparray,$colorstone_pcs); 
          $colorstone_weight = $coll->colorstone_weight;
          array_push($tmparray,$colorstone_weight); 
          $material_category = $coll->material_category;
          array_push($tmparray,$material_category);       
          $material_type = $coll->material_type;
          array_push($tmparray,$material_type);   
          $material_quality = $coll->material_quality;
          array_push($tmparray,$material_quality);   
          $material_mm_size = $coll->material_mm_size;
          array_push($tmparray,$material_mm_size);   
          $material_pcs = $coll->material_pcs;
          array_push($tmparray,$material_pcs); 
          $metal_weight = $coll->material_weight;
          array_push($tmparray,$metal_weight);     
          array_push($sheet,$tmparray);
      }
      foreach ($sheet as $rowkey => $rowData) {
        $forcount = 0;
        $newColumnsArr = array();
        $totalColumns = count($rowData);
        $commaColumnsNum = 0;
        $commaColumnsArr = array();
        foreach ($rowData as $colKey => $colValue) 
        {
            if(strpos($colValue, ',') !== false)
            {
                $colValues = explode(',', $colValue);
                $newColumnsArr[] = $colValues[0];
                $multiple_value_key[] = $colKey;
                $commaColumnsArr[$commaColumnsNum] = $colValues;
                $commaColumnsNum++;
                $forcount = count($colValues);
              } else {
                  $newColumnsArr[] = $colValue;
               }
                if($colKey == ($totalColumns-1))
                {
                  $newSheetArr[] = $newColumnsArr;
                  if($commaColumnsNum > 1){
                      for($col_j=0;$col_j<$forcount;$col_j++){
                        if($col_j == 0) continue;
                        $newDynColumnsArr = array();
                        $counterCol = 0;
                        for($col_i=0;$col_i<$totalColumns;$col_i++){
                          
                          if(in_array($col_i, $multiple_value_key)){

                            $newDynColumnsArr[] = $commaColumnsArr[$counterCol][$col_j];
                            $counterCol++;
                          } else {
                            $newDynColumnsArr[] = '';
                          }
                        }
                        $newSheetArr[] = $newDynColumnsArr;
                      }
                    }
                 }
              }
            }
     
     ?>
      <table border="1" class="costing_list_table" id="costing_list_table">
			<thead>
			<tr>
				<th>Sr No.</th>
				<th>Color or Hand Design</th>
				<th>Item#</th>
				<th>Style#</th>
				<th>Metal Karat</th>
				<th>Color</th>
				<th>Product Category</th>
				<th>Gross Wt</th>
				<th>Total Diamond Pcs</th>
				<th>Total Diamond Wt.</th>
				<th>Total Color Stone Pcs</th>
				<th>Total Color Stone Wt</th>
				<th>Material Category</th>
				<th>Material Type</th>
				<th>Material Inter. Quality</th>
				<th>Material MM Size</th>
				<th>Material pices</th>
				<th>Material Weight</th>
			</tr>
			</thead>
			<tbody>
				<?php

				$count = 1;
      foreach ($newSheetArr as $coll) { ?>
              <?php
              if(count(array_filter($coll)) == 0){ 
                continue;
              }
              ?>
      				<tr>
							<td><?php if(!empty($coll[0]))  { echo $count; }  ?></td>
							<td><img src="<?php echo $coll[1]; ?>" alt="" /></a></td>
							<td><?php echo $coll[2]; ?></td>
							<td><?php echo $coll[3]; ?></td>
							<td><?php echo $coll[4]; ?></td>
							<td><?php echo $coll[5]; ?></td>
							<td><?php echo $coll[6]; ?></td>
							<td><?php echo $coll[7]; ?></td>
							<td><?php echo $coll[8]; ?></td>
							<td><?php echo $coll[9]; ?></td>
							<td><?php echo $coll[10]; ?></td>
							<td><?php echo $coll[11]; ?></td>
							<td><?php echo $coll[12]; ?></td>
							<td><?php echo $coll[13]; ?></td>
							<td><?php echo $coll[14]; ?></td>
							<td><?php echo $coll[15]; ?></td>
              <td><?php echo $coll[16]; ?></td>
              <td><?php echo $coll[17]; ?></td>
							</tr>
				<?php 
				if(!empty($coll[0]))  { echo $count++; }
				 }
				 ?>
		</tbody>
  	</table>
  <?php } 
  if($estimationcatalogid  == 'estimation')
  {  
            $sheet =array();
            $serialnumber = 0;
            foreach ($collection as $coll) {
                $MetalWeight = $coll->metal_weight;
                $Color = $coll->color;
                $ProductCategory = $coll->product_category;
                $Diamondtype = $coll->material_type;
                $MetalKarat = $coll->metal_karat;
                $CalculatedLabourCharge = \App\Helpers\CostingHelper::getDiamondMelaLabourPrice($MetalWeight,$ProductCategory,$Diamondtype,$Color);
                $CalculatedMetalCharge = \App\Helpers\CostingHelper::getDiamondMelaMetalPrice($Color,$MetalKarat);
                $MaterialWeight =$coll->material_weight;
                $MaterialInterQuality = $coll->material_quality;
                $MaterialType = $coll->material_type;
                $MaterialPcs = $coll->material_pcs;
                $SeiveSize = explode(",",$coll->seive_size);
                $cntwt  = explode(",",$coll->material_weight);
                $Count = count($cntwt);
                $CalculatedStoneRate  = \App\Helpers\CostingHelper::getDiamondMelaStonePrice($MaterialWeight,$MaterialInterQuality,$MaterialType,$Count,$MaterialPcs);
                $tmparray =array();
                $serialnumber = $serialnumber + 1;
                array_push($tmparray,$serialnumber);
                $images =$coll->image;
                array_push($tmparray,$images);
                $item = $coll->item;
                array_push($tmparray,$item);
                $style = $coll->style;
                array_push($tmparray,$style);   
                $metalkarat = $coll->metal_karat;
                array_push($tmparray,$metalkarat);   
                $color = $coll->color;
                array_push($tmparray,$color); 
                $product_category = $coll->product_category;
                array_push($tmparray,$product_category);     
                $gross_weight = $coll->gross_weight;
                array_push($tmparray,$gross_weight);
                $metal_weight = $coll->metal_weight;
                array_push($tmparray,$metal_weight);
                $metalrate = $CalculatedMetalCharge;
                array_push($tmparray,$metalrate);
                $metalamount = round($metal_weight * $CalculatedMetalCharge);
                array_push($tmparray,$metalamount);
                $VendorCharge = $CalculatedLabourCharge;
                array_push($tmparray,$VendorCharge);
                if($metal_weight <= 1)
                { 
                  $fnMwt = 1;
                  $labouramount = round($fnMwt * $CalculatedLabourCharge);
                } 
                else
                {
                  $fnMwt = $metal_weight;
                  $labouramount = round($fnMwt * $CalculatedLabourCharge);
                }
                array_push($tmparray,$labouramount);
                $total_diamondpcs = $coll->diamond_pcs;
                array_push($tmparray,$total_diamondpcs);   
                $total_diamondwt = $coll->diamond_weight;
                array_push($tmparray,$total_diamondwt);   
                $colorstone_pcs = $coll->colorstone_pcs;
                array_push($tmparray,$colorstone_pcs); 
                $colorstone_weight = $coll->colorstone_weight;
                array_push($tmparray,$colorstone_weight); 
                $material_category = $coll->material_category;
                array_push($tmparray,$material_category);       
                $material_type = $coll->material_type;
                array_push($tmparray,$material_type);   
                $material_quality = $coll->material_quality;
                array_push($tmparray,$material_quality);   
                $seive_size = $coll->seive_size;
                array_push($tmparray,$seive_size);   
                $material_mm_size = $coll->material_mm_size;
                array_push($tmparray,$material_mm_size);   
                $material_pcs = $coll->material_pcs;
                array_push($tmparray,$material_pcs); 
                $material_weight = $coll->material_weight;
                array_push($tmparray,$material_weight); 
                
                $stone_amt = $CalculatedStoneRate['dataamt'];
                array_push($tmparray,$stone_amt);     
                $totalstoneamtsum = $CalculatedStoneRate['totalstoneamtsum'];
                //array_push($tmparray,$totalstoneamtsum);
                $total_amount = $metalamount + $labouramount + $totalstoneamtsum;
                array_push($tmparray,$total_amount);
                array_push($sheet,$tmparray);
            }
            
            $newSheetArr = array();
            foreach ($sheet as $sheetkey => $rowData) {
                $newColumnsArr = array();
                $totalColumns = count($rowData);
                $commaColumnsNum = 0;
                $commaColumnsArr = array();
                $forcount = 0;
                foreach ($rowData as $colKey => $colValue) {
                    if(strpos($colValue, ',') !== false){
                        $colValues = explode(',', $colValue);
                        $newColumnsArr[] = $colValues[0];
                        $multiple_value_key[] = $colKey;
                        $commaColumnsArr[$commaColumnsNum] = $colValues;
                        $commaColumnsNum++;
                        $forcount = count($colValues);
                    } 
                    else {
                        $newColumnsArr[] = $colValue;
                    }
                    if($colKey == ($totalColumns-1)){
                        if(count(array_filter($newColumnsArr)) > 0) { 
                            $newSheetArr[] = $newColumnsArr;
                        }
                        if($commaColumnsNum > 1) {
                            for($col_j=0;$col_j<$forcount;$col_j++) {
                                if($col_j == 0) continue;
                                    $newDynColumnsArr = array();
                                    $counterCol = 0;
                                    for($col_i=0;$col_i<$totalColumns;$col_i++){
                                        if(in_array($col_i, $multiple_value_key)){
                                            $newDynColumnsArr[] = $commaColumnsArr[$counterCol][$col_j];
                                            $counterCol++;
                                        }
                                        else {
                                            $newDynColumnsArr[] = '';
                                        }
                                    }
                                    if(count(array_filter($newDynColumnsArr)) > 0){ // for blank td
                                        $newSheetArr[] = $newDynColumnsArr;
                                    }
                            }
                        }
                    }
                } 
            }


      ?>
      <table border="1" class="costing_list_table" style="border-collapse: collapse;text-align: center;" id="costing_list_table">
			<thead>
			<tr>
				<th>Sr No.</th>
				<th>Color or Hand Design</th>
				<th>Item#</th>
				<th>Style#</th>
				<th>Metal Karat</th>
				<th>Color</th>
				<th>Product Category</th>
				<th>Gross Wt</th>
				<th>Total Metal Wt.</th>
				<th>Metal Rate</th>
        <th>Metal Amount</th>
        <th>Labour Rate</th>
        <th>Labour Amount</th>
				<th>Total Diamond Pcs</th>
				<th>Total Diamond Wt.</th>
				<th>Total Color Stone Pcs</th>
				<th>Total Color Stone Wt</th>
				<th>Material Category</th>
				<th>Material Type</th>
				<th>Material Inter. Quality</th>
        <th>Seive Size</th>
				<th>Material MM Size</th>
				<th>Material pices</th>
				<th>Material Weight</th>
        <th>Total Stone Amount</th>
				<th>Total Amount</th>
			</tr>
			</thead>
			<tbody>
				<?php
      $count = 1;
      foreach ($newSheetArr as $coll) { ?>
              <?php
              if(count(array_filter($coll)) == 0) { 
                continue;
              }
              ?>
              <tr>
              <td><?php if(!empty($coll[0]))  { echo $count; }  ?></td>
              <td><img src="<?php echo $coll[1]; ?>" alt="" /></a></td>
              <td><?php echo $coll[2]; ?></td>
              <td><?php echo $coll[3]; ?></td>
              <td><?php echo $coll[4]; ?></td>
              <td><?php echo $coll[5]; ?></td>
              <td><?php echo $coll[6]; ?></td>
              <td><?php echo $coll[7]; ?></td>
              <td><?php echo $coll[8]; ?></td>
              <td><?php echo $coll[9]; ?></td>
              <td><?php echo $coll[10]; ?></td>
              <td><?php echo $coll[11]; ?></td>
              <td><?php echo $coll[12]; ?></td>
              <td><?php echo $coll[13]; ?></td>
              <td><?php echo $coll[14]; ?></td>
              <td><?php echo $coll[15]; ?></td>
              <td><?php echo $coll[16]; ?></td>
              <td><?php echo $coll[17]; ?></td>
              <td><?php echo $coll[18]; ?></td>
              <td><?php echo $coll[19]; ?></td>
              <td><?php echo $coll[20]; ?></td>
              <td><?php echo $coll[21]; ?></td>
              <td><?php echo $coll[22]; ?></td>
              <td><?php echo $coll[23]; ?></td>
              <td><?php echo $coll[24]; ?></td>
              <td><?php echo $coll[25]; ?></td>
              
              </tr>
        <?php 
        if(!empty($coll[0]))  { echo $count++; }
         }
         ?>
				<?php } ?> 
		</tbody>
  	</table>

  <?php } else {
      echo "There are no Products";

  }  ?>