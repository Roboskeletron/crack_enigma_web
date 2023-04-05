<?php
require_once("../database.php");
require_once("../database/models/user.php");
require_once("../web_tools/http.php");

header("Content-Type: application/json; charset=UTF-8");

$database = DatabaseProvider::get_database();
$database->connect();

$response = $database->sql_query('SELECT * FROM users WHERE email=$1 OR name=$2', 
    array($_SERVER["PHP_AUTH_USER"], $_POST["username"]));

$result = $database->get_array($response);

if (count($result) >= 1 && gettype($result) == "array"){
    $code = 401;
    foreach ($result as $user){
        if ($user["name"] == $_POST["username"]){
            response_with_message($code, "Пользователь с таким именем уже существует");
            $database->close();
            exit();
        }
    }
    response_with_array($code, array("message" => "На этот адрес уже зарегистрирован аккакунт", "Email" => $_SERVER["PHP_AUTH_USER"],
        "Username" => $_POST["username"]));
}
else{
    $hash = password_hash($_SERVER["PHP_AUTH_PW"], null);
    $database->sql_query('INSERT INTO users VALUES ($1, $2, $3)',
    array($_POST["username"], $_SERVER["PHP_AUTH_USER"], $hash));

    response_with_array(200, array("message" => "Новый пользователь успешно зарегистрирован", "Email" => $_SERVER["PHP_AUTH_USER"],
        "Username" => $_POST["username"]));
}

$database->close();
