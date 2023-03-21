<?php
require_once("../identity/jwt.php");
require_once("../database.php");
require_once("validation.php");
header("Content-Type: application/json; charset=UTF-8");

$name = "null";

if (isTokenSet())
    $name = validate_jwt()["name"];

$database = DatabaseProvider::get_database();

$database->connect();

$limit = validate_limit(intval($_GET["limit"]), 30, 100);
$start_id = validate_id(intval($_GET["item_id"]));


$response = $database->sql_query('select id, name, author, "total attempts", 
"successful attempts" from cyphertexts where author<>$1 and id > $2 limit $3',
    array($name, $start_id, $limit));

$texts = $database->get_array($response);

http_response_code(200);
echo json_encode($texts);

$database->close();
