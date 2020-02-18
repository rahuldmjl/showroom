<?php
$pieces = 1;
$total_amount_paid = 0;
$total_diamond_weight = 0;
$total_diamond_pieces = 0;
$i = 0;
?>
<table class="table table-bordered" data-toggle="datatables">
  <thead>
    <tr class="bg-light text-dark">
      <td colspan="10"><?php echo'<b>Vendor Details</b>';?></td>
    </tr>
    <tr>
      <td colspan="5">
        <?php 
          echo  '<b>Voucher No </b> : <br/>';
          echo  '<b>PO No </b> : <br/>';
          echo  '<b>Date </b> : <br/>';
        ?>
      </td>
      <td colspan="5">
          @foreach($data as $values)
          <?php               
              echo (!empty($values->issue_voucher_no) ? $values->issue_voucher_no : ' - ') .'<br/>';
              echo (!empty($values->po_number) ? $values->po_number : ' - ') .'<br/>';
              echo (!empty($values->purchased_at) ? $values->purchased_at : ' - ') .'<br/>';
          ?>
          @if ($values->issue_voucher_no = 2)
              @break
          @endif
          @endforeach
      </td>
    </tr>
   
      @foreach($data as $value)
        <?php
        $amount_paid = $value->amount_paid;
        $total_amount_paid += round((float) $amount_paid, 2);
        $total_diamond_weight += round($value->diamond_weight, 3);
        $total_diamond_pieces += $value->pieces;
        ?>
        <tr class="bg-light text-dark">
          <td colspan="10"><?php echo'<b>Diamond '.++$i.'</b>';?></td>
        </tr>
        <td colspan="5">
          <?php 
              echo '<b>Diamond Weight </b> : <br/>'; 
              echo '<b>Diamond Pieces </b> : <br/>';
              echo '<b>Diamond Shape </b> : <br/>';  
              echo '<b>Diamond Quality </b> : <br/>';  
              echo '<b>Amount</b> : <br/>';
          ?>
        </td>
        <td colspan="5">
          <?php 
            echo (!empty($value->diamond_weight) ? $value->diamond_weight : ' - ') .'<br/>';
            echo (!empty($value->pieces) ? $value->pieces : ' - ') .'<br/>';
            echo (!empty($value->stone_shape) ? $value->stone_shape : ' - ') .'<br/>';
            echo (!empty($value->diamond_quality) ? $value->diamond_quality : ' - ') .'<br/>';
            echo (!empty($value->amount_paid) ? $value->amount_paid : ' - ') .'<br/>';
         ?>
        </td>
      @endforeach
      <tr>
      <td colspan="5">
        
      </td>
      <td colspan="5">
        <?php 
          echo  '<b>Total Pieces </b> : '.$total_diamond_pieces.'<br/>'; 
          echo  '<b>Total Weight </b> : '.$total_diamond_weight.'<br/>';
          echo  '<b>Grand Total </b> : '.$total_amount_paid.'<br/>';          
        ?>
      </td>
    </tr>
  </thead>
</table>  
