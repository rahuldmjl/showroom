<div class="modal-header text-inverse">
    <button type="button" class="close p-0 m-0" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 class="modal-title" id="myLargeModalLabel">
    	<?php
    	if($attachmentType == 'gstin')
    	{
    		echo 'GSTIN Attachment';
    	}
    	else if($attachmentType == 'pan_card')
    	{
    		echo 'PAN Card Attachment';
    	}
    	?>
    </h5>
</div>
<div class="modal-body">
    <div class="row">
        <?php
        $explodedFileName = explode('.',$attachment);
        $fileExtention = end($explodedFileName);
        ?>
        <?php if($fileExtention != 'pdf'):?>
            <img src="<?= $attachment?>" class="img-responsive" alt="<?= ($attachmentType == 'gstin') ? 'GSTIN' : 'PAN Card'?>"/>
        <?php else:?>
            <iframe class="w-100 h-500" src="<?= $attachment?>"frameborder="0"></iframe>
        <?php endif;?>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-danger ripple text-left" data-dismiss="modal">Close</button>
</div>
<style>
    .h-500{height:500px;}
</style>