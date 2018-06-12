function checkCookie() {
    var cookieUser = getCookie("polixbus_user");
    var cookieHash = getCookie("polixbus_hash");

    if (cookieUser != "" && cookieHash != "") {
        verifyCookie(cookieUser, cookieHash)
    } else {
        showLogin();
    }
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function showLogin() {
    document.getElementById("login").style.visibility = 'visible';     // Show
    document.getElementById("logged").style.visibility = 'hidden';      // Hide
}

function showLogged() {
    document.getElementById("logged").style.visibility = 'visible';     // Show
    document.getElementById("login").style.visibility = 'collapse';      // Hide
}

function verifyCookie(user, hash) {
    $.post('checkUser.php', { field1: user, field2 : hash},
        function(returnedData){
            if(JSON.parse(returnedData).t == 1) {
                document.getElementById("logged").innerHTML = JSON.parse(returnedData).d;
                showLogged();
            } else {
                logout();
            }
        });
}

function login() {
    var user = document.getElementById("user").value;
    var pass = document.getElementById("pass").value;

    $.post('login.php', { field1: user, field2 : pass},
        function(returnedData){
            console.log(returnedData);
            location.reload();
        });
}

function logout() {
    document.cookie = "polixbus_user=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    document.cookie = "polixbus_hash=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    location.reload();
}