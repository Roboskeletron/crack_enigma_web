const enigma = new Enigma()
const input = document.getElementById('text input')
const output = document.getElementById('text output')
const rotors = [document.getElementById("rotor1"), document.getElementById("rotor2"),
    document.getElementById("rotor3")]
const actionButton = document.getElementById('actionButton')
const deleteButton = document.getElementById('deleteButton')

let length = 0
let encryptedLength = 0
let lastLetter = ''
let reevaluateWholeText = false
let saveText = false

function addRotorTypes(rotors) {
    rotors.forEach(rotor => rotor.insertAdjacentHTML("beforeend",
        `<option>I</option>
<option>I</option>
<option>II</option>
<option>III</option>
<option>IV</option>
<option>V</option>
<option>VI</option>
<option>VII</option>
<option>VIII</option>`))
}

function onPositionChanged(rotorId) {
    reevaluateText(() => {
        document.getElementById(rotorId).value = document.getElementById(rotorId).value.toUpperCase()
        const value = document.getElementById(rotorId).value
        switch (rotorId) {
            case 'value rotor1':
                enigma.rotor1.position = value
                break
            case 'value rotor2':
                enigma.rotor2.position = value
                break
            case 'value rotor3':
                enigma.rotor3.position = value
                break
            case 'value rotor4':
                enigma.rotor4.position = value
                break
        }
        saveEnigmaStatus()
    })
}

function onTypeChanged(rotorId) {
    reevaluateText(() => {
        const value = document.getElementById(rotorId).value
        switch (rotorId) {
            case 'rotor1':
                enigma.rotor1.type = value
                break
            case 'rotor2':
                enigma.rotor2.type = value
                break
            case 'rotor3':
                enigma.rotor3.type = value
                break
            case 'rotor4':
                enigma.rotor4.type = value
                break
            case 'reflector type':
                enigma.reflector.type = value
                break
        }
        saveEnigmaStatus()
    })
}

function encryptLetter(value) {
    value = value.toUpperCase()

    if (!canEncrypt(value))
        return

    enigma.moveForward()
    value = enigma.transform(value)
    output.value += value
    encryptedLength++

    if (encryptedLength % 4 == 0)
        output.value += ' '
}

function deleteLetter(value) {
    value = value.toUpperCase()

    if (!canEncrypt(value))
        return

    enigma.moveBackward()
    output.value = output.value.substring(0, output.value.length - 1)
    encryptedLength--

    if (encryptedLength % 4 == 0)
        output.value = output.value.substring(0, output.value.length - 1)
}

function canEncrypt(letter) {
    return letter.charCodeAt(0) >= 'A'.charCodeAt(0) && letter.charCodeAt(0) <= 'Z'.charCodeAt(0)
}

function onTextInput() {
    if (reevaluateWholeText) {
        for (let i = 0; i < encryptedLength; i++) {
            enigma.moveBackward()
        }

        output.value = ''
        encryptedLength = 0
        for (let i = 0; i < input.value.length; i++) {
            let value = input.value[i]
            encryptLetter(value)
        }
        reevaluateWholeText = false
        length = input.value.length
        lastLetter = input.value.at(-1)
        saveInputText()
        return
    }

    const delta = input.value.length - length

    if (delta == -1)
        for (let i = delta; i < 0; i++) {
            deleteLetter(lastLetter)
            lastLetter = input.value.at(-1)
        }
    else if (delta == 1)
        for (let i = length; i < length + delta; i++) {
            let value = input.value[i]
            encryptLetter(value)
        }
    else {
        reevaluateWholeText = true
        onTextInput()
        return
    }

    length += delta
    lastLetter = input.value.at(-1)
    saveInputText()
}

function onPaste() {
    reevaluateWholeText = true
}

function onSwapClicked() {
    onPaste()
    input.value = output.value
    onTextInput()
}

function onRotorMoveButtonClicked(id) {
    id = id.split(' ')

    const direction = id[0] == 'up' ? 1 : -1
    id = 'value ' + id[1]

    const positionValue = document.getElementById(id)

    let code = positionValue.value.charCodeAt(0) + direction
    if (code < 'A'.charCodeAt(0))
        code = 'Z'.charCodeAt(0)
    else if (code > 'Z'.charCodeAt(0))
        code = 'A'.charCodeAt(0)

    positionValue.value = String.fromCharCode(code)

    onPositionChanged(id)
}

function reevaluateText(action) {
    const text = input.value
    input.value = ''
    onPaste()
    onTextInput()

    action()

    input.value = text
    onTextInput()
}

