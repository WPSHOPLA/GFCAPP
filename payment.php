<?php
die();
require('lib/init.php');
requireNotSubAccount();
if(isSubscribed()) {
	$url = BASE_URL . 'account_team.php';
	header("Location: $url");
	exit();
}


$payStatusMsg = '';

$base_price = 23.40;
$total_price = 0;
$locations_count = sizeof($userClass->getSubscriptionLocations($session_uid));
$users_count = 1 + $userDetails->extra_users;					
$total_price = $base_price * $users_count * $locations_count;
$line_quantity = $locations_count * $users_count;

if (!empty($_POST['cardNumber']) && !empty($_POST['cardCVC']) && !empty($_POST['cardExpiry']) && !empty($_POST['cardName'])) {
	//cardNumber cardCVC cardExpiry
	$card_name = $_POST['cardName'];
	$card_number = $_POST['cardNumber'];
	$card_cvc = $_POST['cardCVC'];
	$card_expiry = $_POST['cardExpiry'];
	$card_month = explode('/', $card_expiry)[0];
	$card_year = explode('/', $card_expiry)[1];
	echo '<br /><br /><br /><br /><br />';

	
									
	$created_card = $paymentClass->createCard($userDetails->id, $userDetails->co_customer_id, $card_number, $card_year, $card_month, $card_name);
	if($created_card->status == 'success') {
		$created_subscription = $paymentClass->createSubscription($userDetails->id, $userDetails->co_customer_id, 7, $created_card->id, $line_quantity);
		if($created_subscription->status == 'success') {
			$userClass->extendSubscription($session_uid, 7);
			/*$send_invoice = $paymentClass->invoiceSubscription($created_subscription->id);
			if($send_invoice->status == 'success') {
				$pay_invoice = $paymentClass->payInvoice($userDetails->co_customer_id, $send_invoice->id, $total_price);
				if($pay_invoice->status == 'success') {
					$userClass->extendSubscription($session_uid, 30);
					$payStatusMsg = $mainClass->alert('success', 'Success');
				} else {
					$payStatusMsg = $mainClass->alert('error', $pay_invoice->message);
				}
			} else {
				$payStatusMsg = $mainClass->alert('error', $send_invoice->message);
			}*/
		} else {
			$payStatusMsg = $mainClass->alert('error', $created_subscription->message);
		}
	} else {
		$payStatusMsg = $mainClass->alert('error', $created_card->message);
	}
}

include('templates/default/header.php');
?>
<style>
/* CSS for Credit Card Payment form */
.credit-card-box .panel-title {
    display: inline;
    font-weight: bold;
}
.credit-card-box .form-control.error {
    border-color: red;
    outline: 0;
    box-shadow: inset 0 1px 1px rgba(0,0,0,0.075),0 0 8px rgba(255,0,0,0.6);
}
.credit-card-box label.error {
  font-weight: bold;
  color: red;
  padding: 2px 8px;
  margin-top: 2px;
}
.credit-card-box .payment-errors {
  font-weight: bold;
  color: red;
  padding: 2px 8px;
  margin-top: 2px;
}
.credit-card-box label {
    display: block;
}
/* The old "center div vertically" hack */
.credit-card-box .display-table {
    display: table;
}
.credit-card-box .display-tr {
    display: table-row;
}
.credit-card-box .display-td {
    display: table-cell;
    vertical-align: middle;
    width: 50%;
}
/* Just looks nicer */
.credit-card-box .panel-heading img {
    min-width: 180px;
}
</style>


<script src="https://assets.chargeover.com/chargeover/minify/?g=chargeover.js"></script>
<script>
  ChargeOver.Core.setup({
    'instance': 'gofetchcode.chargeover.com',
    'token': 'OXq2eAhT8V3bNr0QpGlWLUmI5JtnFc1z'
  });
</script>

<div class="container-fluid content">
    <div class="main-container">
		<div class="row">
			<div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
				<div class="search-form">
					
					<div class="container">
						<div class="row">
							<!-- You can make it whatever width you want. I'm making it full width
								on <= small devices and 4/12 page width on >= medium devices -->
							<div class="col-xs-12 col-md-4 col-md-offset-1">
								<!-- CREDIT CARD FORM STARTS HERE -->
								
								<?php
								echo $payStatusMsg;
								//Success
								/*if(!$userClass->isSubscribed($session_uid)) {
									echo $mainClass->alert('error', 'You have no active subscription');
								}*/
								?>
								
								<?php 
								loadUserDetails();
								if(!isSubscribed()) { ?>
								<center><div>
								<?php
									echo '<h1>$' . number_format($total_price, 2, '.', '') . ' / year</h1>';
									echo '<h6>$' . number_format($base_price, 2, '.', '') . '(base price / year) x ' . $users_count . '(amount of users) x ' . $locations_count . '(amount of locations)</h6>';
								?>
								</div></center>

								<div class="panel panel-default credit-card-box">
									<div class="panel-heading display-table" >
										<div class="row display-tr" >
											<h3 class="panel-title display-td" >Payment Details</h3>
											<div class="display-td" >                            
												<img class="img-responsive pull-right" src="http://i76.imgup.net/accepted_c22e0.png">
											</div>
										</div>
									</div>
									<div class="panel-body">
										<form role="form" id="payment-form" method="post">
											<div class="row">
												<div class="col-xs-12">
													<div class="form-group">
														<label for="cardNumber">CARD NUMBER</label>
														<div class="input-group">
															<input 
																type="tel"
																class="form-control"
																name="cardNumber"
																placeholder="Valid Card Number"
																autocomplete="cc-number"
																required autofocus 
																/>
															<span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-xs-7 col-md-7">
													<div class="form-group">
														<label for="cardExpiry"><span class="hidden-xs">EXPIRATION</span><span class="visible-xs-inline">EXP</span> DATE</label>
														<input 
															type="tel" 
															class="form-control" 
															name="cardExpiry"
															placeholder="MM / YYYY"
															autocomplete="cc-exp"
															required 
															/>
													</div>
												</div>
												<div class="col-xs-5 col-md-5 pull-right">
													<div class="form-group">
														<label for="cardCVC">CV CODE</label>
														<input 
															type="tel" 
															class="form-control"
															name="cardCVC"
															placeholder="CVC"
															autocomplete="cc-csc"
															required
															/>
													</div>
												</div>
											</div>
											
											
											<div class="row">
												<div class="col-xs-12">
													<div class="form-group">
														<label for="cardName">NAME ON CARD</label>
														<div class="input-group">
															<input 
																type="text"
																class="form-control"
																name="cardName"
																placeholder="Name on card"
																autocomplete="cc-name"
																required autofocus 
																/>
															<span class="input-group-addon"><i class="fa fa-user"></i></span>
														</div>
													</div>
												</div>
											</div>
											

											<div class="row">
												<div class="col-xs-12">
													<button class="btn btn-success btn-lg btn-block" type="submit">Start Subscription</button>
												</div>
											</div>
											
											
											
											<div class="row" style="display:none;">
												<div class="col-xs-12">
													<p class="payment-errors"></p>
												</div>
											</div>
										</form>
									</div>
								</div>			
								<!-- CREDIT CARD FORM ENDS HERE -->
								<?php } ?>
								
								
							</div>
						</div>
					</div>

					
				</div>
				
				<div class="search-examples">
					
				</div>	
				
			</div>
		</div>
	</div>
</div>
<?php include('templates/default/footer.php'); ?>