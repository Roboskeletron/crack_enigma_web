<?php
require_once("../identity/jwt.php");
require_once("../database.php");
require_once("enigma.php");
require_once("../web_tools/http.php");
require_once("../database/models/cyphertext.php");

header("Content-Type: application/json; charset=UTF-8");

$token = validate_jwt();

$database = DatabaseProvider::get_database();
$database->connect();

switch ($_SERVER["REQUEST_METHOD"]) {
    case "PUT":
    {
        $data = get_raw_json();

        create_cyphertext($database, $data, $token);
        break;
    }
    case 'GET':
    {
        send_cyphertext($database, $token);
        break;
    }
    case 'POST':
    {
        switch ($_GET['action']) {
            case 'modify':
            {
                $id = getId();
                $cyphertext = Cyphertext::fetch_by_id($database, $id);

                if (!check_author($cyphertext, $token))
                    break;

                $data = get_raw_json();

                update_cyphertext($database, $data, $id);

                break;
            }
            case 'crack':
            {
                $id = getId();
                $cyphertext = Cyphertext::fetch_by_id($database, $id);

                if (check_author($cyphertext, $token, false)){
                    response_with_message(403,
                        'You cant crack your own cyphertext');
                    break;
                }

                $data = get_raw_json();

                crack_cyphertext($database, $cyphertext, $data);

                break;
            }
            default:
            {
                response_with_message(405, 'not supported action');
                break;
            }
        }
        break;
    }
    case 'DELETE':
    {
        $id = getId();
        $cyphertext = Cyphertext::fetch_by_id($database, $id);

        if (!check_author($cyphertext, $token))
            break;

        delete_cyphertext($id, $database);

        break;
    }
    default:
    {
        response_with_message(405, "not supported request method");
        break;
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

function check_query_result($result, $database): bool
{
    if (!$result) {
        $error = $database->get_error();
        if (str_contains($error, 'foreign key')) {
            response_with_message(403, "user not found");
        } else if (str_contains($error, "duplicate key")) {
            response_with_message(400, "cyphertext with that name already exists");
        } else {
            response_with_message(500, "unknown error");
        }
        return false;
    }

    return true;
}

function create_cyphertext($database, $data, $token): bool
{
    $enigma = create_enigma($data['enigma status']);

    $encrypted = encrypt_text($data['text'], $enigma);

    $status = json_encode($data['enigma status']);

    $result = $database->sql_query('insert into cyphertexts (name, author, text, encrypted, code) values ($1, $2, $3, $4, $5)',
        array($data['name'], $token['name'], $data['text'], $encrypted, $status));

    if (!check_query_result($result, $database))
        return false;

    $result = $database->sql_query('select id from cyphertexts where author = $1 and name = $2',
        array($token['name'], $data['name']));

    $data = $database->get_array($result)[0];

    response_with_array(200, array("id" => $data['id'], "message" => "cyphertext created successfully"));
    return true;
}

function update_cyphertext($database, $data, $id)
{
    $enigma = create_enigma($data['enigma status']);

    $encrypted = encrypt_text($data['text'], $enigma);

    $status = json_encode($data['enigma status']);

    $result = $database->sql_query('update cyphertexts  set text = $2, encrypted = $3, code = $4 where id = $1',
        array($id, $data['text'], $encrypted, $status));

    if (!check_query_result($result, $database))
        return false;

    response_with_message(200, 'cyphertext updated successfully');
}

function send_cyphertext($database, $token)
{
    $id = getId();

    if ($id == null)
        return false;

    $cyphertext = Cyphertext::fetch_by_id($database, $id);

    if ($cyphertext == null)
        return false;

    if ($cyphertext->getAuthor() == $token['name']) {
        $arr = array("id" => $cyphertext->getId(), "name" => $cyphertext->getName(), "text" => $cyphertext->getText(),
            "code" => $cyphertext->getCode());
    } else
        $arr = array("id" => $cyphertext->getId(), "name" => $cyphertext->getName(), "encrypted" => $cyphertext->getEncrypted());

    response_with_array(200, $arr);
    return true;
}

/**
 * @return mixed
 */
function getId()
{
    if (!isset($_GET['id'])) {
        response_with_message(400, "no id provided");
        return null;
    }

    return $_GET['id'];
}

function check_author($cyphertext, $token, $response = true): bool
{
    if ($cyphertext->getAuthor() != $token['name']) {
        if ($response)
            response_with_message(403,
                'cant modify cyphertext, which doesnt belong to user');

        return false;
    }

    return true;
}

function delete_cyphertext($id, $database)
{
    $result = $database->sql_query('delete from cyphertexts where id = $1',
        array($id));

    if (!check_query_result($result, $database))
        return;

    response_with_message(200, 'cyphertext deleted successfully');
}

function crack_cyphertext($database, $cyphertext, $data){
    $code = $data['enigma status'];

    if ($cyphertext->getCode() == $code){

    }
}
