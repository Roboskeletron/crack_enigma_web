<?php
class Plug{
    private $pin1 = " ";
    private $pin2 = " ";

    public function __construct($pin1, $pin2)
    {
        $this->setPin1($pin1);
        $this->setPin2($pin2);
    }

    /**
     * @return string
     */
    public function getPin1(): string
    {
        return $this->pin1;
    }

    /**
     * @param string $pin1
     */
    public function setPin1(string $pin1): void
    {
        if ($this->canEncode($pin1) || $pin1 == ' '){
            $this->pin1 = $pin1;
            return;
        }

        echo json_encode(array("message" => "plug pin must be a character from A to Z"));
        http_response_code(400);
        die;
    }

    /**
     * @return string
     */
    public function getPin2(): string
    {
        return $this->pin2;
    }

    /**
     * @param string $pin2
     */
    public function setPin2(string $pin2): void
    {
        if ($this->canEncode($pin2) || $pin2 == ' '){
            $this->pin2 = $pin2;
            return;
        }

        echo json_encode(array("message" => "plug pin must be a character from A to Z"));
        http_response_code(400);
        die;
    }

    public function transform($value){
        if ($value == $this->pin1)
            return $this->pin2;

        if ($value == $this->pin2)
            return $this->pin1;

        return $value;
    }

    public static function canEncode(string $value): bool
    {
        return ord($value) >= ord("A") && ord($value) <= ord("Z");
    }
}

class ReflectorEnigma{
    private $letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private  $letterTransformation = array("B" => "ENKQAUYWJICOPBLMDXZVFTHRGS", "C" => "RDOBJNTKVEHMLFCWZAXGYIPSUQ");
    private $type;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        if ($type == "B" || $type == "C") {
            $this->type = $type;
            return;
        }

        echo json_encode(array("message" => "Reflector type must be B or C"));
        http_response_code(400);
        die;
    }

    public function __construct($type){
        $this->setType($type);
    }

    public function transform($value){
        $transformString = $this->letterTransformation[$this->type];
        $letterPosition = strpos($this->letters, $value, 0);

        return substr($transformString, $letterPosition, 1);
    }
}

class Rotor{
    private $inputPins = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private $transformationPins = array(
        "I" => "EKMFLGDQVZNTOWYHXUSPAIBRCJ",
        "II" => "AJDKSIRUXBLHWTMCQGZNPYFVOE",
        "III" => "BDFHJLCPRTXVZNYEIWGAKMUSQO",
        "IV" => "ESOVPZJAYQUIRHXLNFTGKDCMWB",
        "V" => "VZBRGITYUPSDNHLXAWMJQOFECK",
        "VI" => "JPGVOUMFYQBENHZRDKASXLICTW",
        "VII" => "NZJHGRCXMYSWBOUFAIVLPEKQDT",
        "VIII" => "FKQHTLXOCBJSPDZRAMEWNIUYGV",
        "β" => "LEYJVCNIXWPBQMDRTAKZGFUHOS",
        "γ" => "FSOKANUERHMBTIYCWLQPZXVGJD"
    );
    
    private $type;
    private $position;
    private $step_trigger;
    private $onNotchTriggedAction = null;

    /**
     * @return mixed
     */
    public function getStepTrigger()
    {
        return $this->step_trigger;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        switch ($type){
            case 'I':
                $this->step_trigger = "Q";
                break;
            case'II':
                $this->step_trigger = 'E';
                break;
            case 'III':
                $this->step_trigger = 'V';
                break;
            case 'IV':
                $this->step_trigger = 'J';
                break;
            case 'V':
                $this->step_trigger = 'Z';
                break;
            case 'VI':
            case 'VII':
            case 'VIII':
                $this->step_trigger = 'ZM';
                break;
            case 'β':
            case 'γ':
                $this->step_trigger = 'none';
                break;
            default:
            {
                json_encode(array("message" => "Invalid rotor type ".$type));
                http_response_code(400);
                die;
            }
        }
        
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position): void
    {
        if (Plug::canEncode($position)) {
            $this->position = $position;

            $this->inputPins = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $this->transformationPins = array(
                "I" => "EKMFLGDQVZNTOWYHXUSPAIBRCJ",
                "II" => "AJDKSIRUXBLHWTMCQGZNPYFVOE",
                "III" => "BDFHJLCPRTXVZNYEIWGAKMUSQO",
                "IV" => "ESOVPZJAYQUIRHXLNFTGKDCMWB",
                "V" => "VZBRGITYUPSDNHLXAWMJQOFECK",
                "VI" => "JPGVOUMFYQBENHZRDKASXLICTW",
                "VII" => "NZJHGRCXMYSWBOUFAIVLPEKQDT",
                "VIII" => "FKQHTLXOCBJSPDZRAMEWNIUYGV",
                "β" => "LEYJVCNIXWPBQMDRTAKZGFUHOS",
                "γ" => "FSOKANUERHMBTIYCWLQPZXVGJD"
            );

            for ($i = ord($this->position) - ord('A'); $i > 0; $i--){
                $this->shiftInputs();
            }
            return;
        }

        json_encode(array("message" => "Invalid rotor position ".$position));
        http_response_code(400);
        die;
    }

