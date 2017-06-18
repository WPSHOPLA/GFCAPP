<?php
class paymentClass {
	private $API;
		
	public function __construct() {
		include ROOT_DIR . '/lib/classes/ChargeOverAPI.php';
		
		$url = 'https://gofetchcode.chargeover.com/api/v3';
		$authmode = ChargeOverAPI::AUTHMODE_HTTP_BASIC;
		$username = 'X5Ci1mxVKBhQ3kjUZqbED6e9zp82Lads';
		$password = 'efMxvTHmpREFGqzNIPVs8ahr6OXuCkJ3';
		
		$this->API = new ChargeOverAPI($url, $authmode, $username, $password);
	}
	
	public function createCustomer($user_id, $user_username, $user_email) {
		$Customer = new ChargeOverAPI_Object_Customer(array(
			'company' => $user_username,
			'external_key' => 'gfc_users_' . $user_id . '_' . $user_username . '_' . $user_email . '_' . time(),
			'superuser_name' => $user_username,
			'superuser_email' => $user_email
		));
		
		$resp = $this->API->create($Customer);
		if (!$this->API->isError($resp)) {
			$customer_id = $resp->response->id;
			return $customer_id;
		} else {
			print('error saving customer via API: ' . $this->API->lastError());
			die();
			return -1;
			/*print('error saving customer via API: ' . $this->API->lastError());
			print("\n\n\n\n");
			print($this->API->lastRequest());
			print("\n\n\n\n");
			print($this->API->lastResponse());
			print("\n\n\n\n");*/
		}
	}
	
	public function updatePhone($customer_id, $phone) {
		$Customer = new ChargeOverAPI_Object_Customer(array(		
			'superuser_phone' => $phone
		));
		
		$resp = $this->API->modify($customer_id, $Customer);
				
		if (!$this->API->isError($resp)) {
			$invoice_id = $resp->response->id;
			return (object) array('status' => 'success', 'id' => $resp->response->id);
		} else {
			return (object) array('status' => 'error', 'message' => $resp->message);
		}
	}
	
	public function createCard($user_id, $customer_id, $number, $expdate_year, $expdate_month, $name, $address = '', $city = '', $postcode = '', $country = '', $state = '') {
		$CreditCard = new ChargeOverAPI_Object_CreditCard(array(
			'customer_id' => $customer_id, // Must be the customer ID of an existing customer in ChargeOver
			//'customer_external_key' => 'abcd12345', 
			'number' => $number, 
			'expdate_year' => $expdate_year, 
			'expdate_month' => $expdate_month, 
			'name' => $name,
			'address' => $address,
			'city' => $city,
			'postcode' => $postcode,
			'country' => $country,
			'state' => $state
			));
			
		$resp = $this->API->create($CreditCard);
		if (!$this->API->isError($resp)) {
			$creditcard_id = $resp->response->id;
			return (object) array('status' => 'success', 'id' => $creditcard_id);
			//return $creditcard_id;
		} else {
			return (object) array('status' => 'error', 'message' => $resp->message);
			//return $resp;
			//print('Error saving credit card via API!');
			//print($this->API->lastResponse());
		}
	}
	
	public function deleteCard($creditcard_id) {
		$resp = $this->API->delete(ChargeOverAPI_Object::TYPE_CREDITCARD, $creditcard_id);
		if (!$this->API->isError($resp)) {
			return (object) array('status' => 'success');
		} else {
			return (object) array('status' => 'error', 'message' => $resp->message);
		}
	}
	
	public function getCards($customer_id) {
		$resp = $this->API->find('creditcard', array('customer_id:EQUALS:' . $customer_id));
		if (!$this->API->isError($resp)) {
			return (object) array('status' => 'success', 'cards' => $resp->response);
		} else {
			return (object) array('status' => 'error', 'message' => $resp->message);
		}
	}
	
