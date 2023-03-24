const rotors = [document.getElementById("rotor1"), document.getElementById("rotor2"),
    document.getElementById("rotor3")]

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

addRotorTypes(rotors)
