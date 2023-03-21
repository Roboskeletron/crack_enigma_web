addScript("javascript/exceptions.js")
addScript("javascript/navigation_bar.js")
addScript("javascript/auth_form.js")

function addScript(url){
    const script = document.createElement("script")
    script.src = url

    document.body.appendChild(script)
}