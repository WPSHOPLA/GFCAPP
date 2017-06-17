<?php
require('lib/init.php');

if(isLoggedIn()) {
	$url = BASE_URL . 'index.php';
	header("Location: $url"); // Page redirecting to home.php 
	exit();
}

$notifyMsgReset = '';

$errorMsgEmail = '';

if (!empty($_POST['reset_request_submit'])) {
	$email = $_POST['email'];
	$requestPasswordReset = $userClass->requestPasswordReset($email);
		
	if($requestPasswordReset === 'INVALID_EMAIL') {
		$errorMsgEmail = 'Email not found';
	} else if($requestPasswordReset) {
		$notifyMsgReset = $mainClass->alert('success', 'A password reset link has been sent to your email.');
	}
}

include('templates/default/header.php');
?>
<div class="container-fluid content">
	<div class="main-container">
		<div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
			<div class="login-form">
				<?php echo $notifyMsgReset; ?>
				
				<div class="h1 text-blue">Password Recovery</div>

				<form name="form" method="post">
					<div class="form-group<?php if($errorMsgEmail != '') echo ' has-error'; ?>">
						<?php if($errorMsgEmail != '') echo '<span class="help-block with-errors"><ul class="list-unstyled"><li>' . $errorMsgEmail . '</li></ul></span>'; ?>
						<input type="text" name="email" class="form-control" placeholder="Email" required>
					</div>

					<div class="form-actions form-group ">
						<input type="submit" name="reset_request_submit" class="full-width" value="Recover">
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php include('templates/default/footer.php'); ?>