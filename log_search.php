<?php
require('lib/init.php');
requireStaff();

$username = isset($_GET['username']) ? $_GET['username'] : '';
$question = isset($_GET['question']) ? $_GET['question'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$logUserDetails = null;

if (!empty($_GET['log_search'])) {
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
			<div class="col-md-8 col-md-offset-2" id="logContainer">
				<div class="search-form" style="max-width: 500px;">
					<?php echo $errorMsgUser; ?>
					<form name="search" method="get" class="nested-search">
						<div class="form-group">
							<input type="text" name="username" class="form-control" placeholder="Username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
						</div>
  
						<div class="form-group">
							<input type="text" name="question" class="form-control awesomplete" id="ajax" placeholder="Question" value="<?php echo htmlspecialchars($question, ENT_QUOTES, 'UTF-8'); ?>">
						</div>
						
						<script>
							$(function() {
								var input = document.getElementById('ajax');
								var request = new XMLHttpRequest();
								var awesomplete = new Awesomplete(input, {minChars: 1});
								$("#ajax").on('input', function() {
									if(input.value.length < 1)
										return;
									
									awesomplete.list = [];
									request.abort();
									request.open('GET', 'api/autocomplete.php?question_log=' + input.value, true);
									request.send();
								});

								request.onreadystatechange = function(response) {
									if (request.readyState === 4) {
										if (request.status === 200) {
											var jsonOptions = JSON.parse(request.responseText);
											var list = [];
											jsonOptions.forEach(function(item) {
												list.push(item);
											});
											awesomplete.list = list;
										}
									}
								};
							});
						</script>
						
						<div class="form-group">
							<input type="text" id="reportrange" name="date" class="form-control" placeholder="Date" value="<?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>">
						</div>
 
						<script type="text/javascript">
						$(function() {

							var start = moment().subtract(29, 'days');
							var end = moment();

							function cb(start, end) {
								$('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
							}

							$('#reportrange').daterangepicker({
								//startDate: start,
								//endDate: end,
								parentEl: "#logContainer",
								ranges: {
								   'Today': [moment(), moment()],
								   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
								   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
								   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
								   'This Month': [moment().startOf('month'), moment().endOf('month')],
								   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
								}
							}, cb);

							cb(start, end);
							
						});
						</script>
						
						<div class="form-actions form-group ">
							<button type="submit" name="log_search" class="full-width" value="submit">Show Log</button>
						</div>
					</form>					
				</div>
				
				<div class="search-examples">
					<?php
					if(true || $username != '') {
						if(true || $logUserDetails) {
							$conditionArray = [];
							
							array_push($conditionArray, ['action_type', '=', 2, PDO::PARAM_INT]);
							
							if($username != '')
								if($logUserDetails)
									array_push($conditionArray, ['user_id', '=', $logUserDetails->id, PDO::PARAM_INT]);
								else
									array_push($conditionArray, ['user_id', '=', -1, PDO::PARAM_INT]);
							
							if($question != '')
								array_push($conditionArray, ['param1', '=', $question, PDO::PARAM_STR]);
							
							if($date != '') {
								$date_split = explode(' - ', $date);
								$date_start = $date_split[0];
								$date_end = $date_split[1];
								
								$date_start = date('Y-m-d H:i:s', strtotime($date_start));
								$date_end = date('Y-m-d H:i:s', strtotime($date_end . ' +1 day'));
								
								/*print_r($date_start);
								echo "<br />";
								print_r($date_end);*/
								
								
								array_push($conditionArray, ['time', '>=', $date_start, PDO::PARAM_STR]);
								array_push($conditionArray, ['time', '<', $date_end, PDO::PARAM_STR]);
								
								//print_r($conditionArray);
							}
							
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