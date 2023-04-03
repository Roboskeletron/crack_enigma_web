<?php
require_once("../identity/jwt.php");
require_once("../database.php");
require_once("enigma.php");
header("Content-Type: application/json; charset=UTF-8");

$token = validate_jwt();

switch ($_SERVER["REQUEST_METHOD"]) {
    case "PUT":
    {
        $data = get_json_from_stream(fopen("php://input", "r"));
        $enigma = create_enigma($data['enigma status']);

        $encrypted = encrypt_text($data['text'], $enigma);

        http_response_code(200);
        echo json_encode(array("message" => $encrypted));
        break;
    }
    default:
    {
        echo json_encode(array("message" => "not supported request method"));
        http_response_code(405);
        die;
    }
}

function get_json_from_stream($stream)
{
    $text = "";
    while ($data = fread($stream, 1024)) {
        $text = $text . $data;
    }

    fclose($stream);

    return json_decode($text, true);
}

/**
 * @param $enigma_status
 * @return Enigma
 */
function create_enigma($enigma_status): Enigma
{
    $rotor1 = new Rotor($enigma_status['rotor1']['position'], $enigma_status['rotor1']['type']);
    $rotor2 = new Rotor($enigma_status['rotor2']['position'], $enigma_status['rotor2']['type']);
    $rotor3 = new Rotor($enigma_status['rotor3']['position'], $enigma_status['rotor3']['type']);
    $rotor4 = new Rotor($enigma_status['rotor4']['position'], $enigma_status['rotor4']['type']);
    $reflector = new ReflectorEnigma($enigma_status['reflector']['type']);
    $plugboard = array();

    foreach ($enigma_status['plugboard'] as $key => $plug) {
        array_push($plugboard, new Plug($plug['pin1'], $plug['pin2']));
    }

    return new Enigma($rotor1, $rotor2, $rotor3, $rotor4, $reflector, $plugboard);
}

/**
 * @param $text
 * @param Enigma $enigma
 * @return string
 */
function encrypt_text($text, Enigma $enigma): string
{
    $symbols = str_split($text, 1);
    $encrypted = '';
    $encrypted_len = 0;

    foreach ($symbols as $key => $letter) {
        $letter = strtoupper($letter);
        if (Plug::canEncode($letter)) {
            $enigma->moveForward();
            $encrypted = $encrypted . $enigma->transform($letter);
            if (++$encrypted_len % 4 == 0)
                $encrypted = $encrypted . ' ';
        }
    }
    return $encrypted;
}
