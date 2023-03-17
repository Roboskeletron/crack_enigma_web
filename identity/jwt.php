<?php
require "../vendor/autoload.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once("../database/models/user.php");
function generate_token($user): string
{
    $key = 'some_key';
    $payload =[
        'email'=> $user->get_email(),
        'expires' => time() + 60 * 60
    ];

    return JWT::encode($payload, $key, 'HS256');
}

function confirm_token($token): ?array
{
    $key = 'some_key';
    try {
        return (array)JWT::decode($token, new Key($key, 'HS256'));
    }
    catch (Exception $exception){
        return null;
    }
}
