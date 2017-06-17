<?php
require('lib/init.php');
requireStaff();

$username = isset($_GET['username']) ? $_GET['username'] : '';
$logUserDetails = null;

if (!empty($_GET['log_user'])) {
	if($username != '') {
		$logUserDetails = $userClass->userDetailsByUsername($username);
	}
}

if($username != '' && !$logUserDetails)
	$errorMsgUser = $mainClass->alert('error', 'User not found');
else
	$errorMsgUser = '';

include('templates/default/header.php');
?>
<div class="container-fluid content">
    <div class="main-container">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="search-form" style="max-width: 500px;">
					<?php echo $errorMsgUser; ?>
					<form name="search" method="get" class="nested-search">
						<div class="form-group">
							<input type="text" name="username" class="form-control" placeholder="Username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
						</div>
						
						<div class="form-actions form-group ">
							<button type="submit" name="log_user" class="full-width" value="submit">Show Log</button>
						</div>
					</form>					
				</div>
				
				<div class="search-examples">
					<?php
					if($username != '') {
						if($logUserDetails) {
							$conditionArray = [];
							
							array_push($conditionArray, ['user_id', '=', $logUserDetails->id, PDO::PARAM_INT]);
							
							$pagination = $mainClass->getPagination('user_log', 25, $conditionArray);
							
							echo '<table class="table"><thead><tr>
								<th>Time</th>
								<th>Action</th>
								<th>Info</th>
								</tr></thead><tbody>';
								
							foreach ($logClass->findLogs($pagination) as $log) {
								if($log->action_type == 2 && $log->param1 == '')
									continue;
								
								$action_time = date("m-d-y, g:i a", strtotime($log->time));
								
								if($log->action_type == 1)
									$action_name = 'Login';
								else if($log->action_type == 2)
									$action_name = 'Ask';
								else if($log->action_type == 3)
									$action_name = 'Check section';
								else
									$action_name = 'undefined action';
								
								
								echo '<tr>';
								//echo '<div class="result-block">';
								
								
								//echo '<div class="highlight" >' . $action_time . '</div>';
								//echo '<div class="highlight" >IP: ' . $log->ip . '</div>';
								//echo '<div class="highlight" >Browser: ' . $log->browser . '</div>';
								
								echo '<td width="25%">' . $action_time . '</td>';
								echo '<td width="25%">' . $action_name . '</td>';
								
								if($log->action_type == 1) {
									echo '<td>Session duration: ' . $log->duration . ' minutes</td>';
									//echo 'Session duration: ' . $log->duration . ' minutes<br />';
								} else if($log->action_type == 2) {
									echo '<td>' . $log->param1 . '</td>';
									//echo 'Question: ' . $log->param1 . '<br />';
								} else if($log->action_type == 3) {
									echo '<td>' . $log->param3 . '</td>';
									//echo 'Question: ' . $log->param1 . '<br />';
									//echo 'Section: ' . $log->param3 . '<br />';
								}
								
								
								
								//echo '<button class="btn btn-default btn-xs" data-toggle="collapse" data-target="#log_id_' . $log->id . '">Info</button>';
								//echo '<div id="log_id_' . $log->id . '" class="collapse">';
								//echo 'IP: ' . $log->ip . '<br />';
								//echo 'Browser: ' . $log->browser . '<br />';
								//echo '</div>';
								
								
								echo '</tr>';
								//echo '<div class="heading">' . $log->action_type . '</div>';
								//echo '<br /><br /><br />';
							}
							echo '</tbody></table>';
								
							echo $pagination['html'];
						}
					}
					?>	
				</div>	
				
			</div>
		</div>
	</div>
</div>
<?php include('templates/default/footer.php'); ?>