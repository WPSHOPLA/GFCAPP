<?php
require('lib/init.php');
requireLogin();

$countByStateArray = array();

$notifyMsgUpload = '';

if (!empty($_POST['csv_upload'])) {
	set_time_limit(100000);
	session_start();
	$sup_id = $_POST['sup_id'];
	session_write_close();
	$csv_files = $mainClass->getFilesArray($_FILES['file']);

	if(empty($csv_files[0]['name'])) {
		$notifyMsgUpload = $mainClass->alert('error', 'Please select a file.');
	} else {
		$allowed_mime_types = array('application/vnd.ms-excel', 'application/vnd.msexcel', 'text/csv', 'text/comma-separated-values', 'application/csv', 'application/excel');
		$upload_report = ['names_success' => [], 'names_fail' => [], 'questions_count' => 0, 'questions_skipped_count' => 0];
		session_start();
		$_SESSION['upload_status'][$sup_id] = [
			'fileNameCurrent' => '',
			'fileCountTotal' => sizeof($csv_files),
			'fileCountCurrent' => 0,
			'questionCountTotal' => 0,
			'questionCountCurrent' => 0
		];
		session_write_close();
		foreach ($csv_files as $csv_file) {
			session_start();
			$_SESSION['upload_status'][$sup_id]['questionCountTotal'] = 0;
			$_SESSION['upload_status'][$sup_id]['questionCountCurrent'] = 0;
			session_write_close();
			if (in_array($csv_file['type'], $allowed_mime_types)) {
				$csv_file['new_name'] = 'csv_' . date('Y-m-d-H-i-s') . '_' . uniqid() . '.csv';
				if(move_uploaded_file($csv_file["tmp_name"], "csvs/{$csv_file['new_name']}")) {
					session_start();
					$_SESSION['upload_status'][$sup_id]['fileCountCurrent'] = $_SESSION['upload_status'][$sup_id]['fileCountCurrent'] + 1;
					$_SESSION['upload_status'][$sup_id]['fileNameCurrent'] = $csv_file['name'];
					session_write_close();
					
					array_push($upload_report['names_success'], $csv_file['name']);
					$state = strtoupper($_POST['state']);
					setcookie('state_id', $state, time() + (86400 * 30 * 12), "/");
					$_COOKIE['state_id'] = $state;
					$status = 2;
			
					$csv_lines = array_map('str_getcsv', file("csvs/{$csv_file['new_name']}"));
					session_start();
					$_SESSION['upload_status'][$sup_id]['questionCountTotal'] = sizeof($csv_lines);
					$_SESSION['upload_status'][$sup_id]['questionCountCurrent'] = 0;
					session_write_close();
					foreach($csv_lines as $csv_line) {
						$qid = $questionClass->addQuestion($userDetails->id, $state, $status, $csv_line[0], $csv_line[1]);
						session_start();
						$_SESSION['upload_status'][$sup_id]['questionCountCurrent'] = $_SESSION['upload_status'][$sup_id]['questionCountCurrent'] + 1;
						session_write_close();
						if($qid == -1)
							$upload_report['questions_skipped_count']++;
						else
							$upload_report['questions_count']++;
					}
				} else {
					array_push($upload_report['names_fail'], $csv_file['name']);
				}
			} else {
				array_push($upload_report['names_fail'], $csv_file['name']);
			}
		}
		
		$notifyMsgUpload = sizeof($upload_report['names_fail']) > 0 ? $mainClass->alert('error', 'Failed uploads: ' . implode(', ', $upload_report['names_fail'])) : '';
		$notifyMsgUpload .= $mainClass->alert('success',
								'Uploaded files: ' . implode(', ', $upload_report['names_success']) . '<br />' .
								'Questions: ' . $upload_report['questions_count'] . '<br />' .
								'Questions skipped: ' . $upload_report['questions_skipped_count'] . '<br />'
								//'<a href="' . BASE_URL . 'questionslinked.php">See Questions</a>'
							);
	}
}

