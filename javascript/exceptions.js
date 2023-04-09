function handleError(error) {
    switch (error["message"]) {
        case "token expired":
            alert("Ваша сессия истекла, пожалуйста, войдите снова")
            break
        case "user not found":
            alert("Аккаунт не найден, возможно, он был удалён. Пожалуйста, войдите в существующий аккаунт или создайте новый")
            break
        case "identity token required":
            alert("Пожалуйста,  войдите в существующий аккаунт или создайте новый")
            break
        case "invalid token signature":
            alert("Возможно токен был изменён, войдите в аккаунт снова")
            break
        case "cyphertext with that name already exists":
            alert("Вы уже создавали шифр с таким именем")
            return
        case "cyphertext not found":
            alert('Шифр не найден')
            return
        case "no id provided":
            alert("Не предоставлен id шифра")
            return
        case "cant modify cyphertext, which doesnt belong to user":
            alert("Попытка модификации чужого шифра")
            return
        case "You cant crack your own cyphertext":
            alert("Нельзя взломать свой собственный шифр")
            return;
        default:
            alert("Неизвестная ошибка")
            return
    }

    deleteCookie("token")
    showForm(true)
}