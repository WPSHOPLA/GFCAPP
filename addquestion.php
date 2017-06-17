<?php
require('lib/init.php');
requireLogin();

$countByStateArray = array();

$notifyMsgAsk = '';
$errorMsgQuestion = '';
$errorMsgParagraphNum = '';
$errorMsgState = '';
$errorMsgStatus = '';


if (!empty($_POST['question_submit'])) {
	$question = $_POST['question'];
	$paragraph_num = $_POST['paragraph_num'];
	$state = strtoupper($_POST['state']);
	$status = strtoupper($_POST['status']);
	
	setcookie('state_id', $state, time() + (86400 * 30 * 12), "/");
	$_COOKIE['state_id'] = $state;
	
	/* Regular expression check */
	$question_check = strlen($question) >= 20;
	$paragraph_num_check = strlen($paragraph_num) >= 3;
	$state_check = isset($questionClass->getStates()[$state - 1]);
	$status_check = isset($questionClass->getStatus()[$status]);

	
	if(!$question_check)
		$errorMsgQuestion = 'Question must be at least 20 characters.';
	
	if(!$paragraph_num_check)
		$errorMsgParagraphNum = 'Section must be at least 3 characters.';
	
	if(!$state_check)
		$errorMsgState = 'Invalid State.';
	
	if(!$status_check)
		$errorMsgStatus = 'Invalid Status.';
	
	if($question_check && $paragraph_num_check && $status_check && $state_check) {
		$qid = $questionClass->addQuestion($userDetails->id, $state, $status, $question, $paragraph_num);
		if($qid != -1) {
			$notifyMsgAsk = $mainClass->alert('success', 'Question added. QID: ' . $qid);
		} else {
			$notifyMsgAsk = $mainClass->alert('warning', 'Question is already added');
		}
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
				<?php echo $notifyMsgAsk; ?>
				<form method="post">
				<div class="h1 text-blue">Add a Question</div>			
					<div class="form-group<?php if($errorMsgState != '') echo ' has-error'; ?>">
						<?php if($errorMsgState != '') echo '<span class="help-block with-errors"><ul class="list-unstyled"><li>' . $errorMsgState . '</li></ul></span>'; ?>
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
						
					<div class="form-group<?php if($errorMsgQuestion != '') echo ' has-error'; ?>">
						<?php if($errorMsgQuestion != '') echo '<span class="help-block with-errors"><ul class="list-unstyled"><li>' . $errorMsgQuestion . '</li></ul></span>'; ?>
						<input type="text" name="question" class="form-control" placeholder="Enter your Question" required>
					</div>
					
					<div class="form-group<?php if($errorMsgParagraphNum != '') echo ' has-error'; ?>">
						<?php if($errorMsgParagraphNum != '') echo '<span class="help-block with-errors"><ul class="list-unstyled"><li>' . $errorMsgParagraphNum . '</li></ul></span>'; ?>
						<input type="text" name="paragraph_num" id="ajax" class="form-control awesomplete" placeholder="Section" required>
					</div>
					
					<script>
					$(function() {
						var input = document.getElementById('ajax');
						var request = new XMLHttpRequest();
						var awesomplete = new Awesomplete(input, {minChars: 1, highlight: false});
						$("#ajax").on('input', function() {
							if(input.value.length < 1)
								return;
							awesomplete.list = [];
							request.abort();
							request.open('GET', 'api/autocomplete.php?section=' + input.value + "&state_id=" + $("[name=state]").val(), true);
							request.send();
						});

						request.onreadystatechange = function(response) {
							if (request.readyState === 4) {
								if (request.status === 200) {
									var jsonOptions = JSON.parse(request.responseText);
									var list = [];
									jsonOptions.forEach(function(item) {
										list.push({label: item.paragraph_num + ' - ' + item.paragraph_title, value: item.paragraph_num});
									});
									awesomplete.list = list;
								}
							}
						};
					});
					</script>
					
					
					<div class="form-group<?php if($errorMsgState != '') echo ' has-error'; ?>">
						<?php if($errorMsgState != '') echo '<span class="help-block with-errors"><ul class="list-unstyled"><li>' . $errorMsgState . '</li></ul></span>'; ?>
						<select name="status" class="form-control" required>
							<?php
							foreach ($questionClass->getStatus() as $status_id => $status) {
								echo '<option value="' . $status_id . '">' . $status . '</option>';
							}
							?>
						</select>
					</div>
					
					
					
	
					
					
					<div class="form-actions form-group ">
						<button type="submit" name="question_submit" class="full-width" value="submit">Create Question</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php include('templates/default/footer.php'); ?>