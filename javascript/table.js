function getTableData() {
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

    function handleError(error){
        console.error(error)
    }

    function handleResponse(response){
        const table = document.getElementById("table_container")
        response.forEach(item => addItem(table, item))
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

    const response = fetch('userinfo/cyphertexts.php?' + new URLSearchParams({token: token}).toString())
    response.then(response => responseCallback(response))
}

function onItemClicked(id){

}
