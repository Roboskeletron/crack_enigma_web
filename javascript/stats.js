function getStats(id) {
    var token = getCookie("token")
    setCookie("reload_page", true)

    const response = fetch('userinfo/stats.php?' + new URLSearchParams({
        token: token,
        limit: 3,
        item_id: id
    }).toString())
    response.then(response => responseCallback(response))

    function responseCallback(response) {
        const json = response.json()
        if (!response.ok) {
            json.then(error => handleError(error))
            document.getElementById("user stats").style.display = 'none'

        } else {
            json.then(response => handleResponse(response))
        }
    }

    function insertItem(table, item) {
        table.insertAdjacentHTML("beforeend",
            `<button type="button" class="row">
<span>${item["name"]}</span>
<span>${item["total attempts"]}</span>
<span>${item["successful attempts"]}</span>
</button>`)
    }

    function handleResponse(response) {
        document.getElementById("username").innerText = response["name"]
        const table = document.getElementById("my cyphertexts")
        const texts = response["cyphertexts"]
        texts.forEach(item => insertItem(table, item))
        if (texts.length > 0)
            setCookie("stats_id", texts[texts.length - 1]["id"])
        else if (id == '0')
            table.innerHTML = "У вас нет шифров"
    }
}

getStats(0)
getTableData(null)
