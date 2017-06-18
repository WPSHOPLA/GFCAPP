//Validate Email from form submit
function validateEmail(email) {
    var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

    if (email.match(mailformat)) {
        return true;
    }
    else {
        return false;
    }
}


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
    // $('body').bind('copy cut paste', function (e) {
    //     if (e.target.name != 'keyword') {
    //         console.log('dont copy man');
    //         e.preventDefault();
    //     }
    // });

    pingSession();
    setInterval(pingSession, 10000);
});

