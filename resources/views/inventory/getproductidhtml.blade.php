<?php foreach($productIdCollection as $product):?>
	<option value="{{$product->entity_id}}" class="">{{$product->certificate_no}}</option>
<?php endforeach;?>