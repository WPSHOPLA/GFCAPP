<?php
require('lib/init.php');
requireLogin();

$document_id = $_GET['id'];

$countByStateArray = array();

$document = $documentClass->findDocument($document_id, $userDetails->access >= 2 ? 0 : $userDetails->id);

include('templates/default/header.php');
?>	
<div class="container-fluid content">
    <div class="main-container">
		<div class="row">
			<div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">				
				<div class="search-examples">
					<?php if(!$document) { ?>
					<center><p><b>Document not found</b></p></center>
					<?php } else { ?>
					<div class="heading"><?php echo $document->file_name_original; ?></div>
					<?php /*<p><i>Uploaded on <?php echo $pdf->date_creation; ?> by <?php echo  $userClass->userDetails($pdf->user_id)->username; ?></i></p>*/ ?>
					<?php echo '<button type="submit" onclick="window.open(\'' . BASE_URL . 'documents/' . $document->file_name . '\');">View Document</button>'; ?>
					
					<br /><br />
					
					<?php echo '<p>' . nl2br($document->document_html) . '</p>'; ?>
					<?php } ?>
				</div>	
				
			</div>
		</div>
	</div>
</div>
<?php include('templates/default/footer.php'); ?>