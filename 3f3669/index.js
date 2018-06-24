function checkCookie() {
    // ENFORCE HTTPS
    if (location.protocol != 'https:') {
        location.href = 'https:' + window.location.href.substring(window.location.protocol.length);
    }

    useCookie();

    var cookieUser = getCookie("polixbus_user");
    var cookieHash = getCookie("polixbus_hash");

    if (cookieUser !== "" && cookieHash !== "") {
        verifyCookie(cookieUser, cookieHash);
    } else {
        showLogin();
    }
}

function useCookie() {
    var cookieEnabled = navigator.cookieEnabled;
    if (!cookieEnabled){
        document.cookie = "testcookie";
        cookieEnabled = document.cookie.indexOf("testcookie")!=-1;
    }
    return cookieEnabled || showCookieFail();
}

function showCookieFail(){
    window.location.href = 'error.html';
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function updateCookie() {
    var cookieUser = getCookie("polixbus_user");
    var cookieHash = getCookie("polixbus_hash");

    if (cookieUser !== "" && cookieHash !== "") {
        var TIMEOUT = 2;
        var d = new Date();

        d.setTime(d.getTime() + (TIMEOUT*60*1000));
        var expires = "expires="+ d.toUTCString();

        document.cookie = "polixbus_user=" + cookieUser + ";" + expires + ";path=/";
        document.cookie = "polixbus_hash=" + cookieHash + ";" + expires + ";path=/";
    } else {
        showLogin();
    }

}

function verifyCookie(user, hash) {
    $.post('checkUser.php', { field1: user, field2 : hash},
        function(returnedData){
            if(JSON.parse(returnedData).t === 1) {
                document.getElementById("logged").innerHTML = JSON.parse(returnedData).d;
                showLogged();
                showReservation();
            } else {
                logout();
                showReservation();
                alert(JSON.parse(returnedData).d);
            }
        });
}

function showLogin() {
    document.getElementById("login").style.visibility = 'visible';     // Show
    document.getElementById("logged").style.visibility = 'collapse';      // Hide
}

function showLogged() {
    document.getElementById("logged").style.visibility = 'visible';     // Show
    document.getElementById("login").style.visibility = 'collapse';      // Hide
}

function showSignup() {
    document.getElementById("login").style.visibility = 'collapse';      // Hide
    document.getElementById("logged").style.visibility = 'collapse';      // Hide
    document.getElementById("reservation-table").style.visibility = 'collapse';      // Hide

    document.getElementById("signup_form").style.visibility = 'visible';
}

function signup() {
    // @TODO: Logout before signup
    var rU = validateUser();
    var rP = validatePass();

    if(rU === 1 && rP === 1) {

        var pass = document.getElementById("signup_pass").value;
        var user = document.getElementById("signup_user").value.toLowerCase();

        $.post('signup.php', {field1: user, field2: pass},
            function (returnedData) {
                console.log(returnedData);
                if (JSON.parse(returnedData).t === 1) {
                    alert(JSON.parse(returnedData).d);
                    location.reload();
                } else {
                    alert(JSON.parse(returnedData).d);
                    location.reload();
                }
            });
    }
}

function login() {
    // @TODO: Hide signup after login
    // @TODO: Fix uncorrect positioning of table
    var user = document.getElementById("user").value.toLowerCase();
    var pass = document.getElementById("pass").value;

    $.post('login.php', { field1: user, field2 : pass},
        function(returnedData){
            if (JSON.parse(returnedData).t === 1) {
                location.reload();
            } else {
                alert(JSON.parse(returnedData).d);
                location.reload();
            }
        });
}

function logout() {
    document.cookie = "polixbus_user=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    document.cookie = "polixbus_hash=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    location.reload();
    // @TODO: Hide reservation div
}

function validatePass() {
    var pass = document.getElementById("signup_pass");
    var repeatpass = document.getElementById("signup_pass-repeat");
    var lowerCaseLetters = /[a-z]/g;
    var upperCaseLetters = /[A-Z]/g;
    var numbers = /[0-9]/g;

    var returnValue = -1;

    if(pass.value.match(lowerCaseLetters) && (pass.value.match(upperCaseLetters) || pass.value.match(numbers))) {
        if(pass.value === repeatpass.value) {
            pass.style.border = ""
            repeatpass.style.border = ""
            returnValue = 1;
        } else {
            repeatpass.style.border = "2px solid red"
            returnValue = 0;
        }

    } else {
        returnValue = 0;
    }

    return returnValue;
}

function validateUser() {
    var user = document.getElementById("signup_user");
    const regexMail = /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;

    var returnValue = -1;

    if(regexMail.test(user.value.toLowerCase())) {
        user.style.border = "";
        returnValue = 1;
    } else {
        // user.style.border = "2px solid red";
        returnValue = 0;
    }

    return returnValue;
}

function makeReservation() {
    //@TODO: Check if the user has already performed a reservation

    updateCookie();
    document.getElementById("reservation-table").innerHTML = "";
    document.getElementById("reservation-table").style.visibility = 'visible';

    var userLogged = getCookie("polixbus_user");
    var start = document.getElementById("start").value;
    var end = document.getElementById("end").value;
    var num = document.getElementById("number").value;

    if (!(userLogged === "" && start === "" && end === "" && num === "")) {
        if (start < end) {
            $.post('makeReservation.php', { field1: userLogged, field2: start, field3: end, field4: num},
                function(returnedData){
                    console.log(returnedData);

                    if (JSON.parse(returnedData).t === 1) {
                        showReservation();
                    } else {
                        showReservation();
                        alert(JSON.parse(returnedData).d);
                    }

                });
        } else {
            showReservation();
            alert("STARTING place must precede ENDING!");
        }
    }
}

function showReservation() {

    updateCookie();
    document.getElementById("reservation-table").innerHTML = "";
    document.getElementById("reservation-table").style.visibility = 'visible';

    var userLogged = getCookie("polixbus_user");

    $.post('getReservation.php', { field1: userLogged},
        function(returnedData){
            console.log(returnedData);
            if (JSON.parse(returnedData).t === 1) {
                document.getElementById("signup_form").style.visibility = 'collapse';
                document.getElementById("reservation-table").innerHTML = JSON.parse(returnedData).d;
                document.getElementById("startList").innerHTML = JSON.parse(returnedData).s;
                document.getElementById("endList").innerHTML = JSON.parse(returnedData).s;
                document.getElementById("reservation-table").style.visibility = 'visible';
            } else {
                console.log(JSON.parse(returnedData).d);
                alert(JSON.parse(returnedData).d);
            }
        });

    // @TODO: Shift hide/visual in a function
    if (userLogged != "") {
        document.getElementById("delete-reser").style.visibility = 'visible';
        document.getElementById("make-reser").style.visibility = 'visible';
        document.getElementById("reservation-request").style.visibility = 'visible';
    } else {
        document.getElementById("delete-reser").style.visibility = 'collapse';
        document.getElementById("make-reser").style.visibility = 'collapse';
        document.getElementById("reservation-request").style.visibility = 'collapse';
    }
}

function deleteReservation() {

    updateCookie();
    document.getElementById("reservation-table").innerHTML = "";
    document.getElementById("reservation-table").style.visibility = 'visible';

    var userLogged = getCookie("polixbus_user");

    $.post('deleteReservation.php', { field1: userLogged},
        function(returnedData){
            console.log(returnedData);
            if (JSON.parse(returnedData).t === 1) {
                showReservation();
            } else {
                showReservation();
                alert(JSON.parse(returnedData).d);
            }
        });
}