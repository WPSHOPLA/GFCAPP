<?php
require('lib/init.php');
requireLogin();

$countByStateArray = array();

$filter_state = 0;

if (!empty($_GET['pdf_search'])) {
	$filter_state = $_GET['state'];
	setcookie('state_id', $filter_state, time() + (86400 * 30 * 12), "/");
	$_COOKIE['state_id'] = $filter_state;
}

foreach ($documentClass->countDocumentsByState() as $counter) {
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
							foreach ($documentClass->getStates() as $state) {
								$selected = isset($_COOKIE['state_id']) && $_COOKIE['state_id'] == $state['id'] ? ' selected' : ''; 
								$question_count = isset($countByStateArray[$state['id']]) ? $countByStateArray[$state['id']] : 0;
								echo '<option value="' . $state['id'] . '"' . $selected . '>' . $state['name'] . ' (' . $question_count . ')</option>';
							}
							?>
						</select>
					</div>
						
						<div class="form-actions form-group ">
							<button type="submit" name="pdf_search" class="full-width" value="submit">Search</button>
						</div>
					</form>					
				</div>
				
				<div class="search-examples">
					<?php
					function ttruncat($text,$numb) {
						if (strlen($text) > $numb) { 
							$text = substr($text, 0, $numb); 
							$text = substr($text,0,strrpos($text," ")); 
							$etc = " ...";  
							$text = $text.$etc; 
						}
						return $text; 
					}
					$conditionArray = [];
					if($filter_state > 0)
						array_push($conditionArray, ['state_id', '=', $filter_state, PDO::PARAM_INT]);
					$pagination = $mainClass->getPagination('documents', 10, $conditionArray);
					foreach ($documentClass->findDocuments($filter_state, $userDetails->access >= 2 ? 0 : $userDetails->id, $pagination) as $document) {
						echo '<div class="heading">' . $document['file_name_original'] . '</div>';
						//echo '<p><i>Uploaded by ' . $userClass->userDetails($pdf['user_id'])->username . '</i></p>';
						echo '<p>' . nl2br(ttruncat($document['document_text'], 250)) . '</p>';
						echo '<button type="submit" onclick="location.href=\'' . BASE_URL . 'viewdocument.php?id=' . $document['id'] . '\';">View</button>';
						echo '<br /><br /><br />';
					}
					echo $pagination['html'];
					?>	
				</div>	
				
			</div>
		</div>
	</div>
</div>
<?php include('templates/default/footer.php'); ?>