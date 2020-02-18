<?php
use App\Helpers\InventoryHelper;
use App\Helpers\CustomersHelper;
use App\QuotationData;

$shapeArr = config('constants.enum.diamond_shape');//get stone shape for accordian
?>
@extends('layout.mainlayout')

@section('title', 'Quotation')

@section('distinct_head')
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      <!-- /.page-title-right -->
  </div>
  <!-- /.page-title -->
  <!-- =================================== -->
  <!-- Different data widgets ============ -->
  <!-- =================================== -->
  	<div class="widget-list">
      	<div class="row">
  			<div class="col-md-12 widget-holder">
  				<div class="widget-bg">
  					<div class="row mr-l-5 label-text-pl-25">
  						<div class="w-50">
  							<label class="col-form-label">Customer: {{$customerName}}</label>
  						</div>
  					</div>
  					<div class="row mr-l-0 label-text-pl-25">
  						  <div class="accordion w-100" id="quotation-accordion" role="tablist" aria-multiselectable="true">
                    <div class="card card-outline-primary">
                        <div class="card-header" role="tab" id="heading4">
                            <h5 class="m-0"><a role="button" data-toggle="collapse" data-parent="#quotation-accordion" href="#diamond_data" aria-expanded="true" aria-controls="diamond_data">Diamond Detail</a></h5>
                        </div>
                        <!-- /.card-header -->
                        <div id="diamond_data" class="card-collapse collapse show" role="tabpanel" aria-labelledby="heading4">
                            <div class="card-body">
                            	<div class="tabs w-100">
          				  							<ul class="nav nav-tabs">
          				  								<?php $activeClass='active';foreach ($shapeArr as $key => $shape):?>
                                    <?php 
                                      $stringIndex = strcspn( $key , '0123456789' );
                                      list($start, $end) = preg_split('/(?<=.{'.$stringIndex.'})/', $key, 2);
                                      $endChar = !empty($end) ? '-'.$end : '';
                                      ?>
          				  									<li class="nav-item <?php echo $activeClass?>"><a class="nav-link" href="#<?php echo $key?>_shape" data-toggle="tab"><?php echo ucfirst($start).$endChar;?></a></li>
          				  								<?php $activeClass='';endforeach;?>
	                                </ul>
  						                    <div class="tab-content p-3 border border-top-0">
  						                    	<?php $activeClass='active';
                                    //echo "<pre>";
                                    //print_r($stoneRangeData);exit;
  						                    	foreach ($shapeArr as $shapekey => $shape):?>
  						                    		<div class="tab-pane <?php echo $activeClass;?>" id="<?php echo $shapekey;?>_shape">
                                        <?php if(isset($diamondShapeData[$shapekey])):?>
  						                    			<?php foreach ($diamondShapeData[$shapekey] as $diamond)
                                        {
                                            $stoneQuality = '';
                                            $diamondShape = '';
                                            $rangeData = CustomersHelper::getCustomerStoneRangeData($diamond['diamondShape'],$diamond['stone_quality'],$id);
                                        ?>
                                        <div class="form-group">
                                            <div class="col-12 px-0 stone-data-container">
                                                <h6 class="w-100 shape-title"><?php echo isset($diamond['diamondShape']) ? ucfirst($diamond['diamondShape']) : ''?> (<?php echo isset($diamond['stone_quality']) ? $diamond['stone_quality'] : ''?>)</h6>
                                                <div class="row m-0 py-3">
                                                  <?php
                                                  $diamondShape = isset($rangeData->stone_shape) ? $rangeData->stone_shape : '';
                                                  $stone_range_data = json_decode($rangeData->stone_range_data);
                                                  $quotation = DB::table("quotation")->select("labour_charge")->where("id","=",DB::raw("$rangeData->quotation_id"))->get()->first();
                                                  $labour_charge = isset($quotation->labour_charge) ? json_decode($quotation->labour_charge) : '';
                                                  ?>
                                                    <?php foreach($stone_range_data->stone_range as $index=>$stoneRange):?>
                                                      <div class="w-15 col-md px-1">
                                                          <label class="w-100 text-center" for="<?= $stoneRange.$diamond['stone_quality'].$index?>"><?php echo $stoneRange?></label>
                                                          <input type="text" class="form-control" name="stone_data[<?= isset($diamond['stone_quality']) ? $diamond['stone_quality'] : ''?>][stone_price][]" id="<?= $stoneRange.$diamond['stone_quality'].$index?>" value="<?php echo isset($stone_range_data->stone_price[$index]) ? $stone_range_data->stone_price[$index] : ''?>" readonly>
                                                      </div>
                                                    <?php endforeach;?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        }
                                        ?>
                                        <div class="form-group row p-3">
                                            <div class="w-15">
                                                <label class="text-center" for="txtlabourcharge_<?= $shapekey?>">Metal Labour Charge: </label>
                                                <input type="text" class="form-control" id="txtlabourcharge_<?= $shapekey?>" name="txtlabourcharge[<?= $shapekey?>][]" value="<?php echo isset($labour_charge->$shapekey[0]) ? $labour_charge->$shapekey[0] : ''?>" readonly>
                                            </div>
                                        </div>
                                        <?php else:?>
                                            <p>No products!</p> 
                                        <?php endif;?>
  						                    		</div>
  						                    	<?php $activeClass='';endforeach;?>
  						                    </div>
        				  						</div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card-collapse -->
                    </div>
                    <!-- /.panel -->
                </div>
  					</div>
  				</div>
  			</div>
  		</div>    
    </div>
  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->
@endsection
<style>
.shape-title
{
  background-color: #f2f2f2;
padding: 10px;
margin: 0;
color: #000;
text-transform: uppercase;
}
.stone-data-container
{
  border: 1px solid #e6e5e5;
}
</style>
@section('distinct_footer_script')
<?php DB::setTablePrefix('dml_');?>
<!-- <script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script> -->
@endsection   