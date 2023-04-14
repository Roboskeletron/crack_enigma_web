<?php
require_once "config/environment.php";
interface IDatabase {
    public function sql_query($query, $params);
    public function connect();
    public function close();
    public function get_error();
    public function get_array($response);
}

class DatabaseProvider{
    //private static $database = getenv("DATABASE");
    private static $database = null;

    public static function get_database(){
        self::$database = $_ENV["DATABASE"];
        switch (self::$database) {
            case "POSTGRESQL":
                require("database/postgresql.php");
                return  new Postgresql();

            case "MYSQL":
                require("database/Mysql.php");
                return new Mysql();
            default:
                die("Database provider couldn't find any implementation of ".self::$database);
        }
    }
}
