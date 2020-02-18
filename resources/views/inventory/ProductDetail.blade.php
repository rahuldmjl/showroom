<?php 
use App\Helpers\ProductHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;

?>
<table class="table table-striped table-bordered table-font-regular table-responsive" data-toggle="datatables">
<thead>
  <tr>
      <th>Product Detail</th>
      <th>Diamond detail</th>
      <th>Metal detail</th>
  </tr>
  </thead>
  <tbody>
    <tr>    
  <td>
        @foreach($data as $datas)
        @endforeach
        <?php echo '<b>Certificate No </b> : '.(!empty($datas->certificate_no) ? $datas->certificate_no : ''); ?><br/>
        <?php echo '<b>Total SKU </b> : '.(!empty($datas->sku) ? $datas->sku : ''); ?><br/>
        <?php echo '<b>Product Name </b> : '.(!empty($datas->product_name) ? $datas->product_name : '');?><br/>
        <?php echo '<b>Product Status </b> : '.(!empty($datas->inventory_status_value) ? $datas->inventory_status_value : ''); ?><br/>
         <?php echo '<b>Product Price </b> : '.(!empty(ShowroomHelper::currencyFormat(round($datas->custom_price))) ? ShowroomHelper::currencyFormat(round($datas->custom_price)) : ''); ?><br/>
        <?php echo '<b>Product Category </b> : '.(!empty(ProductHelper::_toGetCategoryVal($datas->category_id)) ? ProductHelper::_toGetCategoryVal($datas->category_id) : ''); ?><br/>

          <?php
            $virtualproductposition = !empty(InventoryHelper::getVirtualProdPosition($datas->certificate_no)) ? InventoryHelper::getVirtualProdPosition($datas->certificate_no) : 'N/A';
          ?>
            <?php echo '<b>Virtual Product Position </b> : '.$virtualproductposition; ?>    
  </td>
 <td>
  @foreach($shape as $dshape)
   <?php echo '<b>Diamond Shape </b> : '.(!empty($dshape->value) ? $dshape->value:' - ' ); ?><br/>
 @endforeach
 
   <?php echo '<b>Diamond Quality </b> : '.(!empty($datas->rts_stone_quality) ? $datas->rts_stone_quality : ' - '); ?><br/>
 
   <?php echo '<b>Total Carat </b> : '.(!empty($datas->total_carat) ? $datas->total_carat : ''); ?><br/>
</td>
<td>
  <?php echo '<b>Metal Quality </b> : '.(!empty(ProductHelper::_toGetMetalQualityValue($datas->metal_quality)) ? ProductHelper::_toGetMetalQualityValue($datas->metal_quality) : ''); ?><br/>
   <?php echo '<b>Metal Weight </b> : '.(!empty($datas->metal_weight) ? $datas->metal_weight : ''); ?><br/>
 
  </td>    
                            
                            
</tr>
</tbody>
</table>