var formType = "login"

document.head.insertAdjacentHTML("beforeend", `<link rel="stylesheet" href="css/auth_form.css">`)

document.body.insertAdjacentHTML("beforeend", 
`
<article class="popup" id="popup">
<form id="auth_form" class="wrapper" autocomplete="off">
    <button type="button" class="close-button" onclick="showForm(false)"><img src="images/cross.svg"/></button>
    <section>
        <h1 id="form_header">Login</h1>
        <span id="auth_error" class="error-text"></span><br>
        <span class="username">Имя пользователя:<br><input class="text" type="text" maxlength=30 id="username"><br></span>
        E-mail:<br><input class="text" required type="email" id="email"><br>
        Пароль:<br><input class="text" required minlength="8" type="password" id="password"><br>
        <div class="controls">
        <button type="button" class="submit-button" onclick="onFormSubmitted()">Login</button>
        <button type="button" class="link-like-button" id="type_changer" onclick="changeType()">Sign up</button>
        </div>
    </section>
</form>

</article>`)

function showForm(show){
    var form = document.getElementById("popup")

    if (show){
        form.style.display = "flex"
    }
    else{
        form.style.display = "none"
    }
}

function changeType(){
    if (formType === "login"){
        formType = "sign up"
        document.getElementById("form_header").innerText = "Sign up"
        document.getElementsByClassName("submit-button")[0].innerText = "Sign up"
        document.getElementsByClassName("username")[0].style.display = "block"
        document.getElementById("type_changer").innerText = "Login"
        //document.getElementById("auth_form").action = "auth/signup.php"
    }
    else{
        formType = "login"
        document.getElementById("form_header").innerText = "Login"
        document.getElementsByClassName("submit-button")[0].innerText = "Login"
        document.getElementsByClassName("username")[0].style.display = "none"
        document.getElementById("type_changer").innerText = "Sign up"
        //document.getElementById("auth_form").action = "auth/login.php"
    }
}

async function onFormSubmitted(){
    if (formType === "login"){
        var username = document.getElementById("email").value
        var password = document.getElementById("password").value
        

        var authData = window.btoa([username, password].join(":"))

        let response = await fetch('/auth/login.php', {
            method: 'GET',
            headers: {
                Authorization: ["Basic", authData].join(" ")
            }
        })

        let result = await response.json()

        handleResponse(result, response.ok)

        if (getCookie("reload_page")){
            window.location.reload()
        }

        login()
    }
    else{
        var username = document.getElementById("username").value
        var email = document.getElementById("email").value
        var password = document.getElementById("password").value

        var authData = window.btoa([email, password].join(":"))

        var form = new FormData();
        form.append("username", username)
        let response = await fetch('/auth/signup.php', {
            method: 'POST',
            headers: {
                Authorization: ["Basic", authData].join(" "),
            },
            body: form
        })

        let result = await response.json()

        handleResponse(result, response.ok)

        changeType()
        await onFormSubmitted()
        changeType()
    }
}

function handleResponse(response, code){
    var text = ""
    if (!code){
        text = response["message"]
    }
    else
    {
        text = ""
        showForm(false)
    }
    
    document.getElementById("auth_error").innerText = text
}