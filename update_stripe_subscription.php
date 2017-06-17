<?php
require('lib/init.php');
include('templates/default/header.php');
include('lib/classes/subscriptionClass.php');

global $userDetails;
$sub_status = $userDetails->stripe_sub_status;

?>

<div class="container-fluid content">
    <div class="main-container">
        <div class="row">

            <div class="col-md-6 col-md-offset-3">

                <div class="sub_result failed_sub">
                    <h1>Oops</h1>
                    <?php if ($sub_status == \Stripe\Subscription::STATUS_CANCELED) { ?>
                        <p>You cancelled your subscription.</p>
                        <p>If you want to reactivated your subscription, Click <a href="create_stripe_subscription.php">Here.</a></p>
                    <?php }?>

                </div>

            </div>

        </div>
    </div>
</div>

<?php
include('templates/default/footer.php');
?>
