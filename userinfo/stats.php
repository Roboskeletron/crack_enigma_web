<?php
require_once("../identity/jwt.php");
require_once("../database.php");
header("Content-Type: application/json; charset=UTF-8");


$jwt_token = validate_jwt();

$database = DatabaseProvider::get_database();
$database->connect();

$response = $database->sql_query('SELECT name, "total attempts", "successful attempts" FROM cyphertexts WHERE author=$1', array($jwt_token["name"]));
$result = $database->get_array($response);

if (count($result) < 1){
    http_response_code(410);
    echo json_encode(array("message" => "User not found"));
}
else{
    http_response_code(200);
    echo json_encode(array("name" => $jwt_token["name"], "cyphertexts" => $result));
}

$database->close();