foreach ($questionClass->countQuestionsByState() as $counter) {
	$countByStateArray[$counter['state_id']] = $counter['count'];
}

include('templates/default/header.php');
?>
<div class="container-fluid content">
	<div class="main-container">
		<div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
			<div class="login-form">
				<?php echo $notifyMsgUpload; ?>
				<form method="post" enctype="multipart/form-data" id="upload-form">
				<input type="hidden" name="sup_id" value="<?php echo date('Y-m-d-H-i-s') . '_' . uniqid(); ?>">
				<div class="h1 text-blue">Upload Questions CSV</div>			
					<div class="form-group">
						<select name="state" class="form-control">
							<?php
							foreach ($questionClass->getStates() as $state) {
								$selected = isset($_COOKIE['state_id']) && $_COOKIE['state_id'] == $state['id'] ? ' selected' : ''; 
								$question_count = isset($countByStateArray[$state['id']]) ? $countByStateArray[$state['id']] : 0;
								//echo '<option value="' . $state['id'] . '"' . $selected . '>' . $state['name'] . ' (' . $question_count . ')</option>';
								echo '<option value="' . $state['id'] . '"' . $selected . '>' . $state['name'] . '</option>';
							}
							?>
						</select>
					</div>
						
					<div class="form-group input-group">
						<label class="input-group-btn">
							<span class="btn btn-primary">
								Browse&hellip; <input type="file" name="file[]" style="display: none;" multiple>
							</span>
						</label>
						<input type="text" class="form-control" readonly>
					</div>

					<div class="form-actions form-group ">
						<button type="submit" name="csv_upload" class="full-width" value="submit">Upload</button>
					</div>
					
					<div class="progress-bars" style="display: none;">
						<span id="progress-text-file">Uploading files</span>
						<div class="progress">
							<div id="progress-bar-file" class="progress-bar progress-bar-success progress-bar-striped active" style="background: #29b866; width:0%">

							</div>
						</div>
						
						<span id="progress-text-question">Creating questions</span>
						<div class="progress">
							<div id="progress-bar-question" class="progress-bar progress-bar-success progress-bar-striped active" style="background: #29b866; width:0%">
							
							</div>
						</div>
					</div>
					
					<script>
					$("#upload-form").submit(function(event) {
						checkUploadStatus();
					});
					
					function checkUploadStatus() {
						var request = new XMLHttpRequest();

						setInterval(function() {
							request.abort();
							request.open('GET', 'api/uploadstatus.php?sup_id=' + $('[name=sup_id]').val(), true);
							request.send();
						}, 500);

						request.onreadystatechange = function(response) {
							if (request.readyState === 4) {
								if (request.status === 200) {
									var jsonOptions = JSON.parse(request.responseText);
									if(jsonOptions.fileCountCurrent > 0) {
										$(".progress-bars").show();
										if(jsonOptions.fileCountTotal > 0) {
											$("#progress-bar-file").width((((jsonOptions.fileCountCurrent) * 100) / jsonOptions.fileCountTotal) + '%');
											$("#progress-bar-file").text(jsonOptions.fileCountCurrent + ' / ' + jsonOptions.fileCountTotal);
										} else {
											$("#progress-bar-file").width(0);
											$("#progress-bar-file").text('');
										}
										
										if(jsonOptions.questionCountTotal > 0) {
											$("#progress-bar-question").width((((jsonOptions.questionCountCurrent) * 100) / jsonOptions.questionCountTotal) + '%');
											$("#progress-bar-question").text(jsonOptions.questionCountCurrent + ' / ' + jsonOptions.questionCountTotal);
										} else {
											$("#progress-bar-question").width(0);
											$("#progress-bar-question").text('');
										}
									}
								}
							}
						};
					};
					</script>
					
				</form>
			</div>
		</div>
	</div>
</div>
<?php include('templates/default/footer.php'); ?>