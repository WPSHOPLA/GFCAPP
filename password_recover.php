<?php
require('lib/init.php');

if (isLoggedIn()) {
    $url = BASE_URL . 'index.php';
    header("Location: $url"); // Page redirecting to home.php
    exit();
}

include('templates/default/header.php');
?>
    <div class="container-fluid content">
        <div class="main-container">
            <div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
                <div class="login-form">

                    <div class="h1 text-blue">Password Recovery</div>
                    <form method="post" enctype="multipart/form-data" id="recover_form">
                        <div class="form-group">
                            <input type="text" name="email" class="form-control" placeholder="Email" required id="email"
                                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$">
                        </div>

                        <div class="form-actions form-group ">
                            <input id="reset_request_submit" type="submit" name="reset_request_submit"
                                   class="pri_button full-width" value="Recover">
                        </div>
                    </form>

                    <div id="err_msg" class="text-error response_text"></div>
                    <div id="suc_msg" class="text-success response_text"></div>
                </div>
            </div>
        </div>
    </div>
    <script>

        //reset password request submit
        $('#reset_request_submit').click(function (e) {

            e.preventDefault();
            var email = $("#email").val();
            if (!validateEmail(email)) {
                var msg = "Please input valid email.";
                $('#err_msg').text(msg).fadeIn(1000).delay(2000).fadeOut(2000);
            }
            var data = new FormData();
            data.append('action', 'recover_password');
            data.append('email', email);

            $.ajax({
                url: 'manage_user.php',
                type: "POST",
                data: data,
                processData: false,
                contentType: false,
                success: function (responseText) {

                    var response = JSON.parse(responseText);
                    if (response.result == "success") {
                        $('#suc_msg').text(response.message).fadeIn(1000).delay(2000).fadeOut(2000);
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