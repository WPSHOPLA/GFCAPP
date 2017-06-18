<!DOCTYPE html>
<html lang="en" style="overflow-y: scroll;">
<head>
    <title>GoFetchCode</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description">
    <meta name="author">
    <meta name="robots" content="noindex,nofollow">
    <link rel="shortcut icon" href="/favicon.ico">

    <link rel="stylesheet" type="text/css"
          href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600&amp;subset=cyrillic,latin">
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

    <link rel="stylesheet" href="templates/default/css/main_new.css">
    <link rel="stylesheet" href="templates/default/css/font-awesome.min.css">
    <link rel="stylesheet" href="templates/default/css/awesomplete.css"/>
    <link rel="stylesheet" href="templates/default/css/daterangepicker.css"/>
    <link rel="stylesheet" href="templates/default/css/jquery-confirm.min.css"/>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>

    <script src="templates/default/js/awesomplete.js"></script>
    <script src="templates/default/js/jquery-confirm.min.js"></script>

    <script>
        function toggleNavigation() {
            if (jQuery('.nav-bar').hasClass('nav-open')) {
                jQuery('.nav-bar').removeClass('nav-open');
            } else {
                jQuery('.nav-bar').addClass('nav-open');
                //<div class="overlay overlay--navigation ng-scope" ng-if="isNavOpen" ng-click="toggleNavigation()"></div>
            }
        }

        function pingSession() {
            var request = new XMLHttpRequest();
            request.open('GET', 'api/userlog.php?action=session_ping', true);
            request.send();
        }

        $(function () {
            // We can attach the `fileselect` event to all file inputs on the page
            $(document).on('change', ':file', function () {
                var input = $(this),
                    numFiles = input.get(0).files ? input.get(0).files.length : 1,
                    label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
                input.trigger('fileselect', [numFiles, label]);
            });

            // We can watch for our custom `fileselect` event like this
            $(document).ready(function () {
                $(':file').on('fileselect', function (event, numFiles, label) {

                    var input = $(this).parents('.input-group').find(':text'),
                        log = numFiles > 1 ? numFiles + ' files selected' : label;

                    if (input.length) {
                        input.val(log);
                    } else {
                        if (log) alert(log);
                    }

                });
            });
            $('body').bind('copy cut paste', function (e) {
                if (e.target.name != 'keyword') {
                    console.log('dont copy man');
                    e.preventDefault();
                }
            });

            pingSession();
            setInterval(pingSession, 10000);
        });
    </script>
</head>

