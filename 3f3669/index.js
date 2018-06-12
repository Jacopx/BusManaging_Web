/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *        Distributed Programming - WebProgramming == Jacopo Nasi          *
 *      Repo avail: https://github.com/Jacopx/BusManaging_WebPlatform      *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

function checkCookie() {
    var cookie = getCookie("polixbus");
    if (cookie != "") {
        alert("Welcome again " + cookie);
    } else {
        alert("Coockie NOT SET");
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