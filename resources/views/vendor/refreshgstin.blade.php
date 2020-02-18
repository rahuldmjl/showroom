
<span>GSTIN: <?=$gstinnumber?></span>
<span class="text-right">
<?php if(empty($gstinnumber)):?>
    <a class="text-white pointer" onclick="addGstinPan('<?= $vendorId?>','gstin')">Add</a>
<?php else:?>
    <a class="text-white pointer" onclick="editGstinPan('<?= $vendorId?>','gstin')">Edit</a>
<?php endif;?>
</span>
