<?php
use App\Helpers\ProductHelper;
?>
<table class="table table-striped table-bordered table-responsive" data-toggle="datatables">
<thead>
  <tr>
        <?php $i=1;
        foreach($data as $invoicedata)
        {
          $count = count(json_decode($invoicedata->diamond_data)); 
          $diamondData = json_decode($invoicedata->diamond_data); 
          foreach ($diamondData  as $key=>$value) 
          {
            echo '<th> Diamond Detail :- '.($i++).'</th>';
          }
        }?>

  </tr>
  </thead>
  <tbody>
    <tr>
  
  <?php $i=1;
        foreach($data as $invoicedata)
        {
          
          $count = count(json_decode($invoicedata->diamond_data)); 
            $diamondData = json_decode($invoicedata->diamond_data); 
            foreach ($diamondData  as $key=>$value) {
                 
                  $sieve_size[$key] = $value->sieve_size ;
                  $mm_size[$key] = $value->mm_size; 
                  $diamond_weight[$key] = $value->diamond_weight; 
                  $stone_clarity[$key] = ProductHelper::_toGetDiamondClarityValue($value->stone_clarity);
                  $stone_shape[$key] = ProductHelper::_toGetDiamondShapeValue($value->stone_shape);
                  $final_price[$key] = $value->final_price;
                  $discount[$key] = $value->discount;
                   $maxVal = max(count($sieve_size),count($mm_size),count($diamond_weight));          


          ?>

            <td><?php echo 'Diamond Quality : '.$stone_clarity[$key]."<br/>".'Diamond Shape : '. $stone_shape[$key] ."<br/>".'Total Diamond Wt : '. $diamond_weight[$key] ."<br/><br/>";
              
              
              /*for($count=0;$count <$maxVal;$count++)
              {*/
              
                echo '<strong>Diamond-'.($i++).'</strong>'."<br/>";
                echo 'Seive Size : ' .$sieve_size[$key]."<br/>";
                echo 'MM Size : ' .$mm_size[$key]."<br/>";
                echo 'Diamond Wt : ' .$diamond_weight[$key]."<br/>";
                echo 'Final Price : ' .$final_price[$key]."<br/>";
                echo 'Discount : ' .(!empty($discount[$key])?$discount[$key]:'0.00')."<br/>";

              /*}*/


           }
            
        }

      
       ?> 
       </td>
      



                  
                  
  
</tr>
</tbody>
</table>