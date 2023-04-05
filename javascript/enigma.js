class Enigma {
    get rotor1() {
        return this._rotor1;
    }

    get rotor2() {
        return this._rotor2;
    }

    get rotor3() {
        return this._rotor3;
    }

    get rotor4() {
        return this._rotor4;
    }

    get reflector() {
        return this._reflector;
    }

    get plugboard() {
        return this._plugboard;
    }

    constructor(rotor1 = new Rotor('A', 'I'), rotor2 = new Rotor('A', 'I'),
                rotor3 = new Rotor('A', 'I'), rotor4 = new Rotor('A', 'β'),
                reflector = new Reflector('B'), plugboard = [new Plug(' ', ' '),
            new Plug(' ', ' '), new Plug(' ', ' '), new Plug(' ', ' '),
            new Plug(' ', ' '), new Plug(' ', ' '), new Plug(' ', ' '),
            new Plug(' ', ' '), new Plug(' ', ' '), new Plug(' ', ' '), new Plug(' ', ' '),
            new Plug(' ', ' '), new Plug(' ', ' ')]) {

        if (plugboard.length !== 13)
            throw new Error("Plugboard must have 13 plugs, " + plugboard.length + " were given")

        if (rotor4.type != 'β' && rotor4.type != 'γ')
            throw new Error("Rotor 4 must be β or γ type, " + rotor4.type + " was given")

        this._rotor1 = rotor1;
        this._rotor2 = rotor2;
        this._rotor3 = rotor3;
        this._rotor4 = rotor4;
        this._reflector = reflector;
        this._plugboard = plugboard;

        this.rotor1.onNotchTrigged = rotor2
        this.rotor2.onNotchTrigged = rotor3
    }

    moveForward() {
        this.rotor1.moveForward()

        if (this.rotor2.position == this.rotor2.step_trigger)
            this.rotor2.moveForward()
    }

    moveBackward() {
        this.rotor1.moveBackward()

        if (this.rotor2.position == this.rotor2.step_trigger)
            this.rotor2.moveBackward()
    }

    transform(letter) {
        this.plugboard.forEach(plug => {
            letter = plug.transform(letter)
        })
        letter = this.rotor1.transformRight(letter)
        letter = this.rotor2.transformRight(letter)
        letter = this.rotor3.transformRight(letter)
        letter = this.rotor4.transformRight(letter)
        letter = this.reflector.transform(letter)
        letter = this.rotor4.transformLeft(letter)
        letter = this.rotor3.transformLeft(letter)
        letter = this.rotor2.transformLeft(letter)
        letter = this.rotor1.transformLeft(letter)
        this.plugboard.forEach(plug => {
            letter = plug.transform(letter)
        })

        return letter
    }

    getStatus(){
        const plugboardStatus = []
        this.plugboard.forEach(plug => plugboardStatus.push(plug.getStatus()))

        return {rotor1: this.rotor1.getStatus(), rotor2: this.rotor2.getStatus(), rotor3: this.rotor3.getStatus(),
            rotor4: this.rotor4.getStatus(), reflector: this.reflector.getStatus(), plugboard: plugboardStatus}
    }
}

class Rotor {
    inputPins = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
    transformationPins = {
        'I': 'EKMFLGDQVZNTOWYHXUSPAIBRCJ',
        'II': 'AJDKSIRUXBLHWTMCQGZNPYFVOE',
        'III': 'BDFHJLCPRTXVZNYEIWGAKMUSQO',
        'IV': 'ESOVPZJAYQUIRHXLNFTGKDCMWB',
        'V': 'VZBRGITYUPSDNHLXAWMJQOFECK',
        'VI': 'JPGVOUMFYQBENHZRDKASXLICTW',
        'VII': 'NZJHGRCXMYSWBOUFAIVLPEKQDT',
        'VIII': 'FKQHTLXOCBJSPDZRAMEWNIUYGV',
        'β': 'LEYJVCNIXWPBQMDRTAKZGFUHOS',
        'γ': 'FSOKANUERHMBTIYCWLQPZXVGJD'
    }

    constructor(position, type) {
        this.position = position

        this.type = type
    }

    set position(val) {
        if (val.charCodeAt(0) >= 'A'.charCodeAt(0) && val.charCodeAt(0) <= 'Z'.charCodeAt(0)) {
            this._position = val

            this.inputPins = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            this.transformationPins  = {
                'I': 'EKMFLGDQVZNTOWYHXUSPAIBRCJ',
                'II': 'AJDKSIRUXBLHWTMCQGZNPYFVOE',
                'III': 'BDFHJLCPRTXVZNYEIWGAKMUSQO',
                'IV': 'ESOVPZJAYQUIRHXLNFTGKDCMWB',
                'V': 'VZBRGITYUPSDNHLXAWMJQOFECK',
                'VI': 'JPGVOUMFYQBENHZRDKASXLICTW',
                'VII': 'NZJHGRCXMYSWBOUFAIVLPEKQDT',
                'VIII': 'FKQHTLXOCBJSPDZRAMEWNIUYGV',
                'β': 'LEYJVCNIXWPBQMDRTAKZGFUHOS',
                'γ': 'FSOKANUERHMBTIYCWLQPZXVGJD'
            }
            for (let delta = val.charCodeAt(0) - 'A'.charCodeAt(0); delta > 0; delta--){
                this.shiftInputPins()
            }
            return
        }

        throw new Error("Invalid rotor position " + val)
    }

