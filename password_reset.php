<?php
require('lib/init.php');

if(isLoggedIn()) {
	$url = BASE_URL . 'index.php';
	header("Location: $url"); // Page redirecting to home.php 
	exit();
}

$notifyMsgReset = '';

$errorMsgPassword = '';
$errorMsgPassword2 = '';

$reset_code = isset($_GET['reset_code']) ? $_GET['reset_code'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';
$userIdFromResetLink = $userClass->getUserIdFromResetLink($reset_code, $email);

if (!empty($_POST['reset_submit'])) {
	$password = $_POST['password'];
	$password_2 = $_POST['password_2'];
	
	$password_check = preg_match('~^[A-Za-z0-9!@#$%^&*()_]{6,20}$~i', $password);
	
	if(!$password_check)
		$errorMsgPassword = 'Password must be between 6 and 20 characters.';
	
	if($password != $password_2)
		$errorMsgPassword2 = 'Confirmation password does not match.';
	
	if($password_check && $password == $password_2) {
		if($userIdFromResetLink === 'INVALID_RESET_LINK') {
			//$notifyMsgReset = $mainClass->alert('error', 'The password reset link is invalid or expired.');
		} else if($userIdFromResetLink) {
			$userClass->resetPassword($userIdFromResetLink, $password, $reset_code, $email);
			$notifyMsgReset = $mainClass->alert('success', 'Password has been reset.');
			
		}	
	}
}

include('templates/default/header.php');
?>
<div class="container-fluid content">
	<div class="main-container">
		<div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
			<div class="login-form">
				<?php echo $notifyMsgReset; ?>
				
				<?php
				if($userIdFromResetLink === 'INVALID_RESET_LINK') {
					echo '<div class="search-examples"><center><p><b>The password reset link is invalid or expired.</b></p></center></div>';
				} else {
				?>
				
				<div class="h1 text-blue">Password Reset</div>

				<form name="form" method="post">
					<div class="form-group<?php if($errorMsgPassword != '') echo ' has-error'; ?>">
						<?php if($errorMsgPassword != '') echo '<span class="help-block with-errors"><ul class="list-unstyled"><li>' . $errorMsgPassword . '</li></ul></span>'; ?>
						<input type="password" name="password" class="form-control" placeholder="Password" required>
					</div>
					
					<div class="form-group<?php if($errorMsgPassword2 != '') echo ' has-error'; ?>">
						<?php if($errorMsgPassword2 != '') echo '<span class="help-block with-errors"><ul class="list-unstyled"><li>' . $errorMsgPassword2 . '</li></ul></span>'; ?>
						<input type="password" name="password_2" class="form-control" placeholder="Confirm Password" required>
					</div>

					<div class="form-actions form-group ">
						<input type="submit" name="reset_submit" class="full-width" value="Reset">
					</div>
				</form>

				<?php } ?>
				
			</div>
		</div>
	</div>
</div>
<?php include('templates/default/footer.php'); ?>