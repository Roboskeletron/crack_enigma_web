function getTableData(id) {
    var token = getCookie("token")
    function responseCallback(response){
        const json = response.json()
        if (!response.ok){
            json.then(error => handleError(error))
        }
        else{
            json.then(response => handleResponse(response))
        }
    }

    function handleResponse(response){
        const table = document.getElementById("table_container")
        response.forEach(item => addItem(table, item))
        if (response.length > 0)
            setCookie("texts_id", response[response.length - 1]["id"])
    }

    function addItem(table, item){
        table.insertAdjacentHTML("beforeend",
            `<button type="button" onclick="onItemClicked(${item["id"]})" class="row">
<span>${item["name"]}</span>
<span>${item["author"]}</span>
<span>${item["total attempts"]}</span>
<span>${item["successful attempts"]}</span>
</button>`)
    }


    if (id == null)
        id = 0

    const response = fetch('userinfo/cyphertexts.php?' + new URLSearchParams({token: token, limit: 30, item_id: id}).toString())
    response.then(response => responseCallback(response))
}

function onItemClicked(id){

}

function onMoreButtonClicked(id, callback){
    const next_id = getCookie(id)
    setCookie(id, next_id)
    callback(next_id)
}
