<?php
require_once("../identity/jwt.php");
require_once("../database.php");
require_once("../database/models/user.php");
require_once("../web_tools/http.php");

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
        response_with_message(200, "Loged successfully");
    }
    else {
        response_with_message(401, "Incorrect login or password");
    }
}

$database->close();
