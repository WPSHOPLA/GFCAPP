<?php
require('lib/init.php');

if (isLoggedIn()) {
    $url = BASE_URL . 'index.php';
    header("Location: $url"); // Page redirecting to home.php
    exit();
}

$reset_code = isset($_GET['reset_code']) ? $_GET['reset_code'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';
$user_id = $userClass->getUserIdFromResetLink($reset_code, $email);

include('templates/default/header.php');
?>
    <div class="container-fluid content">
        <div class="main-container">
            <div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
                <div class="login-form">

                    <?php
                    if ($user_id === 'INVALID_RESET_LINK') {
                        echo '<div class="search-examples"><center><p><b>The password reset link is invalid or expired.</b></p></center></div>';
                    } else {
                        ?>

                        <div class="h1 text-blue">Password Reset</div>

                        <form name="form" method="post" enctype="multipart/form-data">
                            <input name="user_id" type="hidden" value="<?php echo $user_id;?>" id="user_id">
                            <div class="form-group">
                                <input type="password" name="password" class="form-control" placeholder="Password" id="p1"
                                       required>
                            </div>

                            <div class="form-group">
                                <input type="password" name="password_2" class="form-control" id="p2"
                                       placeholder="Confirm Password" required>
                            </div>

                            <div class="form-actions form-group ">
                                <input type="submit" class="pri_button" value="Reset" id="reset_submit">
                            </div>
                        </form>

                        <div id="err_msg" class="text-error response_text"></div>
                        <div id="suc_msg" class="text-success response_text"></div>

                    <?php } ?>

                </div>
            </div>
        </div>
    </div>

    <script>

        //reset password request submit
        $('#reset_submit').click(function (e) {

            e.preventDefault();

            var pw1 = $("#p1").val();
            var pw2 = $("#p2").val();
            if(pw1 != pw2){
                var err_msg = "Password Does not Match."
                $('#err_msg').text(err_msg).fadeIn(1000).delay(2000).fadeOut(2000);
                return false;
            }

            var user_id = $('#user_id').val();

            var data = new FormData();
            data.append('action', 'reset_password');
            data.append('user_id', user_id);
            data.append('password', pw1);

            $.ajax({
                url: 'manage_user.php',
                type: "POST",
                data: data,
                processData: false,
                contentType: false,
                success: function (responseText) {

                    var response = JSON.parse(responseText);
                    if (response.result == "success") {
                        $('#suc_msg').text(response.message).fadeIn(1000).delay(2000).fadeOut(2000, function(){
                            window.location = "login.php";
                        });

                    } else {
                        $('#err_msg').text(response.message).fadeIn(1000).delay(2000).fadeOut(2000);
                    }
                },
                error: function () {
                    var err_msg = "Problem Occurred. Please try again later.";
                    $('#err_msg').text(err_msg).fadeIn(1000).delay(2000).fadeOut(2000);
                }
            })

        })
    </script>

<?php include('templates/default/footer.php'); ?>