<?php
use App\Helpers\InventoryHelper;
$customerFirstName = isset($customerAddress['firstname']) ? $customerAddress['firstname'] : '';
$customerLastName = isset($customerAddress['lastname']) ? $customerAddress['lastname'] : '';
?>
<p><?=$customerFirstName.' '.$customerLastName?></p>
<p><?=isset($customerAddress['street']) ? $customerAddress['street'] : ''?></p>
<p>
  <?=isset($customerAddress['city']) ? $customerAddress['city'] . ', ' : ''?>
  <?=isset($customerAddress['region']) ? $customerAddress['region'] . ', ' : ''?>
  <?=isset($customerAddress['postcode']) ? $customerAddress['postcode'] : ''?>
</p>
<p><?=isset($customerAddress['country_id']) ? InventoryHelper::getCountryName(isset($customerAddress['country_id']) ? $customerAddress['country_id'] : 'IN') : ''?></p>
<p>T: <?=isset($customerAddress['telephone']) ? $customerAddress['telephone'] : ''?></p>