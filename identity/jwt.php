<?php
require "../vendor/autoload.php";
require_once "../web_tools/http.php";
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
    if (!isTokenSet()){
        response_with_message(401, "identity token required");
        die;
    }

    $jwt_token = $_GET["token"];
    $jwt_token = decode_jwt($jwt_token);

    if ($jwt_token == null){
        unset($_COOKIE['token']);
        setcookie('token', null, 1, '/');
        response_with_message(401, "invalid token signature");
        die;
    }

    $expires = $jwt_token["expires"];

    if ($expires < time()){
        response_with_message(401, "token expired");
        die;
    }

    return $jwt_token;
}

function isTokenSet() : bool {
    return isset($_GET["token"]) && $_GET["token"] != "undefined";
}
