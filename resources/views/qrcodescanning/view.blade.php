<?php $data;
$metal_shape;
$quality;
use App\Helpers\ProductHelper;
?>
<table class="table table-striped table-bordered " data-toggle="datatables">
<thead>
		<tr>

			<th>Product Detail</th>
			<th>Diamond Detail</th>
			<th>Metal Detail</th>
		  	     
		</tr>
 
</thead>
 	<tbody>
		 <tr>
			  	<td> 
			  		
			  		@foreach($data as $value)
				  		Product Name : <?php echo $value->product_name;?> </br>
				  		Price : <?php echo CommonHelper::covertToCurrency($value->custom_price);?></br>
				  		SKU : <?php echo $value->sku;?> </br>
				  		Certificate No : <?php echo $value->certificate_no;?> </br>
				  		Inventory Status : <?php echo $value->inventory_status_value;?> </br>
			  		@endforeach
			  	</td>
			  	<td>
			  			@foreach($data as $value)
			      			Stone Shape : <?php echo ProductHelper::_toGetDiamondShapeValue($value->diamond_shape);?>
			      		@endforeach
			      	</br>
				
						@foreach($data as $mquality)
							Stone Quality : <?php echo $mquality->rts_stone_quality;?>
						@endforeach
				 </br>
					
					@foreach($data as $value) 	
						Total Carat : <?php echo $value->total_carat;?> </br>

					@endforeach

			   </td>
				<td>
					@foreach($data as $value)
						Metal Quality :<?php echo $value->metal_quality_value;?></br>
						Metal Weight : <?php echo $value->metal_weight;?> </br>
					@endforeach
				</td>


		  </tr>
	</tbody>
</table>