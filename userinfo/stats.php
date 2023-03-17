<?php
require_once("../identity/jwt.php");
require_once("../database.php");
header("Content-Type: application/json; charset=UTF-8");


if (!isset($_GET["token"])){
    http_response_code(401);
    echo json_encode(array("message" => "identity token required"));
    die;
}

$jwt_token = $_GET["token"];
$jwt_token = confirm_token($jwt_token);

if ($jwt_token == null){
    unset($_COOKIE['token']);
    setcookie('token', null, 1, '/');
    http_response_code(401);
    echo json_encode(array("message" => "invalid token signature"));
    die;
}

$expires = $jwt_token["expires"];

if ($expires < time()){
    http_response_code(401);
    echo json_encode(array("message" => "token expired"));
    die;
}

$database = DatabaseProvider::get_database();
$database->connect();

$response = $database->sql_query('SELECT *, (SELECT COUNT(*) AS "total messages" FROM cyphertexts) FROM user_stats WHERE username=$1', array($jwt_token["email"]));
$result = $database->get_array($response);

if (count($result) < 1){
    http_response_code(410);
    echo json_encode(array("message" => "User not found"));
}
else{
    http_response_code(200);
    echo json_encode($result[0]);
}

$database->close();
