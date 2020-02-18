<div class="d-flex justify-content-between align-items-center">
<span>GSTIN: <?=$gstinNumber?></span>
<span class="text-right">
<?php if(empty($gstinNumber) && empty($gstinAttachment)):?>
    <a class="text-white pointer" onclick="addGstinPan('<?= $customerId?>','gstin')">Add</a>
<?php else:?>
    <a class="text-white pointer" onclick="editGstinPan('<?= $customerId?>','gstin')">Edit</a>
<?php endif;?>
<?php if(!empty($gstinAttachment)):?>
    <a class="text-white pointer" onclick="viewAttachment('<?= $customerId?>','gstin')">View</a>
<?php endif;?>
</span>
</div>
<div class="d-flex justify-content-between align-items-center">
<span>PAN Card No: <?=$panCardNumber?></span>
<span class="text-right">
<?php if(empty($panCardNumber) && empty($panCardAtttachment)):?>
    <a class="text-white pointer" onclick="addGstinPan('<?= $customerId?>','pan_card')">Add</a>
<?php else:?>
    <a class="text-white pointer" onclick="editGstinPan('<?= $customerId?>','pan_card')">Edit</a>
<?php endif;?>
<?php if(!empty($panCardAtttachment)):?>
    <a class="text-white pointer" onclick="viewAttachment('<?= $customerId?>','pan_card')">View</a>
<?php endif;?>
</span>
</div>