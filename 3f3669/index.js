// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
// *        Distributed Programming - WebProgramming == Jacopo Nasi          *
// *      Repo avail: https://github.com/Jacopx/BusManaging_WebPlatform      *
// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
function checkCookie() {
    var cookieUser = getCookie("polixbus_user");
    var cookieHash = getCookie("polixbus_hash");

    if (cookieUser != "" && cookieHash != "") {
        alert("Cookie SET");
        verifyCookie(cookieUser, cookieHash)
        //showLogged()
    } else {
        alert("Cookie NOT SET");
        //showLogin()
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
            console.log(returnedData);
            document.getElementById("logged").innerHTML = returnedData;
        });
}