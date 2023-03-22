const user_info = document.getElementById("user info")
const edit_form = document.getElementById("edit form")
const messagePrompt = document.getElementById("update error")

function showEditForm(show) {
    if (show) {
        document.getElementById("name field").value = document.getElementById("username label").innerText
        document.getElementById("current password").value = ''
        document.getElementById("new password").value = ''
        edit_form.style.display = "block"
        user_info.style.display = "none"
        messagePrompt.style.display = "none"
    } else {
        edit_form.style.display = "none"
        user_info.style.display = "block"
    }
}

function getProfileInfo() {
    const token = getCookie("token")

    function handleResponse(response){
        document.getElementById("username label").innerText = response["name"]
        document.getElementById("email label").innerText = response["email"]
    }

    function responseCallback(response) {
        const json = response.json()
        if (!response.ok)
            json.then(error => handleError(error))
        else {
            json.then(response => handleResponse(response))
        }
    }

    const response = fetch("userinfo/profile.php?" + new URLSearchParams({token: token}).toString())

    response.then(response => responseCallback(response))
}

function onDeleteAccountClicked(){
    const result = prompt("Внимание это дейтсвие отменить нельзя!\nВведите пароль от аккаунта:", '')

    function responseCallback(response) {
        const json = response.json()
        if (response.ok){
            onLoginButtonClicked()
            window.location.replace("/")
        }
        else {
            json.then(error => informUser(error, message => {
                alert(message)
            }))
        }
    }

    function deleteAccount(password) {
        const token = getCookie("token")

        const response = fetch("userinfo/profile.php?" + new URLSearchParams({token: token}).toString(),
            {
                method: 'DELETE',
                headers:{
                    ContentType: 'application/json'
                },
                body: JSON.stringify({password: password})
            })

        response.then(response => responseCallback(response))
    }

    if (result != null || result == ''){
        deleteAccount(result)
    }
    else{
        alert("Требуется пароль, чтобы удалить аккаунт");
    }
}

function onSaveProfileChangesClicked(){
    function responseCallback(response){
        const json = response.json()
        if (!response.ok){
            json.then(error => informUser(error, message => {
                messagePrompt.style.display = "inline-block"
                messagePrompt.innerText = message
            }))
        }
        else{
            showEditForm(false)
            getProfileInfo()
        }
    }

    const token = getCookie("token")
    const name = document.getElementById("name field").value
    const password = document.getElementById("current password").value
    const new_password = document.getElementById("new password").value
    const change_password = new_password != ''

    const json = {}
    json["name"] = name
    json["password"] = password

    if (change_password)
        json["new password"] = new_password

    let response = fetch("userinfo/profile.php?" + new URLSearchParams({token:token}),
        {
            method: 'PUT',
            headers: {
                ContentType: 'application/json'
            },
            body: JSON.stringify(json)
        })

    response.then(response => responseCallback(response))
}

function informUser(error, action) {
    let message = ''

    switch (error["message"]){
        case "password required":
            message = 'Введите свой пароль, чтобы изменить данные'
            break
        case "provided password is not valid":
            message ='Неверный пароль'
            break
        case "Пользователь с таким именем уже существует":
            message = error["message"]
            break
        default:
            handleError(error)
            break
    }

    action(message)
}

setCookie("reload", "true")
showEditForm(false)
getProfileInfo()