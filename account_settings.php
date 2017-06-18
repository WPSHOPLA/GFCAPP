<?php
require('lib/init.php');
requireLogin();

$errorMsgPassword = '';
$errorMsgPassword2 = '';
$errorMsgEmail = '';

$notifyMsgUpdate = '';

if (!empty($_POST['update_info_submit'])) {
	$email = $_POST['email'];
	
	$email_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,10})$~i', $email);
	
	if(!$email_check)
		$errorMsgEmail = 'Invalid Email.';
	
	if($email_check) {		
		$changeEmail = $userClass->cpassword_recover.phphangeEmail($session_uid, $email);
		if ($changeEmail) {
			//$userDetails = $userClass->userDetails($session_uid);
			loadUserDetails();
			$notifyMsgUpdate = $mainClass->alert('success', 'Email changed');
		}
	}
} else if (!empty($_POST['change_password_submit'])) {
	$password = $_POST['password'];
	$password_2 = $_POST['password_2'];
	
	$password_check = preg_match('~^[A-Za-z0-9!@#$%^&*()_]{6,20}$~i', $password);
	
	if(!$password_check)
		$errorMsgPassword = 'Password must be between 6 and 20 characters.';
	
	if($password != $password_2)
		$errorMsgPassword2 = 'Confirmation password does not match.';
	
	if($password_check && $password == $password_2) {		
		$changeEmail = $userClass->changePassword($session_uid, $password);
		if ($changeEmail) {
			//$userDetails = $userClass->userDetails($session_uid);
			loadUserDetails();
			$notifyMsgUpdate = $mainClass->alert('success', 'Password Changed');
		}
	}
}

include('templates/default/header.php');
?>
<div class="container-fluid content">
	<div class="main-container">
		<div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
			<div class="login-form">
				<?php echo $notifyMsgUpdate; ?>
				
				<div class="h1 text-blue">Account Settings</div>

				<form name="form" method="post">
					<div class="h4 text-blue">Info</div>

					<label>Email</label>
					<div class="form-group<?php if($errorMsgEmail != '') echo ' has-error'; ?>">
						<?php if($errorMsgEmail != '') echo '<span class="help-block with-errors"><ul class="list-unstyled"><li>' . $errorMsgEmail . '</li></ul></span>'; ?>
						<input type="email" name="email" value="<?php echo htmlspecialchars($userDetails->email, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="Email" required>
					</div>
					
					<div class="form-actions form-group ">
						<input type="submit" name="update_info_submit" class="pri_button" value="Save">
					</div>
				</form>
				
				<form name="form" method="post">
					<div class="h4 text-blue">Change Password</div>
					
					<label>Password</label>
					<div class="form-group<?php if($errorMsgPassword != '') echo ' has-error'; ?>">
						<?php if($errorMsgPassword != '') echo '<span class="help-block with-errors"><ul class="list-unstyled"><li>' . $errorMsgPassword . '</li></ul></span>'; ?>
						<input type="password" name="password" class="form-control" placeholder="Password" required>
					</div>
					
					<label>Confirm Password</label>
					<div class="form-group<?php if($errorMsgPassword2 != '') echo ' has-error'; ?>">
						<?php if($errorMsgPassword2 != '') echo '<span class="help-block with-errors"><ul class="list-unstyled"><li>' . $errorMsgPassword2 . '</li></ul></span>'; ?>
						<input type="password" name="password_2" class="form-control" placeholder="Confirm Password" required>
					</div>
					
					<div class="form-actions form-group ">
						<input type="submit" name="change_password_submit" class="pri_button" value="Change">
					</div>
					
				</form>
			</div>
		</div>
	</div>
</div>
<?php include('templates/default/footer.php'); ?>