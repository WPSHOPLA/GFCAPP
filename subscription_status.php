<?php
require('lib/init.php');

include('templates/default/header.php');

include('lib/classes/subscriptionClass.php');

requireLogin();

global $userDetails;

$sub_id = $userDetails->stripe_sub_id;
$sub_c = new subscriptionClass();

$sub = $sub_c->get_subscription_instance($sub_id);
$plan = $sub->plan;
$customer_id = $sub->customer;

$customer = \Stripe\Customer::retrieve($customer_id);
$card = $customer->sources->data[0];

$sub_status = $sub->status;

?>

<div class="container-fluid content">
    <div class="main-container">

        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="h1 highlight">Subscription Status</div>

                <h4 class="mt50">Status: <?php echo $sub_status; ?></h4>

                <?php if ($sub_status == "canceled") {
                    ?>

                    <?php if (isset($_SESSION['first_cancel_sub'])) {

                        if ($_SESSION['first_cancel_sub'] == true) {

                            $_SESSION['first_cancel_sub'] = false;
                            ?>
                            <h5>Hello <?php echo $userDetails->username; ?></h5>
                            <h5>You are successfully unsubscribed.</h5>
                            <h5>We regret to see you go. Email us for your feedback to info@gofetchcode.com</h5>

                            <div class="row">
                                <div class="col-md-8">
                                    <label for="feedback">Feedback:</label>
                                    <textarea rows="3" name="feedback" id="feedback_area" title="feedback"></textarea>
                                    <button class="btn pri_button" id="feedback_bt">Send Feedback</button>
                                </div>
                            </div>
                            <div class="mt50">
                                <div id="err_msg" class="text-warning"></div>
                            </div>
                        <?php } else {

                        }
                    } ?>

                <?php } else { ?>

                    <div class="sub_info mt50">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Trial Start Date</label>
                            </div>
                            <div class="col-md-8">
                                <?php echo date('Y-m-d', $sub->trial_start); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label>Trial End Date</label>
                            </div>
                            <div class="col-md-8">
                                <?php echo date('Y-m-d', $sub->trial_end); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label>Trial Period Days</label>
                            </div>
                            <div class="col-md-8">
                                <?php echo $plan->trial_period_days; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label>Start Date</label>
                            </div>
                            <div class="col-md-8">
                                <?php echo date('Y-m-d', $sub->current_period_start); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label>End Date</label>
                            </div>
                            <div class="col-md-8">
                                <?php echo date('Y-m-d', $sub->current_period_end); ?>
                            </div>
                        </div>

                        <?php if ($sub_status == \Stripe\Subscription::STATUS_CANCELED) { ?>
                            <div class="row">
                                <div class="col-md-4">Ended Date</div>
                                <div class="col-md-8"></div>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <div class="col-md-4">
                                <label>Monthly Bill Amount</label>
                            </div>
                            <div class="col-md-8"><?php echo $plan->amount / 100; ?></div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label>Currency</label>
                            </div>
                            <div class="col-md-8"><?php echo $plan->currency; ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label>Interval Count</label>
                            </div>
                            <div class="col-md-8"><?php echo $plan->interval_count; ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label>Interval</label>
                            </div>
                            <div class="col-md-8"><?php echo $plan->interval; ?></div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label>Card Type</label>
                            </div>
                            <div class="col-md-8">
                                <?php echo $card->brand; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label>Card last Number(4)</label>
                            </div>
                            <div class="col-md-8">
                                <?php echo $card->last4; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label>Customer Email</label>
                            </div>
                            <div class="col-md-8">
                                <?php echo $customer->email; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label>Next Billing Date</label>
                            </div>
                            <div class="col-md-8">
                                <?php echo date('Y-m-d', $sub->trial_end); ?>
                            </div>
                        </div>


                    </div>
                    <div class="mt50">
                        <button class="btn pri_button" id="cancel_sub_bt">Cancel Subscription</button>
                        <button class="btn pri_button hidden" id="update_sub_bt">Update Subscription</button>
                    </div>

                    <div class="mt50">
                        <div id="err_msg" class="text-warning"></div>
                    </div>

                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script>
    $('#cancel_sub_bt').click(function () {

        $.confirm({
            title: 'Cancel Subscription',
            content: 'Are you sure?',
            buttons: {
                Continue: {
                    text: "Continue",
                    btnClass: "btn-primary",
                    action: function () {

                        var data = "action=cancel_subscription";
                        $.ajax({
                            url: 'manage_subscription.php',
                            method: "post",
                            data: data,
                            success: function (response) {

                                if (response == "success") {

                                    location.reload();

                                } else {
                                    $('#err_msg').text(response).fadeIn(1000).delay(2000).fadeOut(2000);
                                }
                            },
                            error: function () {
                                var err_msg = "Problem Occurred. Please try again later.";
                                $('#err_msg').text(response).fadeIn(1000).delay(2000).fadeOut(2000);
                            }
                        });
                    }
                },
                Cancel: {
                    text: "Cancel",
                    btnClass: "btn-dark",
                    action: function () {
                        window.location = "search.php";
                    }
                }
            }
        });
    });

    $('#feedback_bt').click(function () {
        var feed_text = $("#feedback_area").val();
        if (!feed_text) {
            $('#feedback_area').focus();
            return;
        }

        var data = new FormData();
        data.append('action', 'send_feedback');
        data.append('feedback', feed_text);

        $.ajax({
            url: 'manage_subscription.php',
            type: "POST",
            processData: false,
            contentType: false,
            data: data,
            success: function (response) {
                if (!response) {
                    var err_msg = "Thanks for your feedback.";
                    $('#err_msg').text(response).fadeIn(1000).delay(2000).fadeOut(2000);
                } else {
                    $('#err_msg').text(response).fadeIn(1000).delay(2000).fadeOut(2000);
                }
            },
            error: function () {
                var err_msg = "Problem Occurred. Please try again later.";
                $('#err_msg').text(err_msg).fadeIn(1000).delay(2000).fadeOut(2000);
            }
        })

    })
</script>

<?php
include('templates/default/footer.php');
?>
