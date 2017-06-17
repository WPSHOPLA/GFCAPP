<?php
$freePage = true;
include('lib/config.php');
include('lib/session.php');
$userDetails = $userClass->userDetails($session_uid);

include('lib/classes/mainClass.php');
include('lib/classes/questionClass.php');
$questionClass = new questionClass();
$mainClass = new mainClass();

include('templates/default/header.php');
?>
<div class="container-fluid content">
    <div class="main-container">
		<div class="row">
			<div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
				<div class="search-form">
			
					
				</div>						
			</div>
		</div>
	</div>
</div>
<?php include('templates/default/footer.php'); ?>