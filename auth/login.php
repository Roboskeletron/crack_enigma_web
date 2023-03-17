<?php
require_once("../identity/jwt.php");
require_once("../database.php");
require_once("../database/models/user.php");
header("Content-Type: application/json; charset=UTF-8");

$database = DatabaseProvider::get_database();
$database->connect();

$response = $database->sql_query('SELECT * FROM users WHERE email=$1', 
    array($_SERVER["PHP_AUTH_USER"]));

$result = $database->get_array($response);

if (count($result) == 1){
    $user = User::fetch($result[0]);

    if (password_verify($_SERVER["PHP_AUTH_PW"], $user->get_password())){
        $jwt = generate_token($user);
        setcookie("token", $jwt, time() + 60 * 30, '/');
        http_response_code(200);
        echo json_encode(array("message" => "Loged successfully"));
    }
    else {
        http_response_code(401);
        echo json_encode(array("message" => "Incorrect login or password"));
    }
}

$database->close();
