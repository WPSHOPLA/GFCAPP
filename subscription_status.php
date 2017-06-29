<?php
require('lib/init.php');
include('lib/classes/subscriptionClass.php');

//ikf the user is not subscribed, go to create subscription page
check_stripe_sub_status();

global $userDetails;

$sub_id = $userDetails->stripe_sub_id;
$sub_c = new subscriptionClass();

$sub = $sub_c->get_subscription_instance($sub_id);

$plan = $sub->plan;

$customer_id = $sub->customer;

$customer = \Stripe\Customer::retrieve($customer_id);
$card = $customer->sources->data[0];

$sub_status = $sub->status;

include('templates/default/header.php');
?>

<div class="container-fluid content">
    <div class="main-container">

        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="h1 highlight">Subscription Status</div>

                <h4 class="mt50">Status: <?php echo $sub_status; ?></h4>

                <?php if ($sub_status == "canceled") {
                    ?>

                    <?php if (isset($_SESSION['first_cancel_sub']) && $_SESSION['first_cancel_sub'] == true) {
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
                                <a href="create_stripe_subscription.php" class="btn pri_button">Reactivate
                                    Subscription</a>
                            </div>
                        </div>
                        <div class="mt50">
                            <div id="err_msg" class="text-warning"></div>
                        </div>
                    <?php } else { ?>

                        <div class="col-md-4">
                            <a href="create_stripe_subscription.php" class="btn pri_button">Reactivate
                                Subscription</a>
                        </div>

                    <?php }

                } else { ?>

                    <div class="sub_info mt50">

                        <div class="row">
                            <div class="col-md-4">
                                <label>Subscription Type</label>
                            </div>
                            <div class="col-md-8">
                                <?php
                                if ($userDetails->extra_users > 1)
                                    echo "Team";
                                else
                                    echo "Individual";
                                ?>
                            </div>
                        </div>

                        <?php
                        if ($userDetails->extra_users > 1) {
                            ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Team Size</label>
                                </div>
                                <div class="col-md-8">
                                    <?php echo $userDetails->extra_users; ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                        <div class="row">
                            <div class="col-md-4">
                                <label>Billing Period</label>
                            </div>
                            <div class="col-md-8">
                                <?php
                                if ($plan->interval == "month" && $plan->interval_count == "1") {
                                    echo "Monthly";
                                } else {
                                    //day, week, month, year
                                    echo 'Per ' . $plan->interval_count . ' ' . $plan->interval;
                                }
                                ?>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-4">
                                <?php if ($sub->status == "trialing") { ?>
                                    <label>Trial Period Starts On</label>
                                <?php } else { ?>
                                    <label>Trial Period Started On</label>
                                <?php } ?>
                            </div>
                            <div class="col-md-8">
                                <?php echo date('M d, Y', $sub->trial_start); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <?php if ($sub->status == "trialing") { ?>
                                    <label>Trial Period Ends On</label>
                                <?php } else { ?>
                                    <label>Trial Period Ended On</label>
                                <?php } ?>
                            </div>
                            <div class="col-md-8">

                                <?php echo date('M d, Y', $sub->trial_end); ?>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-4">
                                <label>Billing Starts On</label>
                            </div>
                            <div class="col-md-8">
                                <?php echo date('M d, Y', $sub->current_period_start); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label>Monthly Billing Amount</label>
                            </div>
                            <div
                                class="col-md-8 text-uppercase"><?php echo $plan->currency . ' ' . $plan->amount / 100; ?></div>
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
                                <label>Card Number Ends With (last 4 digits)</label>
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
                                <?php echo date('M d, Y', $sub->trial_end); ?>
                            </div>
                        </div>


                    </div>
                    <div class="mt50">
                        <button class="btn pri_button" id="cancel_sub_bt">Cancel Subscription</button>
                        <button class="btn pri_button" id="update_sub_bt">Update Subscription</button>
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
            content: 'Are you sure you want to cancel the subscription?',
            buttons: {
                Continue: {
                    text: "Yes",
                    btnClass: "btn-primary pri-button",
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
                    text: "No",
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
