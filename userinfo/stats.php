<?php
require_once("../identity/jwt.php");
require_once("../database.php");
require_once("validation.php");
header("Content-Type: application/json; charset=UTF-8");


$jwt_token = validate_jwt();

$database = DatabaseProvider::get_database();
$database->connect();

$limit = validate_limit($_GET["limit"], 30, 100);
$start_id = validate_id($_GET["item_id"]);

$response = $database->sql_query('SELECT id, name, "total attempts", "successful attempts" FROM cyphertexts WHERE author=$1 and id > $2 limit $3',
    array($jwt_token["name"], $start_id, $limit));
$result = $database->get_array($response);

http_response_code(200);
echo json_encode(array("name" => $jwt_token["name"], "cyphertexts" => $result));

$database->close();
