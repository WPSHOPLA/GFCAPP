<?php
require('lib/init.php');
include('lib/classes/subscriptionClass.php');

requireLogin();
$sub = new subscriptionClass();

/*Create Subscription*/
if (isset($_POST) && $_POST['action'] == "create_subscription") {

    //array(7) {
// ["locations"]=> array(1) { [0]=> string(10) "california" }
// ["extra_users"]=> string(1) "1"
// ["cardholder-name"]=> string(10) "Hacken Lee"
// ["phone-number"]=> string(12) "129 421 3325"
// ["address-zip"]=> string(3) "532"
// ["address-country"]=> string(2) "us"
// ["token"]=> string(28) "tok_1ASsZELAFQ896ylUrxieEFa4" }
//["total_amount"]=> string(3) "120"

    $card_token = $_POST['token'];

    $total_amount = 100 * floatval($_POST['total_amount']);

    global $session_uid;
    global $mainClass;

    $userDetails = $userClass->userDetails($session_uid);
    $customer_email = $userDetails->email;

    $subscription = $sub->create_subscription_by_card($card_token, $total_amount, $customer_email);
    //$subscription = null;

    if (!$subscription) {

        count_fail_sub();

        //fail subscription
        $url = BASE_URL . 'failed_stripe_subscription.php';
        header("Location: $url");
        exit();

    } else {

        $sub_id = $subscription->id;
        $sub_status = $subscription->status;

        if (($sub_status != \Stripe\Subscription::STATUS_TRIALING) && ($sub_status != \Stripe\Subscription::STATUS_ACTIVE)) {

            count_fail_sub();

            //fail subscription
            $url = BASE_URL . 'failed_stripe_subscription.php';
            header("Location: $url");
            exit();
        }

        //save subscription info
        $extra_users = $_POST['extra_users'];

        $locations = [];
        foreach ($_POST['locations'] as $location) {
            $state_check = isset($mainClass->getStates()[$location]);
            if ($state_check)
                array_push($locations, $location);
        }

        $userClass->clearSubscriptionLocations($session_uid);
        $userClass->addSubscriptionLocations($session_uid, $locations);
        $userClass->setExtraUsers($session_uid, $extra_users);

        $userClass->updateStripeSubscriptionInfo($session_uid, $sub_id, $sub_status);

        $_SESSION['fail_sub_count'] = 0;

        $url = BASE_URL . 'success_stripe_subscription.php';
        header("Location: $url");
        exit();
    }


} else if (isset($_POST) && $_POST['action'] == "cancel_subscription") {

    global $session_uid, $mailClass;
    try {
        $userDetails = $userClass->userDetails($session_uid);
        $subscription_id = $userDetails->stripe_sub_id;

        $sub = \Stripe\Subscription::retrieve($subscription_id);
        $sub->cancel();

        //update user's subscription status
        $userClass->updateStripeSubscriptionInfo($session_uid, $subscription_id, $sub->status);

        $_SESSION['first_cancel_sub'] = true;

        echo "success";

        if ($_SERVER['HTTP_HOST'] != "localhost") {
            //send email to customer
            $user_email = $userDetails->email;
            $mail_subject = "You unsubscribed from Gofetch Successfully";
            $mail_content = "We have cancelled your current subscription as per your request.<br />";
            //$mail_content .=" You will be charged $... until mm/dd/yy. ";
            $mail_content .= "You can contact us at support@gofetchcode.com in case you need help.";
            $mail_content_html = str_replace('\r\n', '<br />', $mail_content);
            $mailClass->sendMail($user_email, $mail_subject, $mail_content, $mail_content_html);
        }


    } catch (Exception $e) {
        echo "Failed";
    }

    exit();
} else if (isset($_POST) && $_POST['action'] == "send_feedback") {

    global $userDetails, $mailClass;

    $user_email = $userDetails->email;

    $mail_subject = 'Feedback from Customer';

    $mail_content = $_POST['feedback'];

    $mail_content_html = str_replace('\r\n', '<br />', $mail_content);

    $result = $mailClass->getMail($user_email, null, $mail_subject, $mail_content, $mail_content_html);

    $_SESSION['first_cancel_sub'] = false;

    if ($result) {
        echo "success";
    } else {
        echo "Could not be sent the feedback. Please try again later.";
    }
    exit();
}

