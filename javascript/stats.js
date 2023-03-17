function getStats() {
    var token = getCookie("token")
    setCookie("reload_page", true)

    if (token != null) {
        const response = fetch('userinfo/stats.php?' + new URLSearchParams({token: token}).toString())
        response.then(response => responseCallback(response))
    } else {
        alert("Пожалуйста,  войдите с другого аккаунта или создайте новый")
        showForm(true)
    }

    function responseCallback(response){
        const json = response.json()
        if (!response.ok){
            console.error(response)

            json.then(error => handleError(error))

        }
        else{
            json.then(response => handleResponse(response))
        }
    }

    function handleError(error){
        switch (error["message"]){
            case "token expired":
                alert("Ваша сессия истекла, пожалуйста, войдите снова")
                break
            case "User not found":
                alert("Аккаунт не найден, возможно, он был удалён. Пожалуйста, войдите с другого аккаунта или создайте новый")
                break
            case "identity token required":
                alert("Пожалуйста,  войдите с другого аккаунта или создайте новый")
                break
            case "invalid token signature":
                alert("Возможно токен был изменён, войдите в аккаунт снова")
                break
            default:
                alert("Неизвестная ошибка")
                break
        }

        deleteCookie("token")
        showForm(true)
    }

    function handleResponse(response) {
        document.getElementById("username").innerText = response["username"]
        document.getElementById("my_msgs").innerText = response["encrypted messages"]
        document.getElementById("my_decrypted_msgs").innerText = response["decrypted messages"]
        document.getElementById("encrypted_msgs").innerText = response["total messages"]
        document.getElementById("decrypted_msgs").innerText = response["successful attempts"]
    }
}

getStats()
getTableData()
