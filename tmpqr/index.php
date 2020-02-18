<!DOCTYPE html>
<html>
<head>
	<title>New QR Codes</title>
</head>
<body>
<?php
$imgs = scandir('/var/www/html/dmlsoftware/tmpqr/');
//var_dump($imgs);
echo '<table style="width:100%">';
$counter = 0;
foreach ($imgs as $im => $img) {
	$mediapath = '/var/www/html/dmlsoftware/tmpqr/' . $img;
	if (@is_array(getimagesize($mediapath))) {
		$image = true;
		//echo $img;
		//var_dump($counter);
		//var_dump($counter % 3);
		if ($counter % 3 == 0) {
			echo '<tr>';
		}
		$imgname = explode('_100', $img);
		echo '<th>' . $imgname[0] . '<br><img src="http://203.112.144.109/dmlsoftware/tmpqr/' . $img . '" /></th>';
		if ($counter % 3 == 2) {
			echo '</tr>';
		}
		$counter++;
	}
}
echo '</table>';
?>
</body>
</html>