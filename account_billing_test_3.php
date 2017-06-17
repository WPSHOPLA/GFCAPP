<?php
require('lib/init.php');
requireLogin();

$errorMsgPassword = '';
$errorMsgPassword2 = '';
$errorMsgEmail = '';

$notifyMsgUpdate = '';
$payStatusMsg = '';

$locations = [];
$extra_users = 0;
$extra_users_tiers = $mainClass->getExtraUsersTiers();

$is_canceling = (isset($_GET['action']) && $_GET['action'] == 'cancel_subscription') ? true : false;
$is_canceled = false;

//$paymentClass->updatePhone($userDetails->co_customer_id, '0755371378');


/*if (!empty($_POST['card_delete_submit'])) {
	$creditcard_id = $_POST['creditcard_id'];
	
	$cards = $paymentClass->getCards($userDetails->co_customer_id)->cards;
	
	foreach($cards as $card) {
		if($card->creditcard_id == $creditcard_id) {
			$deleteCard = $paymentClass->deleteCard($creditcard_id);
			break;
		}
	}	
} else if (!empty($_POST['card_add_submit'])) {
	$cc_number = $_POST['cc_number'];
	$cc_csc = $_POST['cc_csc'];
	$cc_name = $_POST['cc_name'];
	
	$cc_expire = $_POST['cc_expire'];
	$cc_month = explode('/', $cc_expire)[0];
	$cc_year = explode('/', $cc_expire)[1];
	
	$cc_address = $_POST['cc_address'];
	$cc_city = $_POST['cc_city'];
	$cc_zip = $_POST['cc_zip'];
	$cc_country = $_POST['cc_country'];

	//echo $cc_number . '<br>';
	//echo $cc_expire . '<br>';
	//echo $cc_csc . '<br>';
	//echo $cc_name . '<br>';
	
	$created_card = $paymentClass->createCard($userDetails->id, $userDetails->co_customer_id, $cc_number, $cc_year, $cc_month, $cc_name, $cc_address, $cc_city, $cc_zip, $cc_country);
}*/

