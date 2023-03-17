document.head.insertAdjacentHTML("beforeend", `<link rel="stylesheet" href="css/navigation_bar.css">`)

var header = document.getElementsByTagName("header")

if (header.length <= 0){
    header = document.body
}
else{
    header = header[0]
}

header.insertAdjacentHTML("afterbegin",`
<nav>
    <a href="#"><img src="images/Enigma-logo.svg"></a>
    <ul>
        <li> <a href="main.html">Главная</a></li>
        <li> <a href="index.html">О сайте</a></li>
    </ul>
    <ul>
        <li><button id="profile_button" class="profile-button"><img src="images/empty-profile.svg"></button></li>
        <li><button id="login_button" onclick="onLoginButtonClicked()">Login</button></li>
    </ul>
</nav>`);

var token = getCookie('token')
var login_button = document.getElementById("login_button")
var profile_button = document.getElementById("profile_button")

if (token != null){
    login()
}

function onLoginButtonClicked(){
    if (token == null){
        showForm(true)
    }
    else{
        logout()
    }
}

function logout(){
    deleteCookie('token')
    login_button.innerText = "Login"
    profile_button.style.display = 'none'
    token = null
}

function login(){
    login_button.innerText = "Logout"
    profile_button.style.display = 'block'
    token = getCookie('token')
}