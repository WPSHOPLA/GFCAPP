<?php
require('lib/init.php');
include('templates/default/header.php');

$failed = $_SESSION['fail_sub_count'];

$fail_nth_str = "1st";

if ($failed == 1) {
    $fail_nth_str = "1st";
} else if ($failed == 2) {
    $fail_nth_str = "2nd";
} else if ($failed == 3) {
    $fail_nth_str = "3rd";
} else {
    $fail_nth_str = "too_many";
}

?>

<div class="container-fluid content">
    <div class="main-container">
        <div class="row">

            <div class="col-md-6 col-md-offset-3">

                <div class="sub_result failed_sub">
                    <h1>Something went wrong</h1>
                    <?php if ($fail_nth_str != "too_many") { ?>
                        <p>It was your <?php echo $fail_nth_str ?> try.</p>
                    <?php } ?>
                    <?php if ($failed > 3) { ?>
                        <p>It seems the problem hasn’t been solved. We’re very sorry – please contact our customer
                            service
                            team and they’ll get you out of this pickle as soon as possible</p>
                    <?php } ?>
                    <p>We’re very sorry, but something must have gone wrong. At this moment we haven’t received your
                        payment and your credit card has not been charged.</p>
                    <p>We know it’s annoying, but please try again.</p>

                    <a class="btn btn-primary" href="create_stripe_subscription.php">Try Again</a>
                </div>

            </div>

        </div>
    </div>
</div>

<?php
include('templates/default/footer.php');
?>
