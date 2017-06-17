<?php
require('lib/init.php');
requireNotSubAccount();

$invites_info = $userClass->getInvitesInfo($session_uid);

$errorMsgPassword = '';
$errorMsgPassword2 = '';
$errorMsgEmail = '';

$notifyMsgUpdate = '';

if (!empty($_POST['invite_email_submit'])) {
	$email = $_POST['email'];
	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	
	$email_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,10})$~i', $email);
	$invites_check = $invites_info->invites_count > 0;
	
	if(!$invites_check)
		$notifyMsgUpdate = $mainClass->alert('error', 'You cannot invite users as it has reached your team size limit. Do you want to upgrade your account?');
	
	if(!$email_check)
		$errorMsgEmail = 'Invalid Email.';
	
	if($email_check && $invites_check) {		
		$inviteUser = $userClass->inviteUser($session_uid, $email, $first_name, $last_name);
		if ($inviteUser) {
			if($inviteUser === 'EMAIL_ALREADY_EXISTS') {
				$notifyMsgUpdate = $mainClass->alert('error', 'Cannot send an invite. Already a registered user on GoFetchCode website.');
			} else {
				$userDetails = $userClass->userDetails($session_uid);
				$notifyMsgUpdate = $mainClass->alert('success', 'Sent invite to ' . $email);
				$invites_info = $userClass->getInvitesInfo($session_uid);
			}
		}
	}
} else if (!empty($_POST['invite_revoke_submit'])) {
	$invite_code = $_POST['invite_code'];
	$inviteRevoke = $userClass->inviteRevoke($session_uid, $invite_code);
	if ($inviteRevoke) {
		$notifyMsgUpdate = $mainClass->alert('success', 'Invite revoked');
		$invites_info = $userClass->getInvitesInfo($session_uid);
	}
} else if (!empty($_POST['invited_user_delete'])) {
	$user_id = $_POST['user_id'];
	$invitedUserDelete = $userClass->invitedUserDelete($session_uid, $user_id);
	if ($invitedUserDelete) {
		$notifyMsgUpdate = $mainClass->alert('success', 'Account deleted');
		$invites_info = $userClass->getInvitesInfo($session_uid);
	}
}

include('templates/default/header.php');
?>
<div class="container-fluid content">
	<div class="main-container">
		<div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
			<div class="login-form">
				<?php echo $notifyMsgUpdate; ?>
				
				<div class="h1 text-blue">Team</div>
				
				<form name="form" method="post">
					<div class="h4 text-blue">Members</div>

					<?php
					echo '<table class="table"><thead><tr><th>Username</th><th>Email</th><th>Action</th></tr></thead><tbody>';
					//$invites_info->users = [];
		
					foreach($invites_info->users as $user) {
						echo '<tr>';
						echo '<td>' . $user->username . '</td>';
						echo '<td>' . $user->email . '</td>';
						echo '<td><a href=""><form method="post"><input type="hidden" name="user_id" value="' . $user->id . '"><input type="submit" name="invited_user_delete" class="btn btn-default btn-xs" value="Delete"></a></td>';
						//echo '<td><a href="">Delete</a></td>';
						echo '</tr>';
					}
					if(sizeof($invites_info->users) == 0)
						echo '<tr><td align="center" colspan="3">You have no team members</td></tr>';
					echo '</tbody></table>';
					
					//echo '<h5>Invitations: <span class="username">' . $invites_info->invites_count . '</span></h5>';
					?>
				</form>
				
				<form name="form" method="post">
					<?php $invite_text = $invites_info->invites_count == 1 ? 'invite' : 'invites'; ?>
					<div class="h4 text-blue">Invite People (<?php echo '<span class="username">' . $invites_info->invites_count . '</span> ' . $invite_text . ' left'; ?>)</div>
						<div class="form-group<?php if($errorMsgEmail != '') echo ' has-error'; ?>">
							<?php if($errorMsgEmail != '') echo '<span class="help-block with-errors"><ul class="list-unstyled"><li>' . $errorMsgEmail . '</li></ul></span>'; ?>
							<input type="email" name="email" class="form-control" placeholder="Email" required>
						</div>
						
						<div class="row">
							<div class="col-xs-12 col-sm-6">
								<input type="text" name="first_name" class="form-control" placeholder="First Name" required>
							</div>
							<div class="col-xs-12 col-sm-6">
								<input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
							</div>
						</div>
						
						<br />
						
						<div class="form-actions form-group ">
							<input type="submit" name="invite_email_submit" class="full-width" value="Invite">
						</div>
				</form>
				
				<form name="form" method="post">
					<div class="h4 text-blue">Pending Invites</div>
					<?php
					echo '<table class="table"><thead><tr><th>Email</th><th>Invite Code</th><th>Action</th></tr></thead><tbody>';
					//$invites_info->users = [];
					foreach($invites_info->pending_invites as $invite) {
						echo '<tr>';
						echo '<td>' . $invite->email . '</td>';
						echo '<td>' . $invite->invite_code . '</td>';
						echo '<td><a href=""><form method="post"><input type="hidden" name="invite_code" value="' . $invite->invite_code . '"><input type="submit" name="invite_revoke_submit" class="btn btn-default btn-xs" value="Revoke"></a></td>';
						echo '</tr>';
					}
					if(sizeof($invites_info->pending_invites) == 0)
						echo '<tr><td align="center" colspan="3">You have no pending invites</td></tr>';
					echo '</tbody></table>';
					
					//echo '<h5>Invitations: <span class="username">' . $invites_info->invites_count . '</span></h5>';
					?>
				</form>
				
				
			</div>
		</div>
	</div>
</div>
<?php include('templates/default/footer.php'); ?>