function onPlugChanged(id) {
    const plug = document.getElementById(id)
    plug.value = plug.value.toUpperCase()

    id = Number.parseInt(id.substring(4, 6)) - 1

    const values = plug.value.split('')

    if (plug.value.length != 2 || !canEncrypt(values[0]) || !canEncrypt(values[1])) {
        plug.value = ''
        reevaluateText(() => {
            enigma.plugboard[id].pin1 = ' '
            enigma.plugboard[id].pin2 = ' '
            saveEnigmaStatus()
        })
        return
    }

    for (let i = 0; i < enigma.plugboard.length; i++)
        if (enigma.plugboard[i].pin1 === values[0] || enigma.plugboard[i].pin1 === values[1]
            || enigma.plugboard[i].pin2 === values[0] || enigma.plugboard[i].pin2 === values[1]) {
            plug.value = ''
            alert('Невозможно использовать одно значение несколько раз')
            reevaluateText(() => {
                enigma.plugboard[id].pin1 = ' '
                enigma.plugboard[id].pin2 = ' '
                saveEnigmaStatus()
            })
            return
        }

    reevaluateText(() => {
        enigma.plugboard[id].pin1 = values[0]
        enigma.plugboard[id].pin2 = values[1]
        saveEnigmaStatus()
    })
}

function saveEnigmaStatus() {
    const enigmaStatus = enigma.getStatus()

    const json = JSON.stringify({'enigma status': enigmaStatus})

    setCookie('enigmaStatus', json)
}

function updateEnigmaStatus(status) {
    if (status == null || status == '')
        return

    const json = JSON.parse(status)
    const enigmaStatus = json['enigma status']

    rotors[0].value = enigmaStatus['rotor1']['type']
    rotors[1].value = enigmaStatus['rotor2']['type']
    rotors[2].value = enigmaStatus['rotor3']['type']
    rotors[3].value = enigmaStatus['rotor4']['type']
    document.getElementById('reflector type').value = enigmaStatus['reflector']['type']

    document.getElementById('value rotor1').value = enigmaStatus['rotor1']['position']
    document.getElementById('value rotor2').value = enigmaStatus['rotor2']['position']
    document.getElementById('value rotor3').value = enigmaStatus['rotor3']['position']
    document.getElementById('value rotor4').value = enigmaStatus['rotor4']['position']

    const plugboard = enigmaStatus['plugboard']

    for (let i = 0; i < plugboard.length; i++) {
        const id = 'plug' + (i + 1).toString()

        document.getElementById(id).value = plugboard[i]['pin1'] + plugboard[i]['pin2']
    }

    applyEnigmaStatusChanges()
}

function applyEnigmaStatusChanges() {
    onTypeChanged('rotor1')
    onPositionChanged('value rotor1')
    onTypeChanged('rotor2')
    onPositionChanged('value rotor2')
    onTypeChanged('rotor3')
    onPositionChanged('value rotor3')
    onTypeChanged('rotor4')
    onPositionChanged('value rotor4')

    onTypeChanged('reflector type')

    for (let i = 1; i < 14; i++)
        onPlugChanged('plug' + i.toString())
}

function saveInputText() {
    if (!saveText)
        return

    setCookie('text', input.value)
}

function updateInputText() {
    saveText = true
    const text = getCookie('text')

    if (text == null || text == '')
        return

    input.value = text
    reevaluateText(() => {
    })
}

function onActionButtonClicked() {
    const action = getParams().action

    switch (action) {
        case 'create': {
            const enigmaStatus = JSON.parse(getCookie('enigmaStatus'))
            const name = prompt("Введите имя:", '')
            const text = input.value

            createCyphertext(name, text, enigmaStatus)
            break
        }
        case 'modify': {
            const enigmaStatus = JSON.parse(getCookie('enigmaStatus'))
            const text = input.value

            uploadUpdate(text, enigmaStatus)
        }
        case 'crack': {
            const enigmaStatus = getCookie('enigmaStatus')

            uploadCrack(enigmaStatus)
            break
        }
        default:
            throw new Error('Unsupported action provided')
    }
}

function getParams() {
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get(target, p, receiver) {
            return target.get(p)
        }
    })

    return params;
}

function downloadCyphertext(id) {
    if (id == null)
        throw new Error("No id provided")

    token = getCookie('token')

    let response = fetch('/cyphertext/cyphertext.php?' + new URLSearchParams({token: token, id: id}, {
        method: 'GET'
    }))

    response.then(response => responseCallback(response))

    function responseCallback(response) {
        const json = response.json()
        if (response.ok)
            json.then(response => handleResponse(response))
        else
            json.then(error => handleError(error))
    }

    function handleResponse(response) {
        if (response['code'] == null) {
            setCookie('text', response['encrypted'])
            input.value = response['text']
            updateInputText()
            return
        }

        setCookie('enigmaStatus', response['code'])
        setCookie('text', response['text'])
        updateEnigmaStatus(JSON.stringify({'enigma status': response['code']}))
        input.value = response['text']
        updateInputText()
    }
}

