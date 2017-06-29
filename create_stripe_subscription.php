<?php
require('lib/init.php');
include('templates/default/header.php');
include('lib/classes/subscriptionClass.php');

global $mainClass;
$states = $mainClass->getStates();

//get country list
$sub_c = new subscriptionClass();
$country_list = $sub_c->get_country_list();
?>

<div class="container-fluid content">
    <div class="main-container">

        <div class="row">

            <div class=" col-md-6 ">
                <div class="sub_col">

                    <form class="sub_form" action="manage_subscription.php" method="post" id="sub_form">

                        <div class="control">
                            Please check all the state building codes you would like to subscribe to
                        </div>

                        <div class="row" style="padding: 10px;">
                            <?php
                            foreach ($states as $state) {
                                ?>
                                <div class="col-xs-4 control">
                                    <div class="squaredOne">
                                        <input class="location_check" data-location="<?php echo $state['name'] ?>"
                                               name="locations[]"
                                               type="checkbox" data-one="<?php echo $state['one_price'] ?>"
                                               data-team="<?php echo $state['team_price'] ?>"
                                               id="location_<?php echo $state['id'] ?>"
                                               value="<?php echo $state['id'] ?>"/>
                                        <label
                                            for="location_<?php echo $state['id'] ?>"><?php echo $state['name'] ?></label>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>

                        <div class="control">
                            What is your team size?
                        </div>
                        <p class="description">Enter number between 1 to 100. You will be charged per seat.</p>

                        <div class="control">
                            <input name="extra_users" class="form-control" placeholder="1" type="number" min="1" max="100"
                                   id="user_count" value="1" required/>
                        </div>

                        <div class="h1 text-blue" style="margin-top: 50px;">Payment Information</div>

                        <div class="control">
                            <img src="assets/img/visa_master_logo.png">
                        </div>

                        <div class="control">
                            <div id="card-element" class="form-control"></div>
                        </div>

                        <div class="control">
                            <input name="cardholder-name" class="form-control" placeholder="Name on Card"
                                   pattern="[a-zA-Z][\.]?[a-zA-Z\s]{1,20}" required/>
                        </div>

                        <div class="control">
                            <input name="phone-number" class="form-control" placeholder="Phone Number" required
                                   pattern="\(?\d{3}\)?\s?[\-]?\d{3}[\-]?\d{4}"
                                   title="(ddd) ddd-dddd or ddd-ddd-dddd" type="tel"/>
                        </div>

                        <div class="control">
                            <input name="address-zip" class="form-control" placeholder="ZIP or Postal Code"
                                   pattern="\d{5}([\-]\d{4})?"
                                   title="xxxxx or xxxxx-xxx" required/>
                        </div>

                        <div class="control">
                            <label for="country" class="hidden"></label>
                            <select name="address-country" class="form-control" id="country" required>
                                <option value="US">United States</option>
                                <?php foreach ($country_list as $country_id => $country_name) { ?>
                                    <option value="<?php echo $country_id; ?>"><?php echo $country_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <button type="submit" class="pri_button">Submit</button>

                        <input type="hidden" name="total_amount" id="total_amount">
                        <input type="hidden" name="action" value="create_subscription">

                        <div class="control hidden">
                            <div class="input-group">
                                <input type="text" class="field" placeholder="Discount Code" disabled>
                                <span class="input-group-btn">
                                    <button class="btn btn-secondary redeem" type="button">Redeem</button>
                                </span>
                            </div>
                        </div>

                        <div class="outcome">
                            <div class="error" role="alert"></div>
                            <div class="success">
                                Success! Your Stripe token is <span class="token"></span>
                                <input type="hidden" name="token" id="token">
                            </div>
                        </div>

                    </form>

                </div>
            </div>

            <div class="col-md-6">

                <div class="sub_col summary sub_form" id="summary">

                    <div class="h1 text-blue">
                        Payment Summary
                    </div>

                    <p class="team_size">Team Size: <span id="team_size">10</span></p>
                    <table id="summary_tb">
                        <thead>
                        <tr>
                            <th width="50%">Search Code locations</th>
                            <th width="20%">Price</th>
                            <th width="30%">Sub Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                    <p class="price">Total <span id="total_price_value">250</span> USD</p>

                    <label style="margin-top: 20px;">Your subscription will be billed monthly.</label>
                    <p class="description">***No charge will be processed on your card during the free trial.
                        You may choose to cancel anytime***
                    </p>

                    <div class="benefit">
                        <label>Premium Benefits</label>
                        <ul>
                            <li>Access to the most sophisticated Building Code search engine available.</li>
                            <li>Unlimited search queries per user. Whatever the question you have, just ask
                                GoFetchCode.
                            </li>
                            <li>Detailed answers to your questions. Our software brings you to exactly the right
                                paragraph
                                in the Code you subscribe to.
                            </li>
                            <li>
                                Less time and energy spent on looking through endless pages of Code with our efficiency
                                tools.
                                Save your searches, bookmark specific articles, or share and rate the answer provided.
                            </li>
                        </ul>
                    </div>


                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>

    var stripe = Stripe('pk_test_cMjfccB7ZiyVocHxfJKUkQ0u');
    var elements = stripe.elements();

    var card = elements.create('card', {
        hidePostalCode: true,
        style: {
            base: {
                iconColor: '#F99A52',
                color: '#32315E',
                lineHeight: '48px',
                fontWeight: 400,
                fontFamily: '"Helvetica Neue", "Helvetica", sans-serif',
                fontSize: '15px',

                '::placeholder': {
                    color: '#CFD7DF',
                }
            },
        }
    });
    card.mount('#card-element');

    function setOutcome(result) {
        var successElement = document.querySelector('.success');
        var errorElement = document.querySelector('.error');
        successElement.classList.remove('visible');
        errorElement.classList.remove('visible');

        if (result.token) {
            // Use the token to create a charge or a customer
            // https://stripe.com/docs/charges
            successElement.querySelector('.token').textContent = result.token.id;
            successElement.querySelector('#token').value = result.token.id;
            //successElement.classList.add('visible');

            document.getElementById("sub_form").submit();

        } else if (result.error) {
            errorElement.textContent = result.error.message;
            errorElement.classList.add('visible');
        }
    }

    card.on('change', function (event) {
        setOutcome(event);
    });

    document.querySelector('form').addEventListener('submit', function (e) {
        e.preventDefault();
        //count location check
        var location_count = document.querySelectorAll('.location_check:checked').length;
        if (location_count == 0) {
            alert("Please select one location at least.");
            return false;
        }

        var form = document.querySelector('form');
        var extraDetails = {
            name: form.querySelector('input[name=cardholder-name]').value,
            address_zip: form.querySelector('input[name=address-zip]').value,
            address_country: form.querySelector('[name=address-country]').value
        };
        stripe.createToken(card, extraDetails).then(setOutcome);
    });

</script>

<script>

    function fix_user_count() {
        var user_count = $('#user_count').val();
        if (user_count < 1) {
            $('#user_count').val(1);
        } else if (user_count > 100) {
            $('#user_count').val(100);
        } else {
            return;
        }
    }

    var total_price = 0;

    function calculate_price() {

        total_price = 0;

        var check_count = $('.location_check:checked').length;
        if (!check_count) {
            $('#summary').hide();
        } else {
            $('#summary').show();
        }

        $('#summary_tb tbody tr').remove();

        //get user count
        var user_count = $('#user_count').val();

        $('.location_check:checked').each(function () {

            var one = $(this).data('one');
            var team = $(this).data('team');

            var location_name = $(this).data('location');
            var price = 0;

            var sub_total = 0;

            if (user_count > 1) {
                sub_total = team * user_count;
                price = team;

            } else {
                sub_total = one * user_count;
                price = one;
            }

            total_price += sub_total;

            $('#summary_tb').append('<tr><td>' + location_name + '</td><td>' + price + ' USD</td><td>' + price + '*' + user_count + ' USD</td></tr>');

        });

        //update total price
        $('#total_price_value').text(total_price);
        $('#total_amount').val(total_price);

        //update team size
        $('#team_size').text(user_count);
    }

    $('#user_count').change(function () {
        fix_user_count();
        calculate_price();
    });

    $('.location_check').change(function () {
        calculate_price();
    });

    $(document).ready(function () {
        calculate_price();
    });

</script>

<?php
include('templates/default/footer.php');
?>
