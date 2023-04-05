<?php
require_once("../identity/jwt.php");
require_once("../database.php");
require_once("enigma.php");
require_once("../web_tools/http.php");

header("Content-Type: application/json; charset=UTF-8");

$token = validate_jwt();

switch ($_SERVER["REQUEST_METHOD"]) {
    case "PUT":
    {
        $data = get_raw_json();

        $enigma = create_enigma($data['enigma status']);

        $encrypted = encrypt_text($data['text'], $enigma);

        $database = DatabaseProvider::get_database();
        $database->connect();

        $status = json_encode($data['enigma status']);

        $result = $database->sql_query('insert into cyphertexts (name, author, text, encrypted, code) values ($1, $2, $3, $4, $5)',
            array($data['name'], $token['name'], $data['text'], $encrypted, $status));

        if (!$result) {
            $error = $database->get_error();
            if (str_contains($error, 'foreign key')) {
                http_response_code(400);
                echo json_encode(array("message" => "user not found"));
            } else if (str_contains($error, "duplicate key")) {
                http_response_code(400);
                echo json_encode(array("message" => "cyphertext with that name already exists"));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => "unknown error"));
            }
            break;
        }

        http_response_code(200);
        echo json_encode(array("message" => "cyphertext created successfully"));
        break;
    }
    case 'GET':
    {
        if (!isset($_GET['id'])){
            http_response_code(400);
            echo json_encode(array("message" => "no id provided"));
        }
    }
    default:
    {
        echo json_encode(array("message" => "not supported request method"));
        http_response_code(405);
        die;
    }
}

$database->close();

/**
 * @param $enigma_status
 * @return Enigma
 */
function create_enigma($enigma_status): Enigma
{
    try {
        $rotor1 = new Rotor($enigma_status['rotor1']['position'], $enigma_status['rotor1']['type']);
        $rotor2 = new Rotor($enigma_status['rotor2']['position'], $enigma_status['rotor2']['type']);
        $rotor3 = new Rotor($enigma_status['rotor3']['position'], $enigma_status['rotor3']['type']);
        $rotor4 = new Rotor($enigma_status['rotor4']['position'], $enigma_status['rotor4']['type']);
        $reflector = new ReflectorEnigma($enigma_status['reflector']['type']);
        $plugboard = array();

        foreach ($enigma_status['plugboard'] as $key => $plug) {
            array_push($plugboard, new Plug($plug['pin1'], $plug['pin2']));
        }
    } catch (Throwable $exception) {
        http_response_code(400);
        echo json_encode(array("message" => "failed to generate enigma with provided parameters"));
        die;
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