function onInitialized() {
    const params = getParams()
    const action = params.action

    if (action == null) {
        throw new Error('No action provided')
    }

    switch (action) {
        case 'create': {
            const reset = getCookie('resetCyphertext')

            if (reset == 'true'){
                deleteCookie('text')
                deleteCookie('enigmaStatus')
                saveEnigmaStatus()
            }

            updateEnigmaStatus(getCookie('enigmaStatus'))
            updateInputText()

            setCookie('resetCyphertext', 'false')
            break
        }
        case 'modify': {
            setCookie('resetCyphertext', 'true')
            downloadCyphertext(params.id)
            actionButton.innerText = "Сохранить"
            deleteButton.style.display = 'block'
            break
        }
        case 'crack': {
            setCookie('resetCyphertext', 'true')
            saveEnigmaStatus()
            downloadCyphertext(params.id)
            break
        }
        default:
            throw new Error('Unsupported action provided')
    }
}

function createCyphertext(name, text, status) {
    if (name == null || name == '') {
        alert('Попробуйте друго имя')
        return
    }

    if (text == null || text == '')
        return

    const json = JSON.stringify({name: name, text: text, 'enigma status': status['enigma status']})
    const token = getCookie('token')

    let response = fetch('cyphertext/cyphertext.php?' +
        new URLSearchParams({token: token}).toString(), {
        method: 'PUT',
        headers: {
            ContentType: 'application/json'
        },
        body: json
    })

    function handleResponse(response) {
        const id = response['id'];

        window.location.replace('/cyphertext.html?' + new URLSearchParams({action: 'modify', id: id}))
    }

    function responseCallback(response) {
        const json = response.json()
        if (response.ok)
            json.then(response => handleResponse(response))
        else
            json.then(error => handleError(error))
    }

    response.then(response => responseCallback(response))
}

function uploadUpdate(text, status) {
    if (text.length == 0) {
        alert("Шифр не должен быть пустым")
        return
    }

    const json = JSON.stringify({text: text, 'enigma status': status['enigma status']})
    const token = getCookie('token')

    let response = fetch('/cyphertext/cyphertext.php' + window.location.search +
        '&' + new URLSearchParams({token: token}).toString(), {
        method: 'POST',
        headers: {
            ContentType: 'application/json'
        },
        body: json
    })

    function responseCallback(response) {
        const json = response.json()
        if (response.ok)
            json.then(response => handleResponse(response))
        else
            json.then(error => handleError(error))
    }

    function handleResponse(response) {
        window.location.reload()
    }

    response.then(response => responseCallback(response))
}

function onDeleteButtonClicked() {
    function responseCallback(response) {
        const json = response.json()
        if (response.ok)
            json.then(response => handleResponse(response))
        else
            json.then(error => handleError(error))
    }

    function handleResponse(response) {
        deleteCookie('enigmaStatus')
        deleteCookie('text')
        window.history.back()
    }

    if (confirm('Удалить шифр? Это действие нельзя отменить!')) {
        let response = fetch('/cyphertext/cyphertext.php' + window.location.search +
            '&' + new URLSearchParams({token: token}).toString(), {
            method: 'DELETE'
        })

        response.then(response => responseCallback(response))
    }
}

function uploadCrack(code) {
    let response = fetch('/cyphertext/cyphertext.php' + window.location.search +
        '&' + new URLSearchParams({token: token}).toString(), {
        method: 'POST',
        headers: {
            ContentType: 'application/json'
        },
        body: code
    })

    function responseCallback(response) {
        const json = response.json()
        if (response.ok)
            json.then(response => handleResponse(response))
        else
            json.then(error => handleError(error))
    }

    function handleResponse(response) {
        switch (response['message']) {
            case 'failed to crack cyphertext due to invalid code': {
                alert('Не удалось взломать шифр, неверный код')
                break
            }
            case 'cyphertext cracked successfully': {
                alert('Шифр взломан успешно')
                break
            }
        }
    }

    response.then(response => responseCallback(response))
}

function onResetStatusClicked(){
    const json = '{"enigma status":{"rotor1":{"type":"I","position":"A"},"rotor2":{"type":"I","position":"A"},"rotor3":{"type":"I","position":"A"},"rotor4":{"type":"β","position":"A"},"reflector":{"type":"B"},"plugboard":[{"pin1":" ","pin2":" "},{"pin1":" ","pin2":" "},{"pin1":" ","pin2":" "},{"pin1":" ","pin2":" "},{"pin1":" ","pin2":" "},{"pin1":" ","pin2":" "},{"pin1":" ","pin2":" "},{"pin1":" ","pin2":" "},{"pin1":" ","pin2":" "},{"pin1":" ","pin2":" "},{"pin1":" ","pin2":" "},{"pin1":" ","pin2":" "},{"pin1":" ","pin2":" "}]}}'
    updateEnigmaStatus(json)
    setCookie('enigmaStatus', json)
}

function onResetTextClicked(){
    deleteCookie('text')
    input.value = ''
    reevaluateText(() => {})
}

addRotorTypes(rotors)

rotors.push(document.getElementById("rotor4"))

onInitialized()