    set type(val) {
        switch (val) {
            case 'I':
                this._step_trigger = 'Q'
                break
            case'II':
                this._step_trigger = 'E'
                break
            case 'III':
                this._step_trigger = 'V'
                break
            case 'IV':
                this._step_trigger = 'J'
                break
            case 'V':
                this._step_trigger = 'Z'
                break
            case 'VI':
            case 'VII':
            case 'VIII':
                this._step_trigger = 'ZM'
                break
            case 'β':
            case 'γ':
                this._step_trigger = 'none'
                break
            default:
                throw new Error("Invalid rotor type " + val)
        }

        this._type = val
    }

    set onNotchTrigged(action) {
        this._onNotchTriggedAction = action
    }

    get position() {
        return this._position
    }

    get type() {
        return this._type
    }

    get step_trigger() {
        return this._step_trigger
    }

    move(direction = 1) {
        let code = this.position.charCodeAt(0) + direction

        if (this.position === this.step_trigger && this._onNotchTriggedAction != null)
            this._onNotchTriggedAction.move(direction)

        if (code > 'Z'.charCodeAt(0))
            code = 'A'.charCodeAt(0)
        else if (code < 'A'.charCodeAt(0))
            code = 'Z'.charCodeAt(0)

        this.position = String.fromCharCode(code)
    }

    moveForward() {
        this.move()
    }

    moveBackward() {
        this.move(-1)
    }

    shiftInputPins(direction = 1) {
        if (direction == 1) {
            const letter = this.inputPins[0]
            this.inputPins = this.inputPins.substring(1, this.inputPins.length) + letter

            Object.keys(this.transformationPins).forEach(key => {
                const letter = this.transformationPins[key][0]
                this.transformationPins[key] =
                    this.transformationPins[key].substring(1, this.transformationPins[key].length) + letter
            })
        } else {
            const letter = this.inputPins.at(-1)
            this.inputPins = letter + this.inputPins.substring(0, this.inputPins.length - 1)

            Object.keys(this.transformationPins).forEach(key => {
                const letter = this.transformationPins[key].at(-1)
                this.transformationPins[key] =
                    letter - this.transformationPins[key].substring(0, this.transformationPins[key].length - 1)
            })
        }
    }

    shift(letter, type = 'in') {
        const input = type == 'in' ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : this.inputPins
        const output = type == 'in' ? this.inputPins : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'

        const position = input.indexOf(letter)

        return output[position]
    }

    transform(letter, direction = 'right') {
        const transformString = direction == 'right' ? this.transformationPins[this.type] : this.inputPins
        const lettersString = direction == 'right' ? this.inputPins : this.transformationPins[this.type]

        letter = this.shift(letter)

        let letterPosition = lettersString.indexOf(letter)

        letter = transformString[letterPosition]

        letter = this.shift(letter, 'out')

        return letter
    }

    transformRight(letter) {
        return this.transform(letter)
    }

    transformLeft(letter) {
        return this.transform(letter, 'left')
    }

    getStatus(){
        return {type: this.type, position: this.position}
    }
}

class Reflector {
    letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
    letterTransformation = {'B': 'ENKQAUYWJICOPBLMDXZVFTHRGS', 'C': 'RDOBJNTKVEHMLFCWZAXGYIPSUQ'}

    constructor(type) {
        this.type = type
    }

    get type() {
        return this._type
    }

    set type(val) {
        if (val == 'B' || val == 'C') {
            this._type = val
            return
        }

        throw new Error("Invalid reflector type " + val)
    }

    transform(letter) {
        const transformString = this.letterTransformation[this.type]

        const letterPosition = this.letters.indexOf(letter)

        letter = transformString[letterPosition]

        return letter
    }

    getStatus(){
        return {type: this.type}
    }
}

class Plug {
    constructor(pin1, pin2) {
        this.pin1 = pin1
        this.pin2 = pin2
    }

    get pin1() {
        return this._pin1
    }

    get pin2() {
        return this._pin2
    }

    set pin1(val) {
        val = val.toUpperCase()
        if ((val.charCodeAt(0) >= 'A'.charCodeAt(0) && val.charCodeAt(0) <= 'Z'.charCodeAt(0)) || val === ' ') {
            this._pin1 = val
            return
        }

        throw  new Error("Input val for plug must be a letter from A to Z")
    }

    set pin2(val) {
        val = val.toUpperCase()
        if ((val.charCodeAt(0) >= 'A'.charCodeAt(0) && val.charCodeAt(0) <= 'Z'.charCodeAt(0)) || val === ' ') {
            this._pin2 = val
            return
        }

        throw  new Error("Output val for plug must be a letter from A to Z")
    }

    transform(letter) {
        if (letter === this.pin1)
            return this.pin2

        if (letter === this.pin2)
            return this.pin1

        return letter
    }

    getStatus(){
        return {pin1: this.pin1, pin2: this.pin2}
    }
}
