<?php 
use App\Helpers\ProductHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
?>
<table class="table table-bordered table-font-regular" data-toggle="datatables">
  <thead>
    <tr class="bg-light text-dark">
        <td colspan="10"><?php echo'<b>Product Detail</b>';?></td>
    </tr>
    <tr>   
      <td colspan="5">
          @foreach($data as $datas)
          @endforeach
          <?php
            $virtualproductposition = !empty(InventoryHelper::getVirtualProdPosition($datas->certificate_no)) ? InventoryHelper::getVirtualProdPosition($datas->certificate_no) : 'N/A';
          ?>
          <?php 
            echo '<b>Certificate No </b> :<br/>';
            echo '<b>Total SKU </b> : <br/>';
            echo '<b>Product Name </b> : <br/>';
            echo '<b>Product Status </b> : <br/>';
            echo '<b>Product Price </b> : <br/>';
            echo '<b>Product Category </b> : <br/>';
            echo '<b>Virtual Product Position </b> : <br/>';?>  
      </td>
      <td colspan="5">
        <?php echo (!empty($datas->certificate_no) ? $datas->certificate_no : '') .'<br/>';
         echo(!empty($datas->sku) ? $datas->sku : '').'<br/>';
         echo(!empty($datas->product_name) ? $datas->product_name : '').'<br/>';
        echo (!empty($datas->inventory_status_value) ? $datas->inventory_status_value : '').'<br/>';
         echo (!empty(ShowroomHelper::currencyFormat(round($datas->custom_price))) ? ShowroomHelper::currencyFormat(round($datas->custom_price)) : '').'<br/>';
         echo (!empty(ProductHelper::_toGetCategoryVal($datas->category_id)) ? ProductHelper::_toGetCategoryVal($datas->category_id) : '').'<br/>';
         echo $virtualproductposition;?>
      </td>
    </tr>
    <tr class="bg-light text-dark">
      <td colspan="10"><?php echo'<b>Metal detail</b>';?></td>
    </tr>
    <tr>
      <td colspan="5">
        <?php 
          echo  '<b>Metal Quality </b> : <br/>'; 
          echo  '<b>Metal Weight </b> : <br/>';
        ?>
      </td>
      <td colspan="5">
          <?php 
            echo (!empty(ProductHelper::_toGetMetalQualityValue($datas->metal_quality)) ? ProductHelper::_toGetMetalQualityValue($datas->metal_quality) : '') .'<br/>';
            echo (!empty($datas->metal_weight) ? $datas->metal_weight : '') .'<br/>';
         ?>
      </td>
    </tr>
    <tr class="bg-light text-dark">
      <td colspan="10"><?php echo'<b>Diamond detail</b>';?></td>
    </tr>
    <tr>
      <td colspan="5">
        <?php echo  '<b>Diamond Shape </b> : <br/>'; 
              echo  '<b>Diamond Quality </b> : <br/>';  
              echo  '<b>Total Carat </b> : <br/>';?>
      </td>
      <td colspan="5">
         <?php 
              echo (!empty(ProductHelper::_toGetDiamondShapeValue($datas->diamond_shape)) ? ProductHelper::_toGetDiamondShapeValue($datas->diamond_shape):'   -   ' ) .'<br/>';
              echo (!empty($datas->rts_stone_quality) ? $datas->rts_stone_quality : '   -   ') .'<br/>';
              echo (!empty($datas->total_carat) ? $datas->total_carat : '') .'<br/>';
         ?>
      </td>
    </tr>
  </thead>
</table>  
