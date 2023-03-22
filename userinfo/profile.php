<?php
require_once("../identity/jwt.php");
require_once("../database.php");
require_once("../database/models/user.php");

header("Content-Type: application/json; charset=UTF-8");

$token = validate_jwt();

switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":
    {
        echo json_encode($token);
        http_response_code(200);
        exit;
    }
    case "PUT":
    {
        $json = get_json_from_stream(fopen("php://input", "r"));

        hasPassword($json);

        $database = DatabaseProvider::get_database();
        $database->connect();

        $user = User::get_from_database($database, $token["email"]);

        update_profile($database, $user, $json);
    }
    case "DELETE":{
        $json = get_json_from_stream(fopen("php://input", "r"));

        hasPassword($json);

        $database = DatabaseProvider::get_database();
        $database->connect();

        $user = User::get_from_database($database, $token["email"]);
        delete_profile($database, $user, $json["password"]);
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

function hasPassword($json)
{
    if (isset($json["password"]) && $json["password"] != "")
        return;

    echo json_encode(array("message" => "password required"));
    http_response_code(401);
    die;
}

function update_profile($database, $user, $data){
    if ($user == null) {
        $database->close();
        echo json_encode(array("message" => "user not found"));
        http_response_code(404);
        die;
    }

    $password = $data["password"];

    password_verification($password, $user, $database);

    if (isset($data["name"]))
        $user->set_name($data["name"]);

    if (isset($data["new password"]))
        $user->set_password($data["new password"]);

    $response = $database->sql_query('update users set name =  $1, password = $2 where email = $3',
        array($user->get_name(), $user->get_password(), $user->get_email()));

    if (!$response) {
        $database->close();
        echo json_encode(array("message" => "Пользователь с таким именем уже существует"));
        http_response_code(409);
        die;
    }

    $token = generate_token($user);

    setcookie("token", $token, time() + 60 * 30, '/');
    $database->close();

    echo json_encode(array("message" => "profile updated successfully"));
    http_response_code(200);
    exit;
}

function password_verification($password, $user, $database): void
{
    if (!password_verify($password, $user->get_password())) {
        $database->close();
        echo json_encode(array("message" => "provided password is not valid"));
        http_response_code(403);
        die;
    }
}

function delete_profile($database, $user, $password){
    if ($user == null) {
        $database->close();
        echo json_encode(array("message" => "user not found"));
        http_response_code(404);
        die;
    }

    password_verification($password, $user, $database);

    $database->sql_query('delete from users where email = $1', array($user->get_email()));

    $database->close();
    echo json_encode(array("message" => "profile deleted successfully"));
    http_response_code(200);
    exit;
}
