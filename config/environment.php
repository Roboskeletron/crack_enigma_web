<?php
require "../vendor/autoload.php";
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable($_SERVER["DOCUMENT_ROOT"]);
$dotenv->safeLoad();

if (isset($_ENV["ENVIRONMENT_OVERWRITE"])){
    $dotenv = Dotenv::createImmutable($_SERVER["DOCUMENT_ROOT"], $_ENV["ENVIRONMENT_OVERWRITE"]);
    $dotenv->safeLoad();
}
