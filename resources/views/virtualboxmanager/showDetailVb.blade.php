<?php 
use App\Helpers\ProductHelper;
use App\Helpers\ShowroomHelper;
$total_diamond_carat = 0;
$total_metal_weight = 0;
$gtotal = 0;
?>
<table class="table table-bordered" data-toggle="datatables">
  <thead>
    @foreach($data as $values)
    <tr class="bg-light text-dark">
      <td colspan="10"><?php echo'<b>Product'.' '.$i++.'</b>'?></td>
    </tr>
    <tr>
      <td colspan="5">
        <?php 
          echo  '<b>Product Name </b> : <br/>';
          echo  '<b>SKU </b> : <br/>';
          echo  '<b>Diamond Quality </b> : <br/>';
          echo  '<b>Diamond Shape </b> : <br/>'; 
          echo  '<b>Diamond Carat </b> : <br/>';
          echo  '<b>Metal Quality </b> : <br/>';
          echo  '<b>Metal Weight </b> : <br/>';
          echo  '<b>Product Price </b> : <br/>';      
        ?>
      </td>
      <td colspan="5">
          
          <?php               
              echo (!empty($values->name) ? $values->name : ' - ') .'<br/>';
              echo (!empty($values->sku) ? $values->sku : ' - ') .'<br/>';
              echo (!empty($values->rts_stone_quality) ? $values->rts_stone_quality : ' - ') .'<br/>';
              echo (!empty(ProductHelper::_toGetDiamondShapeValue($values->stone_shape)) ? ProductHelper::_toGetDiamondShapeValue($values->stone_shape):'   -   ' ) .'<br/>';
              echo (!empty($values->total_carat) ? $values->total_carat : '') .'<br/>';
              echo (!empty(ProductHelper::_toGetMetalQualityValue($values->metal_quality)) ? ProductHelper::_toGetMetalQualityValue($values->metal_quality) : '') .'<br/>';
              echo (!empty($values->metal_weight) ? $values->metal_weight : '') .'<br/>'; 
              echo (!empty(ShowroomHelper::currencyFormat(round($values->custom_price))) ? ShowroomHelper::currencyFormat(round($values->custom_price)) : '').'<br/>';
          ?>
          
      </td>
    </tr>
    @endforeach

    <tr class="bg-light text-dark">
      <td colspan="10"><?php echo'<b>Total</b>';?></td>
    </tr>
    
      @foreach($data as $value)
        <?php
        $total_diamond_carat += round($value->total_carat, 3);
        $total_metal_weight += $value->metal_weight;
        $gtotal += round($value->custom_price);
        ?>
      @endforeach
      <tr>
        <td colspan="5">
        <?php 
              echo  '<b>Total Metal Weight </b>: <br/>'; 
              echo  '<b>Total Diamond Carat </b>: <br/>';  
              echo  '<b> Grand Total </b> : <br/>';
        ?>
        </td>
          <td colspan="5">
            <?php 
              echo  $total_metal_weight.'<br/>'; 
              echo  $total_diamond_carat.'<br/>';
              echo  ShowroomHelper::currencyFormat(round($gtotal)).'<br/>'; 
            ?>
          </td>
        </tr>
      <?php /*<tr>
      <td colspan="5">
        
      </td>
      <td colspan="5">
        <?php 
          echo  '<b>Total Pieces </b> : '.$total_diamond_pieces.'<br/>'; 
          echo  '<b>Total Weight </b> : '.$total_diamond_weight.'<br/>';
          echo  '<b>Grand Total </b> : '.$total_amount_paid.'<br/>';          
        ?>
      </td>
    </tr> */?>
  </thead>
</table>  
