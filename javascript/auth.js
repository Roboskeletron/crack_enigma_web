var authStatus = getCookie("auth_status");
var expiresMillisec = 60 * 30;

setAuthStatus(authStatus)

function setAuthStatus(status){
    var date = new Date();
    date.setMilliseconds(expiresMillisec);
    setCookie("auth_status", status, {expires: date});
    console.log(getCookie("auth_status"));

    if (status == "true"){
        authStatus = true;
        document.getElementById("login_button").innerHTML = "Logout";
    }
    else{
        authStatus = false;
        document.getElementById("login_button").innerHTML = "Login";
    }
}

function onLoginButtonClicked(){
    if (authStatus){
        setAuthStatus("false");
    }
    else{
        document.getElementById("popup").style.display = "block";
    }
}