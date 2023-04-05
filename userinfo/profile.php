<?php
require_once("../identity/jwt.php");
require_once("../database.php");
require_once("../database/models/user.php");
require_once("../web_tools/http.php");

header("Content-Type: application/json; charset=UTF-8");

$token = validate_jwt();

switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":
    {
        response_with_array(200, $token);
        exit;
    }
    case "PUT":
    {
        $json = get_raw_json();

        hasPassword($json);

        $database = DatabaseProvider::get_database();
        $database->connect();

        $user = User::get_from_database($database, $token["email"]);

        update_profile($database, $user, $json);
    }
    case "DELETE":{
        $json = get_raw_json();

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

function hasPassword($json)
{
    if (isset($json["password"]) && $json["password"] != "")
        return;

    response_with_message(401, "password required");
    die;
}

function update_profile($database, $user, $data){
    if ($user == null) {
        $database->close();
        response_with_message(404, "user not found");
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
        response_with_message(409, "Пользователь с таким именем уже существует");
        die;
    }

    $token = generate_token($user);

    setcookie("token", $token, time() + 60 * 30, '/');
    $database->close();

    response_with_message(200, "profile updated successfully");
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
        response_with_message(404, "user not found");
        die;
    }

    password_verification($password, $user, $database);

    $database->sql_query('delete from users where email = $1', array($user->get_email()));

    $database->close();
    response_with_message(200, "profile deleted successfully");
    exit;
}
