<table class="table table-striped table-bordered table-responsive" data-toggle="datatables">
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
    <?php 

    //echo "<pre>"; print_r($data);exit;
    $counter = 1; $i=0;?>
        @foreach($data as $rowkey => $datas)
          <?php echo '<b>Style</b> : '.(!empty($datas['style']) ? $datas['style'] : '');?><br/>
          <?php echo '<b>Item </b> : '.(!empty($datas['item']) ? $datas['item'] : ''); ?><br/>
          <?php echo '<b>PO No </b> : '.(!empty($datas['po_no']) ? $datas['po_no'] : ''); ?><br/>
          <?php echo '<b>Certificate No </b> : '.(!empty($datas['certificate_no']) ? $datas['certificate_no'] : ''); ?><br/>
          <?php echo '<b>Total SKU </b> : '.(!empty($datas['sku']) ? $datas['sku'] : ''); ?><br/>
          <?php echo '<b>Total Amount </b> : '.(!empty($datas['total_amount']) ? $datas['total_amount'] : ''); ?><br/>
          <?php echo '<b>Gross Weight </b> : '.(!empty($datas['gross_weight']) ? $datas['gross_weight'] : ''); ?><br/>
          <?php $counter++;?>
        @endforeach
  </td>
  <td>
    <?php $counter = 1; $i=0;?>
      @foreach($data as $rowkey => $datas)
        <?php echo '<b>Stone Use</b>  : '.(!empty($datas['stone_use']) ? $datas['stone_use'] : ''); ?><br/>
        <?php $category = App\Helpers\ProductHelper::_toGetCategoryVal($datas['categorys']['category_id']);?>
        <?php echo '<b>Stone Category</b>  : '.(!empty($category) ? $category : ''); ?><br/>
        <?php echo '<b>Stone</b>  : '.(!empty($stoneElem[$rowkey]['stone_stone']) ? $stoneElem[$rowkey]['stone_stone'] : ''); ?><br/>
        <?php echo '<b>Stone Shape</b>  : '.(!empty($stoneElem[$rowkey]['stone_shape']) ? $stoneElem[$rowkey]['stone_shape'] : ''); ?><br/>
        <?php echo '<b>Stone Seive Size</b>  : '.(!empty($stoneElem[$rowkey]['seive_size']) ? $stoneElem[$rowkey]['seive_size']  : ''); ?><br/>
        <?php echo '<b>Stone MM Size</b>  : '.(!empty($stoneElem[$rowkey]['mm_size']) ? $stoneElem[$rowkey]['mm_size'] : ''); ?><br/>
        <?php echo '<b>Stone Carat</b>   : '.(!empty($stoneElem[$rowkey]['carat']) ? $stoneElem[$rowkey]['carat'] : ''); ?><br/>
        <?php echo '<b>Stone Quality</b>  : '.(!empty($stoneElem[$rowkey]['stone_clarity']) ? $stoneElem[$rowkey]['stone_clarity'] : ''); ?><br/>
        <?php echo '<b>Stone Rate</b>  : '.(!empty($stoneElem[$rowkey]['stone_rate']) ? $stoneElem[$rowkey]['stone_rate'] : ''); ?><br/>
        <?php echo '<b>Stone Amount</b>  : '. (!empty($stoneElem[$rowkey]['stone_amount']) ? $stoneElem[$rowkey]['stone_amount'] : ''); ?><br/>
        <?php $counter++;?>
      @endforeach
  </td>
  <td>
    <?php $counter = 1; $i=0;?>
      @foreach($data as $rowkey => $datas)
        <?php $val = App\Helpers\ProductHelper::_toGetMetalQualityValue($datas['metals']['metal_quality_id']);?>
        <?php echo '<b>Metal Quality</b> :'.(!empty($val) ? $val : ''); ?><br/>
        <?php echo '<b>Metal Weight</b> : '.(!empty($datas['metals']['metal_weight']) ? $datas['metals']['metal_weight'] : ''); ?><br/>
        <?php echo '<b>Metal Rate</b> : '.(!empty($datas['metals']['metal_rate']) ? $datas['metals']['metal_rate'] : ''); ?><br/>
        <?php echo '<b>Metal Labour Charge</b> : '.(!empty($datas['metals']['metal_labour_charge']) ? $datas['metals']['metal_labour_charge'] : ''); ?><br/>
        <?php echo '<b>Metal Amount</b> : '.(!empty($datas['metals']['metal_amount']) ? $datas['metals']['metal_amount'] : '') ?><br/>
        <?php $counter++;?>
      @endforeach
  </td>
    
                            
                            
</tr>
</tbody>
</table>