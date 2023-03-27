class Enigma{

}

class Rotor{
    letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
    letterTransformation = {'I': 'EKMFLGDQVZNTOWYHXUSPAIBRCJ', 'II': 'AJDKSIRUXBLHWTMCQGZNPYFVOE', 'III': 'BDFHJLCPRTXVZNYEIWGAKMUSQO',
    'IV': 'ESOVPZJAYQUIRHXLNFTGKDCMWB', 'V': 'VZBRGITYUPSDNHLXAWMJQOFECK', 'VI': 'JPGVOUMFYQBENHZRDKASXLICTW',
    'VII': 'NZJHGRCXMYSWBOUFAIVLPEKQDT', 'VIII': 'FKQHTLXOCBJSPDZRAMEWNIUYGV', 'β': 'LEYJVCNIXWPBQMDRTAKZGFUHOS', 'γ': 'FSOKANUERHMBTIYCWLQPZXVGJD'}
    constructor(position, type) {
        this.position = position

        this.type = type
    }

    set position(val){
        if (val.charCodeAt(0) >= 'A'.charCodeAt(0) && val <= 'Z'.charCodeAt(0)) {
            this.position = val
        }

        throw new Error("Invalid rotor position " + val)
    }

    set type(val){
        switch (val.charCodeAt(0)){
            case 'I'.charCodeAt(0):
                this.step_trigger = 'Q'
                break
            case'II'.charCodeAt(0):
                this.step_trigger = 'E'
                break
            case 'III'.charCodeAt(0):
                this.step_trigger = 'V'
                break
            case 'IV'.charCodeAt(0):
                this.step_trigger = 'J'
                break
                case 'V'.charCodeAt(0):
                    this.step_trigger = 'Z'
                    break
            case 'VI'.charCodeAt(0), 'VII'.charCodeAt(0), 'VIII'.charCodeAt(0):
                this.step_trigger = 'ZM'
                break
                case 'β'.charCodeAt(0), 'γ'.charCodeAt(0):
                    this.step_trigger = 'none'
                break
            default:
                throw new Error("Invalid rotor type " + val)
        }

        this.type = val
    }

    set onNotchTrigged(action){
        this.onNotchTriggedAction = action
    }

    get position(){
        return this.position()
    }

    get type(){
        return this.type
    }

    get step_trigger(){
        return this.step_trigger
    }

    move(direction = 1){
        let code = this.position().charCodeAt(0) + direction

        if (this.position === this.step_trigger && direction === 1)
            this.onNotchTriggedAction(direction)

        if (code > 'Z'.charCodeAt(0))
            code = 'A'.charCodeAt(0)
        else if (code < 'A'.charCodeAt(0))
            code = 'Z'.charCodeAt(0)

        this.position = String.fromCharCode(code)

        if (this.position === this.step_trigger && direction === -1)
            this.onNotchTriggedAction(direction)
    }

    moveForward(){
        this.move()
    }

    moveBackward(){
        this.move(-1)
    }

    transform(letter){
        const transformString = this.letterTransformation[this.type]

        const letterPosition = this.letters.indexOf(letter)

        return transformString[letterPosition]
    }
}

class Reflector {
    letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
    letterTransformation = {'B': 'ENKQAUYWJICOPBLMDXZVFTHRGS', 'C': 'RDOBJNTKVEHMLFCWZAXGYIPSUQ'}

    constructor(type) {
        this.type = type
    }

    get type(){
        return this.type
    }

    set type(val){
        if (val == 'B' || val == 'C') {
            this.type = val
            return
        }

        throw new Error("Invalid reflector type " + val)
    }

    transform(letter){
        const transformString = this.letterTransformation[this.type]

        const letterPosition = this.letters.indexOf(letter)

        return transformString[letterPosition]
    }
}

class Plug {
    constructor(input, output) {
        this.input = input
        this.output = output
    }

    get input(){
        return this.input
    }

    get output(){
        return this.output
    }

    set input(val){
        val = val.toUpperCase()
        if (val >= 'A' && val <= 'Z'){
            this.input = val
            return
        }

        throw  new Error("Input val for plug must be a letter from A to Z")
    }

    set output(val){
        val = val.toUpperCase()
        if (val >= 'A' && val <= 'Z'){
            this.output = val
            return
        }

        throw  new Error("Output val for plug must be a letter from A to Z")
    }

    transform(letter){
        //TODO implement method
        throw  new Error("Not implemented")
    }
}
