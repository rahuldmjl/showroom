<?php
$serialNo = 0;
foreach ($exhibitionData as $key => $exhibition) {
	$title = isset($exhibition->title) ? $exhibition->title : '';
	$place = isset($exhibition->place) ? $exhibition->place : '';
	$address = isset($exhibition->address) ? $exhibition->address : '';
	$address = strlen($address) > 50 ? substr($address,0,50)."..." : $address;
	$markup = isset($exhibition->markup) ? $exhibition->markup : 0;
	$qty = isset($exhibition->qty) ? $exhibition->qty : 0;
	$createdDate = isset($exhibition->created_at) ? date('d/m/Y h:i', strtotime($exhibition->created_at)) : '';
	$serialNo++;
	?>
	<tr>
		<td>{{$serialNo}}</td>
		<td>{{$title}}</td>
		<td>{{$place}}</td>
		<td>{{$address}}</td>
		<td>{{$markup}}</td>
		<td>{{$qty}}</td>
		<td>{{$createdDate}}</td>
		<td>
			<a target="_blank" class="color-content table-action-style" href="{{ route('viewexhibitionproducts',['id'=>$exhibition->id]) }}" alt="View Products" title="View Products"><i class="material-icons md-18">visibility</i></a>
			
			<a title="Edit Exhibition Detail" target="_blank" class="color-content table-action-style pointer btn-editexhibition" data-id="{{$exhibition->id}}"><i class="material-icons md-18">edit</i></a>
			
			<a title="Export Exhibition" class="color-content table-action-style" href="{{ route('generateexhibitionexcel',['id'=>$exhibition->id]) }}"><i class="material-icons md-18">insert_drive_file</i></a>
			

		</td>
	</tr>
<?php }?>