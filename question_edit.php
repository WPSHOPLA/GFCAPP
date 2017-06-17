<?php
require('lib/init.php');
requireLogin();

$question_id = $_GET['id'];
$question = $questionClass->findQuestion($question_id, '');

$countByStateArray = array();

$notifyMsgEdit = '';
$errorMsgQuestion = '';
$errorMsgParagraphNum = '';
$errorMsgState = '';
$errorMsgStatus = '';

$questionDeleted = false;

if (!empty($_POST['question_delete'])) {
	$question = null;
	$question_delete = $questionClass->deleteQuestion($question_id, $userDetails->access >= 2 ? 0 : $userDetails->id);
	if($question_delete) {
		//$url = BASE_URL . 'questions.php';
		//header("Location: $url");
		$questionDeleted = true;
	}
} else if (!empty($_POST['question_edit'])) {
	$question_new = $_POST['question'];
	$paragraph_num = $_POST['paragraph_num'];
	$state_new = strtoupper($_POST['state']);
	$status_new = strtoupper($_POST['status']);
	
	/* Regular expression check */
	$question_check = strlen($question_new) >= 20;
	$paragraph_num_check = strlen($paragraph_num) >= 3;
	$state_check = isset($mainClass->getStates()[$state_new]);
	$status_check = isset($questionClass->getStatus()[$status_new]);

	if(!$question_check)
		$errorMsgQuestion = 'Question must be at least 20 characters.';
	
	if(!$paragraph_num_check)
		$errorMsgParagraphNum = 'Section must be at least 3 characters.';
	
	if(!$state_check)
		$errorMsgState = 'Invalid State.';
	
	if(!$status_check)
		$errorMsgStatus = 'Invalid Status.';
	
	if($question_check && $paragraph_num_check && $state_check && $state_check) {
		$edit_question = $questionClass->editQuestion($question_id, $question_new, $paragraph_num, $userDetails->access >= 2 ? 0 : $userDetails->id, $state_new, $status_new);
		if($edit_question) {
			$question = $questionClass->findQuestion($question_id, '');
			$notifyMsgEdit = $mainClass->alert('success', 'Question edited.');
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
				<?php if(!$question) { ?>
				<div class="search-examples">
					<?php if($questionDeleted) { ?>
					<center><p><b><?php echo $mainClass->alert('warning', 'Question successfully deleted.'); ?></b></p></center>
					<?php } else { ?>
					<center><p><b><?php echo $mainClass->alert('error', 'Question not found.'); ?></b></p></center>
					<?php } ?>
				</div>
				<?php } else { ?>
				<?php echo $notifyMsgEdit; ?>
				<form method="post">
				<div class="h1 text-blue">Edit Question</div>		
					<div class="form-group<?php if($errorMsgQuestion != '') echo ' has-error'; ?>">
						<?php if($errorMsgQuestion != '') echo '<span class="help-block with-errors"><ul class="list-unstyled"><li>' . $errorMsgQuestion . '</li></ul></span>'; ?>
						<input type="text" name="question" class="form-control" value="<?php echo htmlspecialchars($question->question, ENT_QUOTES, 'UTF-8'); ?>">
					</div>

					<div class="form-group<?php if($errorMsgParagraphNum != '') echo ' has-error'; ?>">
						<?php if($errorMsgParagraphNum != '') echo '<span class="help-block with-errors"><ul class="list-unstyled"><li>' . $errorMsgParagraphNum . '</li></ul></span>'; ?>
						<input type="text" name="paragraph_num" id="ajax" class="form-control awesomplete" value="<?php echo htmlspecialchars($question->paragraph_num, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Section" required>
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
						<select name="state" class="form-control">
							<?php
							foreach ($mainClass->getStates() as $state) {
								$selected = $question->state_id == $state['id'] ? ' selected' : ''; 
								$question_count = isset($countByStateArray[$state['id']]) ? $countByStateArray[$state['id']] : 0;
								//echo '<option value="' . $state['id'] . '"' . $selected . '>' . $state['name'] . ' (' . $question_count . ')</option>';
								echo '<option value="' . $state['id'] . '"' . $selected . '>' . $state['name'] . '</option>';
							}
							?>
						</select>
					</div>			
					
					<div class="form-group<?php if($errorMsgStatus != '') echo ' has-error'; ?>">
						<?php if($errorMsgStatus != '') echo '<span class="help-block with-errors"><ul class="list-unstyled"><li>' . $errorMsgStatus . '</li></ul></span>'; ?>
						<select name="status" class="form-control">
							<?php
							foreach ($questionClass->getStatus() as $status_id => $status) {
								$selected = $question->status == $status_id ? ' selected' : ''; 
								echo '<option value="' . $status_id . '"' . $selected . '>' . $status . '</option>';
							}
							?>
						</select>
					</div>
										
					<div class="form-actions form-group ">
						<button type="submit" name="question_edit" class="full-width" value="submit">Edit</button>
					</div>
					
					<div class="form-actions form-group ">
						<button type="submit" name="question_delete" class="full-width btn-red" value="submit">Delete</button>
					</div>
				</form>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<?php include('templates/default/footer.php'); ?>