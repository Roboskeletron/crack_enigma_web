const enigma = new Enigma()
const input = document.getElementById('text input')
const output = document.getElementById('text output')
const rotors = [document.getElementById("rotor1"), document.getElementById("rotor2"),
    document.getElementById("rotor3")]

let length = 0
let encryptedLength = 0
let lastLetter = ''
let reevaluateWholeText = false

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

function reevaluateText(action){
    const text = input.value
    input.value = ''
    onPaste()
    onTextInput()

    action()

    input.value = text
    onTextInput()
}

function onPlugChanged(id){
    const plug = document.getElementById(id)
    plug.value = plug.value.toUpperCase()

    id = Number.parseInt(id.split('').at(-1)) - 1

    const values = plug.value.split('')

    if (plug.value.length != 2 || !canEncrypt(values[0]) || !canEncrypt(values[1])) {
        plug.value = ''
        enigma.plugboard[id].pin1 = ' '
        enigma.plugboard[id].pin2 = ' '
        reevaluateText(() => {})
        return
    }

    for(let i = 0; i < enigma.plugboard.length; i++)
        if (enigma.plugboard[i].pin1 === values[0] || enigma.plugboard[i].pin1 === values[1]
            || enigma.plugboard[i].pin2 === values[0] || enigma.plugboard[i].pin2 === values[1])
        {
            plug.value = ''
            enigma.plugboard[id].pin1 = ' '
            enigma.plugboard[id].pin2 = ' '
            alert('Невозможно использовать одно значение несколько раз')
            reevaluateText(() => {})
            return
        }

    reevaluateText(() => {
        enigma.plugboard[id].pin1 = values[0]
        enigma.plugboard[id].pin2 = values[1]
    })
}

addRotorTypes(rotors)

rotors.push(document.getElementById("rotor4"))
