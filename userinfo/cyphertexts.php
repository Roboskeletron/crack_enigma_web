<?php
require_once("../identity/jwt.php");
require_once("../database.php");
header("Content-Type: application/json; charset=UTF-8");

$jwt_token = validate_jwt();

$database = DatabaseProvider::get_database();

$database->connect();

$response = $database->sql_query('select id, name, author, "total attempts", 
"successful attempts" from cyphertexts where author<>$1', array($jwt_token["name"]));

$texts = $database->get_array($response);

http_response_code(200);
echo json_encode($texts);

$database->close();