	public function getCard($creditcard_id) {
		$resp = $this->API->find('creditcard', array('creditcard_id:EQUALS:' . $creditcard_id));
		if (!$this->API->isError($resp)) {
			return (object) array('status' => 'success', 'card' => $resp->response);
		} else {
			return (object) array('status' => 'error', 'message' => $resp->message);
		}
	}
	
	
	public function invoiceSubscription($subscription_id) {
		$resp = $this->API->action('package', $subscription_id, 'invoice');
		if (!$this->API->isError($resp)) {
			$invoice_id = $resp->response->id;
			return (object) array('status' => 'success', 'id' => $invoice_id);
		} else {
			return (object) array('status' => 'error', 'message' => $resp->message);
		}
	}
	
	public function cancelSubscription($subscription_id) {
		$resp = $this->API->action('package', $subscription_id, 'cancel');
		if (!$this->API->isError($resp)) {
			//$invoice_id = $resp->response->id;
			return (object) array('status' => 'success');
		} else {
			return (object) array('status' => 'error', 'message' => $resp->message);
		}
	}
	
	public function getSubscriptions($customer_id) {
		$resp = $this->API->find('package', array('package_status_state:EQUALS:a', 'customer_id:EQUALS:' . $customer_id));
		if (!$this->API->isError($resp)) {
			return (object) array('status' => 'success', 'subscriptions' => $resp->response);
		} else {
			return (object) array('status' => 'error', 'message' => $resp->message);
		}
	}
	
	public function getSubscription($subscription_id) {
		$resp = $this->API->find('package', 11111);
		if (!$this->API->isError($resp)) {
			return (object) array('status' => 'success', 'subscription' => $resp->response);
		} else {
			return (object) array('status' => 'error', 'message' => $resp->message);
		}
	}
	
	
	public function payInvoice($customer_id, $invoice_id, $amount) {
		$resp = $this->API->action('transaction', null, 'pay', array(
					'customer_id' => $customer_id, 
					'amount' => $amount, 
					'applied_to' => array(
						'invoice_id' => $invoice_id
					)
				));
		if (!$this->API->isError($resp)) {
			$payment_id = $resp->response->id;
			return (object) array('status' => 'success', 'id' => $payment_id);
		} else {
			return (object) array('status' => 'error', 'message' => $resp->message);
		}
	}
	
	public function createSubscription($user_id, $customer_id, $item_ids, $card_id, $line_quantity, $trial_days = 0) {
		$Package = new ChargeOverAPI_Object_Package();
		$Package->setCustomerId($customer_id);
		//$Package->setPaymethod('crd');
		$Package->setCreditcardId($card_id);
		
		//$trial_days = 7;
		
		foreach($item_ids as $item_id) {
			$LineItem = new ChargeOverAPI_Object_LineItem();
			$LineItem->setItemId($item_id);
			
			if($trial_days > 0)
				$LineItem->setTrialDays($trial_days);
			
			$LineItem->setLineQuantity($line_quantity);
			$Package->addLineItems($LineItem);
		}
		
		if($trial_days > 0) {
			$Package->setHolduntilDatetime(date('Y-m-d H:i:s', strtotime('+1 week')));
		}
		
		$resp = $this->API->create($Package);
		if (!$this->API->isError($resp)) {
			$package_id = $resp->response->id;
			return (object) array('status' => 'success', 'id' => $package_id);
		} else {
			return (object) array('status' => 'error', 'message' => $resp->message);
		}
	}
	
	/*public function createSubscription($user_id, $customer_id, $item_id, $card_id, $line_quantity, $trial_days = 0) {
		$Package = new ChargeOverAPI_Object_Package();
		$Package->setCustomerId($customer_id);
		//$Package->setPaymethod('crd');
		$Package->setCreditcardId($card_id);
		
		$LineItem = new ChargeOverAPI_Object_LineItem();
		$LineItem->setItemId($item_id);
		if($trial_days > 0)
			$LineItem->setTrialDays($trial_days);
		
		$LineItem->setLineQuantity($line_quantity);
		
		
		$Package->addLineItems($LineItem);
		
		$resp = $this->API->create($Package);
		if (!$this->API->isError($resp)) {
			$package_id = $resp->response->id;
			return (object) array('status' => 'success', 'id' => $package_id);
		} else {
			return (object) array('status' => 'error', 'message' => $resp->message);
		}
	}*/
	
	
}
?>