<body class="header-fixed">
<div class="wrapper">
    <div class="ng-scope">
        <header class="header">
            <div class="clearfix">
                <a class="col-xs-4 col-sm-2" href="<?php echo 'http://www.gofetchcode.com'; ?>">
                    <img class="logo" src="assets/img/GoFetchCode_Logo_53.png" alt="Logo">
                </a>


                <?php if (basename($_SERVER["SCRIPT_FILENAME"]) == 'search.php') { ?>

                    <div class="col-sm-8 hidden-xs">
                        <form name="search" method="get" class="nested-search">

                            <div class="col-sm-2 hidden-xs">
                                <div class="comb-search input-group">
                                    <select name="state">
                                        <!--<option value="0">Any State</option>-->
                                        <?php
                                        $count_state = [];

                                        $url = 'http://localhost:8983/solr/gfc/select?defType=edismax&indent=on';
                                        $url .= '&fq=entity_type:3';
                                        $url .= '&q=*';
                                        $url .= '&rows=500&wt=json';

                                        $obj = json_decode(file_get_contents($url), true);
                                        foreach ($obj['response']['docs'] as $result) {
                                            $count_state[$result['state_id']] = $result['count'];
                                        }


                                        //foreach ($questionClass->getStates() as $state) {
                                        $states = $mainClass->getStates();
                                        $states_search = isStaff() ? $states : $userClass->getSubscriptionLocations($session_uid);

                                        //print_r($userClass->getSubscriptionLocations($session_uid));
                                        //print_r($states);

                                        foreach ($states_search as $state) {
                                            if (!isStaff())
                                                $state = $states[$state->state_id];
                                            $selected = isset($_COOKIE['state_id']) && $_COOKIE['state_id'] == $state['id'] ? ' selected' : '';
                                            $question_count = isset($count_state[$state['id']]) ? $count_state[$state['id']] : 0;
                                            //echo '<option value="' . $state['id'] . '"' . $selected . '>' . $state['name'] . ' (' . $question_count . ')</option>';
                                            echo '<option value="' . $state['id'] . '"' . $selected . '>' . $state['name'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2 hidden-xs">
                                <div class="comb-search input-group">
                                    <select name="year">
                                        <option value="2016">2016</option>
                                        <option value="2015">2015</option>
                                        <option value="2014">2014</option>
                                        <option value="2013">2013</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-8 hidden-xs">
                                <div class="comb-search input-group">
                                    <input name="keyword" class="awesomplete" type="text" id="ajax" list="json-datalist"
                                           value="<?php echo htmlspecialchars($filter_keyword, ENT_QUOTES, 'UTF-8'); ?>"
                                           placeholder="Enter your keyword">

                                    <!--<datalist id="json-datalist"></datalist>-->
                                    <span class="search-button input-group-btn">
										<button class="submit-comb-search pri-button" value="submit" type="submit"
                                                name="question_search">&nbsp;&nbsp;&nbsp;&nbsp;Search</button>
									</span>
                                </div>
                            </div>


                            <script>
                                $(function () {
                                    var input = document.getElementById('ajax');
                                    var request = new XMLHttpRequest();
                                    var awesomplete = new Awesomplete(input, {minChars: 5});
                                    $("#ajax").on('input', function () {
                                        if (input.value.length < 5)
                                            return;
                                        awesomplete.list = [];
                                        request.abort();
                                        request.open('GET', 'api/autocomplete.php?question=' + input.value + "&state_id=" + $("[name=state]").val(), true);
                                        request.send();
                                    });

                                    request.onreadystatechange = function (response) {
                                        if (request.readyState === 4) {
                                            if (request.status === 200) {
                                                var jsonOptions = JSON.parse(request.responseText);
                                                var list = [];
                                                jsonOptions.forEach(function (item) {
                                                    list.push(item.question);
                                                });
                                                awesomplete.list = list;
                                            }
                                        }
                                    };
                                });
                            </script>


                        </form>
                    </div>


                <?php } ?>


                <?php if (!isLoggedIn()) { ?>
                    <div class="col-xs-8 col-sm-10">
                        <div class="row user-navigation ng-scope">
                            <a href="<?php echo 'http://www.gofetchcode.com' ?>"
                               class="btn btn-transparent btn-rounded">Home</a>
                            <a href="<?php echo BASE_URL . 'login.php'; ?>" class="btn btn-transparent btn-rounded">Login</a>
                            <a href="<?php echo BASE_URL . 'register.php'; ?>" class="btn btn-transparent btn-rounded">Sign
                                Up</a>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="col-xs-12 col-sm-2 pull-right user-navigation">
                        <span class="username ng-binding">Hi <?php echo $userDetails->first_name; ?></span>

                        <button onClick="toggleNavigation()">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="fa fa-bars"></span>
                        </button>
                    </div>

                    <div class="nav-bar">
                        <div class="close-nav">
                            <a onClick="toggleNavigation()">X</a>
                        </div>

                        <ul class="list-unstyle">
                            <li>
                                <a onclick="$(this).next('ul').toggleClass('collapse');"><i
                                        class="fa fa-question-circle" aria-hidden="true"></i>&nbsp;&nbsp;Questions<span
                                        class="caret"></span></a>
                                <ul class="list-unstyle cbc-chapters-list collapse">
                                    <li class="ng-scope"><a href="<?php echo BASE_URL . 'search.php'; ?>">Search</a>
                                    </li>
                                    <li class="ng-scope"><a
                                            href="<?php echo BASE_URL . 'search.php?bookmarks=true'; ?>">Bookmarks</a>
                                    </li>
                                    <?php if (isStaff()) { ?>
                                        <li class="ng-scope"><a href="<?php echo BASE_URL . 'question_list.php'; ?>">Frequent
                                                Questions</a></li>
                                        <li class="ng-scope"><a href="<?php echo BASE_URL . 'question_add.php'; ?>">Create
                                                a Question</a></li>
                                        <li class="ng-scope"><a
                                                href="<?php echo BASE_URL . 'question_add_bulk.php'; ?>">Upload
                                                Questions CSV</a></li>
                                    <?php } ?>
                                </ul>
                                <?php if (isStaff()) { ?>
                                    <a onclick="$(this).next('ul').toggleClass('collapse');"><i class="fa fa-file"
                                                                                                aria-hidden="true"></i>&nbsp;&nbsp;Documents<span
                                            class="caret"></span></a>
                                    <ul class="list-unstyle cbc-chapters-list collapse">
                                        <li class="ng-scope"><a href="<?php echo BASE_URL . 'document_list.php'; ?>">View
                                                Documents</a></li>
                                        <li class="ng-scope"><a href="<?php echo BASE_URL . 'document_add.php'; ?>">Upload
                                                Document</a></li>
                                    </ul>
                                <?php } ?>
                                <?php if (isStaff()) { ?>
                                    <a onclick="$(this).next('ul').toggleClass('collapse');"><i
                                            class="fa fa-info-circle" aria-hidden="true"></i>&nbsp;&nbsp;Logs<span
                                            class="caret"></span></a>
                                    <ul class="list-unstyle cbc-chapters-list collapse">
                                        <li class="ng-scope"><a href="<?php echo BASE_URL . 'log_search.php'; ?>">Searches</a>
                                        </li>
                                        <li class="ng-scope"><a href="<?php echo BASE_URL . 'log_user.php'; ?>">User
                                                Log</a></li>
                                    </ul>
                                <?php } ?>
                                <a onclick="$(this).next('ul').toggleClass('collapse');"><i class="fa fa-user"
                                                                                            aria-hidden="true"></i>&nbsp;&nbsp;Account<span
                                        class="caret"></span></a>
                                <ul class="list-unstyle cbc-chapters-list collapse">
                                    <li class="ng-scope"><a href="<?php echo BASE_URL . 'account_settings.php'; ?>">Account
                                            Settings</a></li>
                                    <li class="ng-scope"><a href="<?php echo BASE_URL . 'subscription_status.php'; ?>">Subscription</a>
                                    <li class="ng-scope"><a href="<?php echo BASE_URL . 'account_team.php'; ?>">Team</a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL . 'logout.php'; ?>"><i class="fa fa-sign-out"
                                                                                    aria-hidden="true"></i>&nbsp;&nbsp;Logout</a>
                            </li>
                        </ul>
                    </div>
                <?php } ?>
            </div>
        </header>

    </div>
		