if (!empty($_POST['subscription_add_submit'])) {
	$extra_users = $_POST['extra_users'];
	if(isset($_POST['locations']) && is_array($_POST['locations'])) {
		foreach($_POST['locations'] as $location) {
			$state_check = isset($mainClass->getStates()[$location]);
			if($state_check)
				array_push($locations, $location);
		}
	}
	
	
	//$extra_users_check = in_array($extra_users, $extra_users_tiers);
	$extra_users_check = $extra_users > 0;
	$locations_check = sizeof($locations) > 0;
	
	if(!$extra_users_check)
		$payStatusMsg = $mainClass->alert('error', 'Invalid team size.');
	
	if(!$locations_check)
		$payStatusMsg = $mainClass->alert('error', 'You must select at least one location.');
	
	if($extra_users_check && $locations_check) {
		$uid = $session_uid;
		$userClass->clearSubscriptionLocations($uid);
		$userClass->addSubscriptionLocations($uid, $locations);
		$userClass->setExtraUsers($uid, $extra_users);
		loadUserDetails();
		
		//print_r($locations);
		//die();
		
		$trial = $userDetails->used_trial == 0 ? true : false;
		
		$cc_number = $_POST['cc_number'];
		$cc_csc = $_POST['cc_csc'];
		$cc_name = $_POST['cc_name_last'] . ' ' . $_POST['cc_name_first'];
		
		/*$cc_expire = $_POST['cc_expire'];
		$cc_month = explode('/', $cc_expire)[0];
		$cc_year = explode('/', $cc_expire)[1];*/
		
		$cc_month = $_POST['cc_expire_month'];
		$cc_year = $_POST['cc_expire_year'];
		
		$cc_address = $_POST['cc_address'];
		$cc_city = $_POST['cc_city'];
		$cc_zip = $_POST['cc_zip'];
		$cc_country = $_POST['cc_country'];
		$cc_state = $_POST['cc_state'];
		
		$customer_phone = $_POST['customer_phone'];
		$paymentClass->updatePhone($userDetails->co_customer_id, $customer_phone);
		
		$location_items = [];
		$location_items['starter'][5] = 19; 	//gfc_california_starter_monthly
		$location_items['starter'][10] = 20; 	//gfc_florida_starter_monthly
		$location_items['team'][5] = 21;		//gfc_california_team_monthly
		$location_items['team'][10] = 22;		//gfc_florida_team_monthly
		
		$sub_type = $extra_users > 1 ? 'team' : 'starter';
		
		$item_ids = [];
		foreach($locations as $loc_id) {
			array_push($item_ids, $location_items[$sub_type][$loc_id]);
		}
		
		//print_r($item_ids);
		//die();
	
		$base_price = 23.40;
		$total_price = 0;
		$locations_count = sizeof($userClass->getSubscriptionLocations($session_uid));
		//$users_count = 1 + $userDetails->extra_users;					
		$users_count = $userDetails->extra_users;					
		$total_price = $base_price * $users_count * $locations_count;
		//$total_price = 0.01;
		//$line_quantity = $locations_count * $users_count;
		$line_quantity = $users_count;

		$created_card = $paymentClass->createCard($userDetails->id, $userDetails->co_customer_id, $cc_number, $cc_year, $cc_month, $cc_name, $cc_address, $cc_city, $cc_zip, $cc_country, $cc_state);
		if($created_card->status == 'success') {
			$created_subscription = $paymentClass->createSubscription($userDetails->id, $userDetails->co_customer_id, $item_ids, $created_card->id, $line_quantity, $trial ? 7 : 0);
			if($created_subscription->status == 'success') {
				if($trial) {
					$userClass->extendSubscription($session_uid, 7);
					$payStatusMsg = $mainClass->alert('success', 'Success');
					$userClass->setUsedTrial($uid, 1);
				} else {
					$send_invoice = $paymentClass->invoiceSubscription($created_subscription->id);
					if($send_invoice->status == 'success') {
						/*$pay_invoice = $paymentClass->payInvoice($userDetails->co_customer_id, $send_invoice->id, $total_price);
						if($pay_invoice->status == 'success') {
							$userClass->extendSubscription($session_uid, 30);
							$payStatusMsg = $mainClass->alert('success', 'Success');
						} else {
							$payStatusMsg = $mainClass->alert('error', $pay_invoice->message);
						}*/
					} else {
						$payStatusMsg = $mainClass->alert('error', $send_invoice->message);
					}
				}
			} else {
				$payStatusMsg = $mainClass->alert('error', $created_subscription->message);
			}
		} else {
			$payStatusMsg = $mainClass->alert('error', $created_card->message);
		}
	}
} else if (!empty($_POST['subscription_cancel'])) {
	$uid = $session_uid;
	
	$subscriptions = $paymentClass->getSubscriptions($userDetails->co_customer_id)->subscriptions;
	$currentSubscription = isset($subscriptions[0]) ? $subscriptions[0] : null;
	//$cancel_info_b_date = date('F j, Y', strtotime($currentSubscription->cache_next_invoice));
	$cancel_info_b_date = date('F j, Y', strtotime($currentSubscription->next_invoice_datetime));
	$cancel_info_until_date = date('F j, Y', strtotime('-1 day', strtotime($cancel_info_b_date)));
	$cancel_info_amount = $currentSubscription->amount_invoiced;
	
	$cancelSubscription = $paymentClass->cancelSubscription($_POST['package_id']);
	
	if($cancelSubscription->status == 'success') {
		$userClass->extendSubscription($session_uid, -1);
		$userClass->clearSubscriptionLocations($uid);
		$userClass->setExtraUsers($uid, $extra_users);
		loadUserDetails();
		$is_canceled = true;
		
		$mail_subject = 'GoFetchCode';
			
		$mail_content =  'We have cancelled your current subscription as per your request. You will be charged $' . $cancel_info_amount . ' until ' . $cancel_info_until_date . '.';
		$mail_content .= '\r\n';
		$mail_content .= 'You can contact us at support@gofetchcode.com in case you need help.';
		
		$mail_content_html = str_replace('\r\n', '<br />', $mail_content);
			
		$mailClass->sendMail($userDetails->email, $mail_subject, $mail_content, $mail_content_html);
	}
}

