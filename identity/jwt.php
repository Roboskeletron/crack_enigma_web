<?php
require "../vendor/autoload.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once("../database/models/user.php");
function generate_token($user): string
{
    $key = 'some_key';
    $payload =[
        'name' => $user->get_name(),
        'email'=> $user->get_email(),
        'expires' => time() + 60 * 60
    ];

    return JWT::encode($payload, $key, 'HS256');
}

function decode_jwt($token): ?array
{
    $key = 'some_key';
    try {
        return (array)JWT::decode($token, new Key($key, 'HS256'));
    }
    catch (Exception $exception){
        return null;
    }
}

function validate_jwt(){
    if (!isset($_GET["token"])){
        http_response_code(401);
        echo json_encode(array("message" => "identity token required"));
        die;
    }

    $jwt_token = $_GET["token"];
    $jwt_token = decode_jwt($jwt_token);

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

    return $jwt_token;
}