    private function shiftInputs(){
        $letter = substr($this->inputPins, 0, 1);
        $this->inputPins =substr($this->inputPins, 1, strlen($this->inputPins)).$letter;
        foreach ($this->transformationPins as $key => $value){
            $letter = substr($this->transformationPins[$key], 0, 1);
            $this->transformationPins[$key] =
                substr($this->transformationPins[$key],
                    1, strlen($this->transformationPins[$key])).$letter;
        }
    }

    private function shift($letter, $type='in'){
        $input = $type == 'in' ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : $this->inputPins;
        $output = $type == 'in' ? $this->inputPins : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $position = strpos($input, $letter);

        return substr($output, $position, 1);
    }

    public function transform($letter, $direction = 'right'){
        $transformString = $direction == 'right' ? $this->transformationPins[$this->type] : $this->inputPins;
        $lettersString = $direction == 'right' ? $this->inputPins : $this->transformationPins[$this->type];

        $letter = $this->shift($letter);

        $letterPosition = strpos($lettersString, $letter);

        $letter = substr($transformString, $letterPosition, 1);

        return $this->shift($letter, 'out');
    }

    public function moveForward(){
        $code = ord($this->getPosition()) + 1;

        if ($this->getPosition() == $this->getStepTrigger() && $this->onNotchTriggedAction != null)
            $this->onNotchTriggedAction->moveForward();

        if ($code > ord('Z'))
            $code = ord('A');
        else if ($code < ord('A'))
            $code = ord('Z');

        $this->setPosition(chr($code));
    }
    
    public function __construct($position, $type){
        $this->setPosition($position);
        $this->setType($type);
    }

    /**
     * @param mixed $onNotchTriggedAction
     */
    public function setOnNotchTriggedAction($onNotchTriggedAction): void
    {
        $this->onNotchTriggedAction = $onNotchTriggedAction;
    }
}

class Enigma{
    private $rotor1;
    private $rotor2;
    private $rotor3;
    private $rotor4;
    private $reflector;
    private $plugboard;

    /**
     * @param Rotor $rotor1
     * @param Rotor $rotor2
     * @param Rotor $rotor3
     * @param Rotor $rotor4
     * @param Reflector $reflector
     * @param array $plugboard
     */
    public function __construct(Rotor $rotor1, Rotor $rotor2, Rotor $rotor3, Rotor $rotor4, ReflectorEnigma $reflector, array $plugboard)
    {
        $this->rotor1 = $rotor1;
        $this->rotor2 = $rotor2;
        $this->rotor3 = $rotor3;
        $this->rotor4 = $rotor4;
        $this->reflector = $reflector;
        $this->plugboard = $plugboard;
        $this->getRotor1()->setOnNotchTriggedAction($this->getRotor2());
        $this->getRotor2()->setOnNotchTriggedAction($this->getRotor3());
    }

    public function moveForward(){
        $this->rotor1->moveForward();

        if ($this->rotor2->getPosition() == $this->rotor2->getStepTrigger())
            $this->rotor2->moveForward();
    }

    public function transform($letter){
        foreach ($this->plugboard as $key => $plug){
            $letter = $plug->transform($letter);
        }
        $letter = $this->rotor1->transform($letter);
        $letter = $this->rotor2->transform($letter);
        $letter = $this->rotor3->transform($letter);
        $letter = $this->rotor4->transform($letter);
        $letter = $this->reflector->transform($letter);
        $letter = $this->rotor4->transform($letter, 'left');
        $letter = $this->rotor3->transform($letter, 'left');
        $letter = $this->rotor2->transform($letter, 'left');
        $letter = $this->rotor1->transform($letter, 'left');
        foreach ($this->plugboard as $key => $plug){
            $letter = $plug->transform($letter);
        }

        return $letter;
    }

    /**
     * @return Rotor
     */
    public function getRotor1(): Rotor
    {
        return $this->rotor1;
    }

    /**
     * @return Rotor
     */
    public function getRotor2(): Rotor
    {
        return $this->rotor2;
    }

    /**
     * @return Rotor
     */
    public function getRotor3(): Rotor
    {
        return $this->rotor3;
    }

    /**
     * @return Rotor
     */
    public function getRotor4(): Rotor
    {
        return $this->rotor4;
    }

    /**
     * @return Reflector
     */
    public function getReflector(): Reflector
    {
        return $this->reflector;
    }

    /**
     * @return array
     */
    public function getPlugboard(): array
    {
        return $this->plugboard;
    }


}