include('templates/default/header.php');
?>
<div class="container-fluid content">
	<div class="main-container">
		<div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-6 col-lg-offset-3">
			<div class="login-form" style="max-width: 1000px;">
				<?php if($is_canceling) {
					$subscriptions = $paymentClass->getSubscriptions($userDetails->co_customer_id)->subscriptions;
					$currentSubscription = isset($subscriptions[0]) ? $subscriptions[0] : null;
					
					echo '<div style="text-align: center;">';
					
					if(is_null($currentSubscription)) {
						if($is_canceled) {
							echo '<div class="h1 highlight">You are successfully unsubscribed. We regret to see you go. Email us for your feedback info@gofetchcode.com</div>';
						} else {
							echo '<div class="h1 highlight">You don\'t have an active subscription</div>';
						}
						
					} else {
						echo '<div class="h1 highlight">Are you sure you want cancel your subscription?</div>';
						
						echo '<form name="form" method="post">';
						echo '<input type="hidden" name="package_id" value="' . $currentSubscription->package_id . '">';
						echo '<input type="submit" name="subscription_cancel" class="full-wisdth" value="YES">&nbsp;&nbsp;&nbsp;';
						//echo '<input type="submit" name="" class="full-wisdth" value="NO">';
						echo '<a class="btn">NO</a>';
						echo '</form>';
					}
					
					
					//echo '<a class="btn btn-lg btn-success" href="http://www.google.com" target="_blank">Google</a>';
					echo '</div>';
				} else {
				?>
			
				<?php echo $payStatusMsg; ?>
				
				<div class="h1 text-blue">Subscription</div>
				
				<?php
				$subscriptions = $paymentClass->getSubscriptions($userDetails->co_customer_id)->subscriptions;
				$currentSubscription = isset($subscriptions[0]) ? $subscriptions[0] : null;
				
				//print_r($currentSubscription);
				
				if($currentSubscription) {
					//print_r($currentSubscription);
					//$subscription = $paymentClass->getSubscription($currentSubscription->package_id);
					$package_status = $currentSubscription->package_status_name;
					
					echo '<form name="form" method="post">
								<h5>Status: ' . $package_status . '</h5>';
								
								echo '<input type="hidden" name="package_id" value="' . $currentSubscription->package_id . '">
								<div class="form-actions form-group ">
									<!--<input type="submit" name="subscription_cancel" class="full-wisdth" value="Cancel Subscription">-->
								</div>
							</form>';
							
							
					
					//print_r($currentSubscription);
					//$b_date = date('F j, Y', strtotime($currentSubscription->cache_next_invoice));
					$b_date = date('F j, Y', strtotime($currentSubscription->next_invoice_datetime));
					$b_period = date('F j, Y', strtotime($currentSubscription->start_datetime)) . ' - ' . date('F j, Y', strtotime('-1 day', strtotime($b_date)));
					$b_payment_type = $currentSubscription->paymethod == 'crd' ? 'Credit Card' : '';
					$b_plan = $currentSubscription->paycycle == 'mon' ? 'Monthly' : '';
					
					if($b_payment_type == 'Credit Card') {
						//$card = $paymentClass->getCard($currentSubscription->creditcard_id)->card[0];
						//$b_payment_type .= '<br /> xxxx-xxxx-xxxx-' . substr($card->mask_number, 1);
						$b_payment_type .= '<br /> xxxx-xxxx-xxxx-xxxx';
					}
					
					//print_r($currentSubscription);
					//print_r($card);
	
					
					echo '<table class="table">
					
					<thead><tr>
					<th>Plan</th>
					<th>Amount paid</th>
					<th>Payment type</th>
					<th>Period</th>
					<th>Due date for the next</th>
					</tr></thead>
					
					<tbody><tr>
					<td>' . $b_plan. '</td>
					<td>$' . $currentSubscription->amount_collected . '</td>
					<td>' . $b_payment_type . '</td>
					<td>' . $b_period . '</td>
					<td>' . $b_date . '</td>
					</tr></tbody>
					
					</table>';
					
					//print_r($currentSubscription);
					
					//echo $currentSubscription->package_id;
					
					//$paymentClass->cancelSubscription($currentSubscription->package_id);
				} else {
				?>
				
				
				
					<div class="row">
						<div class="col-sm-6">
					

							
							<form name="form" method="post" target="_blank">
							
							<div class="form-group">
								<label>Team Size</label>
								<input type="number" name="extra_users" class="form-control" placeholder="1" value="1" required>								
							</div>

							<div class="form-group">
								<label>Locations</label>
								<div class="form-control">
								<?php
								foreach($mainClass->getStates() as $state) {
									if($state['id'] != 5 && $state['id'] != 10)
										continue;
									
									echo '<label class="col-sm-6 col-md-6"><input type="checkbox" class="location" name="locations[]" value="' . $state['id'] . '"> ' . $state['name'] . '</label>';
								}
								?>
								</div>
							</div>
							
							<div class="h1 text-blue">Payment information</div>
							
							
							<div class="form-group">
								<input type="text" name="cc_number" class="form-control" placeholder="Debit or credit card number" autocomplete="cc-number" required size="10">
							</div>
									
							<div class="row">
								<div class="col-xs-12 col-sm-4">		
									<label>Expiration date</label>
									<div class="form-group">
										<select name="cc_expire_month" class="form-control" autocomplete="cc-exp" required>
											<option selected>MM</option>
											<option value='1'>01</option>
											<option value='2'>02</option>
											<option value='3'>03</option>
											<option value='4'>04</option>
											<option value='5'>05</option>
											<option value='6'>06</option>
											<option value='7'>07</option>
											<option value='8'>08</option>
											<option value='9'>09</option>
											<option value='10'>10</option>
											<option value='11'>11</option>
											<option value='12'>12</option>
										</select> 
									</div>
								</div>
								<div class="col-xs-12 col-sm-4">	
									<label>&nbsp;</label>
									<div class="form-group">
										<select name="cc_expire_year" class="form-control" autocomplete="cc-exp" required>
											<option selected>YYYY</option>
											<option value='2017'>2017</option>
											<option value='2018'>2018</option>
											<option value='2019'>2019</option>
											<option value='2020'>2020</option>
											<option value='2021'>2021</option>
											<option value='2022'>2022</option>
											<option value='2023'>2023</option>
											<option value='2024'>2024</option>
											<option value='2025'>2025</option>
											<option value='2026'>2026</option>
											<option value='2027'>2027</option>
											<option value='2028'>2028</option>
										</select> 
									</div>
								</div>
								<div class="col-xs-12 col-sm-4">		
									<label>Secuirty code</label>
									<div class="form-group">
										<input type="text" name="cc_csc" class="form-control" placeholder="CVC" autocomplete="cc-csc" required>
									</div>
								</div>
							</div>
									
									<div class="form-group">
										<input type="text" name="cc_name_first" class="form-control" placeholder="First name on card" autocomplete="cc-name" required>
									</div>
									
									<div class="form-group">
										<input type="text" name="cc_name_last" class="form-control" placeholder="Last name on card" autocomplete="cc-name" required>
									</div>
									
									<div class="form-group">
										<input type="text" name="customer_phone" class="form-control" placeholder="Phone number" autocomplete="billing phone" required>
									</div>
									
									<div class="form-group">
										<input type="text" name="cc_address" class="form-control" placeholder="Address" autocomplete="billing street-address" required>
									</div>
																		
									<div class="form-group">
										<input type="text" name="cc_city" class="form-control" placeholder="City" autocomplete="billing locality" required>
									</div>
									
									<div class="form-group">
										<input type="text" name="cc_state" class="form-control" placeholder="State" autocomplete="billing state" required>
									</div>
									
									<div class="form-group">
										<input type="text" name="cc_zip" class="form-control" placeholder="Zip or postal code" autocomplete="billing postal-code" required>
									</div>
	
									<div class="form-group">
										<!--<input type="text" name="cc_country" class="form-control" placeholder="Country" autocomplete="billing country" required>-->
										<select name="cc_country" class="form-control" autocomplete="billing country" required>
											<option value="Afghanistan">Afghanistan</option>
											<option value="Albania">Albania</option>
											<option value="Algeria">Algeria</option>
											<option value="American Samoa">American Samoa</option>
											<option value="Andorra">Andorra</option>
											<option value="Angola">Angola</option>
											<option value="Anguilla">Anguilla</option>
											<option value="Antartica">Antarctica</option>
											<option value="Antigua and Barbuda">Antigua and Barbuda</option>
											<option value="Argentina">Argentina</option>
											<option value="Armenia">Armenia</option>
											<option value="Aruba">Aruba</option>
											<option value="Australia">Australia</option>
											<option value="Austria">Austria</option>
											<option value="Azerbaijan">Azerbaijan</option>
											<option value="Bahamas">Bahamas</option>
											<option value="Bahrain">Bahrain</option>
											<option value="Bangladesh">Bangladesh</option>
											<option value="Barbados">Barbados</option>
											<option value="Belarus">Belarus</option>
											<option value="Belgium">Belgium</option>
											<option value="Belize">Belize</option>
											<option value="Benin">Benin</option>
											<option value="Bermuda">Bermuda</option>
											<option value="Bhutan">Bhutan</option>
											<option value="Bolivia">Bolivia</option>
											<option value="Bosnia and Herzegowina">Bosnia and Herzegowina</option>
											<option value="Botswana">Botswana</option>
											<option value="Bouvet Island">Bouvet Island</option>
											<option value="Brazil">Brazil</option>
											<option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
											<option value="Brunei Darussalam">Brunei Darussalam</option>
											<option value="Bulgaria">Bulgaria</option>
											<option value="Burkina Faso">Burkina Faso</option>
											<option value="Burundi">Burundi</option>
											<option value="Cambodia">Cambodia</option>
											<option value="Cameroon">Cameroon</option>
											<option value="Canada">Canada</option>
											<option value="Cape Verde">Cape Verde</option>
											<option value="Cayman Islands">Cayman Islands</option>
											<option value="Central African Republic">Central African Republic</option>
											<option value="Chad">Chad</option>
											<option value="Chile">Chile</option>
											<option value="China">China</option>
											<option value="Christmas Island">Christmas Island</option>
											<option value="Cocos Islands">Cocos (Keeling) Islands</option>
											<option value="Colombia">Colombia</option>
											<option value="Comoros">Comoros</option>
											<option value="Congo">Congo</option>
											<option value="Congo">Congo, the Democratic Republic of the</option>
											<option value="Cook Islands">Cook Islands</option>
											<option value="Costa Rica">Costa Rica</option>
											<option value="Cota D'Ivoire">Cote d'Ivoire</option>
											<option value="Croatia">Croatia (Hrvatska)</option>
											<option value="Cuba">Cuba</option>
											<option value="Cyprus">Cyprus</option>
											<option value="Czech Republic">Czech Republic</option>
											<option value="Denmark">Denmark</option>
											<option value="Djibouti">Djibouti</option>
											<option value="Dominica">Dominica</option>
											<option value="Dominican Republic">Dominican Republic</option>
											<option value="East Timor">East Timor</option>
											<option value="Ecuador">Ecuador</option>
											<option value="Egypt">Egypt</option>
											<option value="El Salvador">El Salvador</option>
											<option value="Equatorial Guinea">Equatorial Guinea</option>
											<option value="Eritrea">Eritrea</option>
											<option value="Estonia">Estonia</option>
											<option value="Ethiopia">Ethiopia</option>
											<option value="Falkland Islands">Falkland Islands (Malvinas)</option>
											<option value="Faroe Islands">Faroe Islands</option>
											<option value="Fiji">Fiji</option>
											<option value="Finland">Finland</option>
											<option value="France">France</option>
											<option value="France Metropolitan">France, Metropolitan</option>
											<option value="French Guiana">French Guiana</option>
											<option value="French Polynesia">French Polynesia</option>
											<option value="French Southern Territories">French Southern Territories</option>
											<option value="Gabon">Gabon</option>
											<option value="Gambia">Gambia</option>
											<option value="Georgia">Georgia</option>
											<option value="Germany">Germany</option>
											<option value="Ghana">Ghana</option>
											<option value="Gibraltar">Gibraltar</option>
											<option value="Greece">Greece</option>
											<option value="Greenland">Greenland</option>
											<option value="Grenada">Grenada</option>
											<option value="Guadeloupe">Guadeloupe</option>
											<option value="Guam">Guam</option>
											<option value="Guatemala">Guatemala</option>
											<option value="Guinea">Guinea</option>
											<option value="Guinea-Bissau">Guinea-Bissau</option>
											<option value="Guyana">Guyana</option>
											<option value="Haiti">Haiti</option>
											<option value="Heard and McDonald Islands">Heard and Mc Donald Islands</option>
											<option value="Holy See">Holy See (Vatican City State)</option>
											<option value="Honduras">Honduras</option>
											<option value="Hong Kong">Hong Kong</option>
											<option value="Hungary">Hungary</option>
											<option value="Iceland">Iceland</option>
											<option value="India">India</option>
											<option value="Indonesia">Indonesia</option>
											<option value="Iran">Iran (Islamic Republic of)</option>
											<option value="Iraq">Iraq</option>
											<option value="Ireland">Ireland</option>
											<option value="Israel">Israel</option>
											<option value="Italy">Italy</option>
											<option value="Jamaica">Jamaica</option>
											<option value="Japan">Japan</option>
											<option value="Jordan">Jordan</option>
											<option value="Kazakhstan">Kazakhstan</option>
											<option value="Kenya">Kenya</option>
											<option value="Kiribati">Kiribati</option>
											<option value="Democratic People's Republic of Korea">Korea, Democratic People's Republic of</option>
											<option value="Korea">Korea, Republic of</option>
											<option value="Kuwait">Kuwait</option>
											<option value="Kyrgyzstan">Kyrgyzstan</option>
											<option value="Lao">Lao People's Democratic Republic</option>
											<option value="Latvia">Latvia</option>
											<option value="Lebanon" selected>Lebanon</option>
											<option value="Lesotho">Lesotho</option>
											<option value="Liberia">Liberia</option>
											<option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
											<option value="Liechtenstein">Liechtenstein</option>
											<option value="Lithuania">Lithuania</option>
											<option value="Luxembourg">Luxembourg</option>
											<option value="Macau">Macau</option>
											<option value="Macedonia">Macedonia, The Former Yugoslav Republic of</option>
											<option value="Madagascar">Madagascar</option>
											<option value="Malawi">Malawi</option>
											<option value="Malaysia">Malaysia</option>
											<option value="Maldives">Maldives</option>
											<option value="Mali">Mali</option>
											<option value="Malta">Malta</option>
											<option value="Marshall Islands">Marshall Islands</option>
											<option value="Martinique">Martinique</option>
											<option value="Mauritania">Mauritania</option>
											<option value="Mauritius">Mauritius</option>
											<option value="Mayotte">Mayotte</option>
											<option value="Mexico">Mexico</option>
											<option value="Micronesia">Micronesia, Federated States of</option>
											<option value="Moldova">Moldova, Republic of</option>
											<option value="Monaco">Monaco</option>
											<option value="Mongolia">Mongolia</option>
											<option value="Montserrat">Montserrat</option>
											<option value="Morocco">Morocco</option>
											<option value="Mozambique">Mozambique</option>
											<option value="Myanmar">Myanmar</option>
											<option value="Namibia">Namibia</option>
											<option value="Nauru">Nauru</option>
											<option value="Nepal">Nepal</option>
											<option value="Netherlands">Netherlands</option>
											<option value="Netherlands Antilles">Netherlands Antilles</option>
											<option value="New Caledonia">New Caledonia</option>
											<option value="New Zealand">New Zealand</option>
											<option value="Nicaragua">Nicaragua</option>
											<option value="Niger">Niger</option>
											<option value="Nigeria">Nigeria</option>
											<option value="Niue">Niue</option>
											<option value="Norfolk Island">Norfolk Island</option>
											<option value="Northern Mariana Islands">Northern Mariana Islands</option>
											<option value="Norway">Norway</option>
											<option value="Oman">Oman</option>
											<option value="Pakistan">Pakistan</option>
											<option value="Palau">Palau</option>
											<option value="Panama">Panama</option>
											<option value="Papua New Guinea">Papua New Guinea</option>
											<option value="Paraguay">Paraguay</option>
											<option value="Peru">Peru</option>
											<option value="Philippines">Philippines</option>
											<option value="Pitcairn">Pitcairn</option>
											<option value="Poland">Poland</option>
											<option value="Portugal">Portugal</option>
											<option value="Puerto Rico">Puerto Rico</option>
											<option value="Qatar">Qatar</option>
											<option value="Reunion">Reunion</option>
											<option value="Romania">Romania</option>
											<option value="Russia">Russian Federation</option>
											<option value="Rwanda">Rwanda</option>
											<option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option> 
											<option value="Saint LUCIA">Saint LUCIA</option>
											<option value="Saint Vincent">Saint Vincent and the Grenadines</option>
											<option value="Samoa">Samoa</option>
											<option value="San Marino">San Marino</option>
											<option value="Sao Tome and Principe">Sao Tome and Principe</option> 
											<option value="Saudi Arabia">Saudi Arabia</option>
											<option value="Senegal">Senegal</option>
											<option value="Seychelles">Seychelles</option>
											<option value="Sierra">Sierra Leone</option>
											<option value="Singapore">Singapore</option>
											<option value="Slovakia">Slovakia (Slovak Republic)</option>
											<option value="Slovenia">Slovenia</option>
											<option value="Solomon Islands">Solomon Islands</option>
											<option value="Somalia">Somalia</option>
											<option value="South Africa">South Africa</option>
											<option value="South Georgia">South Georgia and the South Sandwich Islands</option>
											<option value="Span">Spain</option>
											<option value="SriLanka">Sri Lanka</option>
											<option value="St. Helena">St. Helena</option>
											<option value="St. Pierre and Miguelon">St. Pierre and Miquelon</option>
											<option value="Sudan">Sudan</option>
											<option value="Suriname">Suriname</option>
											<option value="Svalbard">Svalbard and Jan Mayen Islands</option>
											<option value="Swaziland">Swaziland</option>
											<option value="Sweden">Sweden</option>
											<option value="Switzerland">Switzerland</option>
											<option value="Syria">Syrian Arab Republic</option>
											<option value="Taiwan">Taiwan, Province of China</option>
											<option value="Tajikistan">Tajikistan</option>
											<option value="Tanzania">Tanzania, United Republic of</option>
											<option value="Thailand">Thailand</option>
											<option value="Togo">Togo</option>
											<option value="Tokelau">Tokelau</option>
											<option value="Tonga">Tonga</option>
											<option value="Trinidad and Tobago">Trinidad and Tobago</option>
											<option value="Tunisia">Tunisia</option>
											<option value="Turkey">Turkey</option>
											<option value="Turkmenistan">Turkmenistan</option>
											<option value="Turks and Caicos">Turks and Caicos Islands</option>
											<option value="Tuvalu">Tuvalu</option>
											<option value="Uganda">Uganda</option>
											<option value="Ukraine">Ukraine</option>
											<option value="United Arab Emirates">United Arab Emirates</option>
											<option value="United Kingdom">United Kingdom</option>
											<option value="United States" selected>United States</option>
											<option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
											<option value="Uruguay">Uruguay</option>
											<option value="Uzbekistan">Uzbekistan</option>
											<option value="Vanuatu">Vanuatu</option>
											<option value="Venezuela">Venezuela</option>
											<option value="Vietnam">Viet Nam</option>
											<option value="Virgin Islands (British)">Virgin Islands (British)</option>
											<option value="Virgin Islands (U.S)">Virgin Islands (U.S.)</option>
											<option value="Wallis and Futana Islands">Wallis and Futuna Islands</option>
											<option value="Western Sahara">Western Sahara</option>
											<option value="Yemen">Yemen</option>
											<option value="Yugoslavia">Yugoslavia</option>
											<option value="Zambia">Zambia</option>
											<option value="Zimbabwe">Zimbabwe</option>
										</select>
									</div>
											
									
							
									<div class="form-actions form-group ">
										<input type="submit" name="subscription_add_submit" class="full-width" value="Subscribe">
									</div>
							
							</form>
					
						</div>
						
						<div class="col-sm-6">
						
							<br />
							<table class="table" id="order_summary" style="display: none;">
								<thead>
									<tr>
									  <th style="text-align: center;" colspan="3">Order summary</th>
									</tr>
								</thead>
								
								<tbody>
									
								</tbody>
								
							</table>
							
							<script>
							var location_items = [];
							location_items['starter'] = [];
							location_items['team'] = [];
							location_items['starter'][5] = 20; 	//gfc_california_starter_monthly
							location_items['starter'][10] = 35; 	//gfc_florida_starter_monthly
							location_items['team'][5] = 10;		//gfc_california_team_monthly
							location_items['team'][10] = 20;		//gfc_florida_team_monthly
				
					
							
							$(function() {
								$('[name=extra_users]').change(function() {updateSummary()});
								$('.location').change(function() {updateSummary()});
							});
							//setInterval(updateSummary, 1000);
							
							function updateSummary() {
								var team_size = $('[name=extra_users]').val();
								var table = $('#order_summary').find('tbody');
								
								if(team_size < 1 || $('.location:checked').length < 1) {
									$('#order_summary').hide();
									$('#summaryNote').hide();
									return;
								} else {
									$('#order_summary').show();
									$('#summaryNote').show();
								}
								
								var table = $('#order_summary').find('tbody');
								table.html('<tr><td>Team Size: 10</td></tr>');
								
								table.append('<tr><td>Search Codes Location</td><td>Price</td><td align="right">Subtotal</td></tr>');
								
								var total = 0;
								
								var quantity = $('[name=extra_users]').val();
								var sub_type = quantity > 1 ? 'team' : 'starter';
								
								$('.location:checked').each(function() {
									var location_name = $(this).parent().text();
									var location_id = $(this).val();
									var location_price = location_items[sub_type][location_id];
									//console.log(location_id);
									
									total += (location_price * quantity);
									
									table.append('<tr><td>' + location_name + '</td><td>' + location_price + ' USD x ' + quantity + '</td><td align="right">' + (location_price * quantity) + ' USD</td></tr>');
								});
								
									
								table.append('<tr><td><b>Total: ' + total + ' USD</b></td><td></td><td></td></tr>');
							}
							</script>
							
							<div id="summaryNote" style="display: none;">
								<div><b>Your subscription will be billed monthly</b></div>
								<br />
								<div style="color: #f00;"><b>No charge will be processed on your card during free trial. You may chose to cancel at any time.</b></div>
							</div>
							
						</div>
					
					</div>
				<?php
				}
				
				
				//$cards = $paymentClass->getCards($userDetails->co_customer_id)->cards;
				
				//print_r($cards);
				?>
		
								
				<?php
				/*if(sizeof($cards) > 0) {			
					//print_r($cards);
					foreach($cards as $card) {
						echo '<form name="form" method="post">
								<h5>Number: xxxx-xxxx-xxxx-' . substr($card->mask_number, 1) . '</h5>
								<h5>Name: ' . $card->name . '</h5>
								<h5>Expire Date: ' . $card->expdate_formatted . '</h5>';
								//<h5>Address: ' . $card->address . '</h5>
								//<h5>City: ' . $card->city . '</h5>
								//<h5>Post Code: ' . $card->postcode . '</h5>
								//<h5>Country: ' . $card->country . '</h5>
								
								echo '<input type="hidden" name="creditcard_id" value="' . $card->creditcard_id . '">
								<div class="form-actions form-group ">
									<input type="submit" name="card_delete_submit" class="full-width" value="Delete">
								</div>
							</form>';
					}
				} else {
					echo '<form name="form" method="post">
					
							<label>Card Number</label>
							<div class="form-group">
								<input type="text" name="cc_number" class="form-control" placeholder="XXXX-XXXX-XXXX-XXXX" autocomplete="cc-number" required>
							</div>
							
							<label>Expiration Date</label>
							<div class="form-group">
								<input type="text" name="cc_expire" class="form-control" placeholder="MM/YYYY" autocomplete="cc-exp" required>
							</div>
							
							<label>CSC</label>
							<div class="form-group">
								<input type="text" name="cc_csc" class="form-control" placeholder="123" autocomplete="cc-csc" required>
							</div>
							
							<label>Name on Card</label>
							<div class="form-group">
								<input type="text" name="cc_name" class="form-control" placeholder="Name" autocomplete="cc-name" required>
							</div>
							
							<label>Address</label>
							<div class="form-group">
								<input type="text" name="cc_address" class="form-control" placeholder="Address" autocomplete="billing street-address" required>
							</div>
							
							<label>City</label>
							<div class="form-group">
								<input type="text" name="cc_city" class="form-control" placeholder="City" autocomplete="billing locality" required>
							</div>
							
							<label>Post Code</label>
							<div class="form-group">
								<input type="text" name="cc_zip" class="form-control" placeholder="Post Code" autocomplete="billing postal-code" required>
							</div>
							
							<label>Country</label>
							<div class="form-group">
								<input type="text" name="cc_country" class="form-control" placeholder="Country" autocomplete="billing country" required>
							</div>
							
							<div class="form-actions form-group ">
								<input type="submit" name="card_add_submit" class="full-width" value="Add Card">
							</div>
						</form>';
				}*/
				?>
				
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<!-- Google Code for Website Lead from Adwords Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 865029976;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "vkiNCJrvpHAQ2J69nAM";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/865029976/?label=vkiNCJrvpHAQ2J69nAM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
<?php include('templates/default/footer.php'); ?>