<?php
require('lib/init.php');
requireLogin();

$countByStateArray = array();

$filter_state = isset($_COOKIE['state_id']) ? $_COOKIE['state_id'] : 0;

if (!empty($_GET['question_search'])) {
	$filter_state = $_GET['state'];
	
	setcookie('state_id', $filter_state, time() + (86400 * 30 * 12), "/");
	$_COOKIE['state_id'] = $filter_state;
}

$conditionArray = [['link_id', '>', 0, PDO::PARAM_INT]];
//if($filter_state > 0)
	//array_push($conditionArray, ['state_id', '=', $filter_state, PDO::PARAM_STR]);
foreach ($questionClass->countQuestionsByState($conditionArray) as $counter) {
	$countByStateArray[$counter['state_id']] = $counter['count'];
}

include('templates/default/header.php');
?>
<div class="container-fluid content">
    <div class="main-container">
		<div class="row">
			<div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
				<div class="search-form">
					<form name="search" method="get" class="nested-search">
						<div class="form-group">
						<select name="state" class="form-control">
							<option value="0">Any State</option>
							<?php
							foreach ($questionClass->getStates() as $state) {
								$selected = isset($_COOKIE['state_id']) && $_COOKIE['state_id'] == $state['id'] ? ' selected' : ''; 
								$question_count = isset($countByStateArray[$state['id']]) ? $countByStateArray[$state['id']] : 0;
								echo '<option value="' . $state['id'] . '"' . $selected . '>' . $state['name'] . ' (' . $question_count . ')</option>';
							}
							?>
						</select>
					</div>
						
						<div class="form-actions form-group ">
							<button type="submit" name="question_search" class="full-width" value="submit">Search</button>
						</div>
					</form>					
				</div>
				
				<div class="search-examples">
					<?php
					$conditionArray = [];
					
					array_push($conditionArray, ['link_id', '>', 0, PDO::PARAM_INT]);
					if($filter_state > 0)
						array_push($conditionArray, ['state_id', '=', $filter_state, PDO::PARAM_INT]);
							
					$pagination = $mainClass->getPagination('questions', 10, $conditionArray);
					foreach ($questionClass->findQuestionsLinked('', $filter_state, $userDetails->access >= 2 ? 0 : $userDetails->id, $pagination) as $question) {
						echo $question . '<br /><br />';
					}
					echo $pagination['html'];
					?>	
				</div>	
				
			</div>
		</div>
	</div>
</div>
<?php include('templates/default/footer.php'); ?>