// BASE FUNCTIONS
// All time the page is refreshed this function is used. Verify HTTPS and Cookies
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

// Verify if cookie can be used
function useCookie() {
    var cookieEnabled = navigator.cookieEnabled;
    if (!cookieEnabled){
        document.cookie = "testcookie";
        cookieEnabled = document.cookie.indexOf("testcookie")!=-1;
    }
    return cookieEnabled || showCookieFail();
}

// Passing to ERROR PAGE when cookie are not usable
function showCookieFail(){
    window.location.href = 'error.html';
}

// Get the cookie with the name provided as argument
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

// Update the expire date to the next TIMEOUT minute
function updateCookie() {
    var cookieUser = getCookie("polixbus_user");
    var cookieHash = getCookie("polixbus_hash");

    if (cookieUser !== "" && cookieHash !== "") {

        $.post('updateTimestamp.php', { field1: cookieUser, field2 : cookieHash},
            function(returnedData){
                console.log(returnedData);
                if(JSON.parse(returnedData).t === 0) {
                    logout();
                    showReservation();
                    alert(JSON.parse(returnedData).d);
                }
            });

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

// Verify that cookie is correctly assembled with the password hashed
function verifyCookie(user, hash) {
    $.post('checkUser.php', { field1: user, field2 : hash},
        function(returnedData){
            if(JSON.parse(returnedData).t === 1) {
                document.getElementById("logged").innerHTML = JSON.parse(returnedData).d;
                showLogged();
                showReservation();
            } else {
                alert(JSON.parse(returnedData).d);
                logout();
                showReservation();
            }
        });
}

// USER MANAGE FUNCTIONS
// Signup new user, password/user validation is performed 3 times (HTML5, JS and PHP)
function signup() {
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
    } else {
        document.getElementById("signup_pass").value = "";
        document.getElementById("signup_user").value = "";
        document.getElementById("signup_pass-repeat").value = "";
        alert("Password and/or mail not correct!");
    }
}

// Login user and cookies save from server-side
function login() {
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

// Logout, set expire time to
function logout() {
    document.cookie = "polixbus_user=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    document.cookie = "polixbus_hash=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    location.reload();
}

// Password validation JS side
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
            repeatpass.style.border = "1px solid red"
            returnValue = 0;
        }

    } else {
        pass.style.border = "1px solid red";
        returnValue = 0;
    }

    return returnValue;
}

// Mail validation JS side
function validateUser() {
    var user = document.getElementById("signup_user");

    // REGEX from W3SCHOOL
    const regexMail = /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;

    var returnValue = -1;

    if(regexMail.test(user.value.toLowerCase())) {
        user.style.border = "";
        returnValue = 1;
    } else {
        returnValue = 0;
    }

    return returnValue;
}

// RESERVATION MANAGING FUNCTIONS
// Making a new reservation, used on button click
function makeReservation() {

    if ( getCookie("polixbus_user") === "" || getCookie("polixbus_hash") === "" )  {
        alert("Session expired!");
        location.reload();
    } else {
        updateCookie();
        document.getElementById("reservation-table").style.visibility = 'visible';

        var userLogged = getCookie("polixbus_user");
        var start = document.getElementById("start").value.toLowerCase();
        var end = document.getElementById("end").value.toLowerCase();
        var num = document.getElementById("number").value;

        const regexAlNum = /^[a-z0-9]+$/i;

        // Verify that all field are not empty
        if (userLogged !== "" && start !== "" && end !== "" && num !== "") {
            if (start < end) {
                if (num > 0) {
                    if (regexAlNum.test(start) && regexAlNum.test(end)) {
                        if (start.length < 254 || end.length < 254) {
                            $.post('makeReservation.php', { field1: userLogged, field2: start, field3: end, field4: num},
                                function(returnedData){
                                    console.log(returnedData);

                                    if (JSON.parse(returnedData).t === 1) {
                                        showReservation();
                                    } else {
                                        alert(JSON.parse(returnedData).d);
                                    }

                                });
                        } else {
                            showReservation();
                            alert("Stop and/or start is longer than 254 chars!");
                        }
                    } else {
                        showReservation();
                        alert("Only letters or number are usable in start and stop!");
                    }
                } else {
                    showReservation();
                    alert("Number of passengers must be greater or equal to 1!");
                }
            } else {
                showReservation();
                alert("STARTING place must precede ENDING!");
            }
        } else {
            showReservation();
            alert("Check reservation fields!");
        }
    }
}

// Show reservations, different layout in case of logged or not
function showReservation() {

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
            } else if (JSON.parse(returnedData).t === -2) {
                document.getElementById("signup_form").style.visibility = 'collapse';
                document.getElementById("reservation-table").innerHTML = JSON.parse(returnedData).d;
            } else {
                console.log(JSON.parse(returnedData).d);
                alert(JSON.parse(returnedData).d);
            }
        });

    if (userLogged !== "") {
        showReservationStuff(1);
    } else {
        showReservationStuff(0);
    }

}

// Delete reservation, verification of user possibility to make reservation or not is server-side performed
function deleteReservation() {

    if ( getCookie("polixbus_user") === "" || getCookie("polixbus_hash") === "" )  {
        alert("Session expired!");
        location.reload();
    } else {
        updateCookie();
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
}

// LAYOUT FUNCTIONS
// Showing login stuffs
function showLogin() {
    document.getElementById("login").style.visibility = 'visible';
    document.getElementById("logged").style.visibility = 'collapse';
}

// Showing logged stuffs
function showLogged() {
    document.getElementById("logged").style.visibility = 'visible';
    document.getElementById("login").style.visibility = 'collapse';
    document.getElementById("signup_option").style.visibility = 'collapse';
}

// Showing signup stuffs
function showSignup() {
    document.getElementById("login").style.visibility = 'collapse';
    document.getElementById("logged").style.visibility = 'collapse';
    document.getElementById("reservation-table").style.visibility = 'collapse';
    document.getElementById("signup_option").style.visibility = 'visible';
    document.getElementById("signup_form").style.visibility = 'visible';
}

// Show/hide reservation stuffs
function showReservationStuff(type) {

    if (type === 1) {
        document.getElementById("delete-reser").style.visibility = 'visible';
        document.getElementById("make-reser").style.visibility = 'visible';
        document.getElementById("reservation-request").style.visibility = 'visible';
    } else {
        document.getElementById("delete-reser").style.visibility = 'collapse';
        document.getElementById("make-reser").style.visibility = 'collapse';
        document.getElementById("reservation-request").style.visibility = 'collapse';
    }

}