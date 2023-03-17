<?php
interface IDatabase {
    public function sql_query($query, $params);
    public function connect();
    public function close();
    public function get_error();
    public function get_array($response);
}

class DatabaseProvider{
    //private static $database = getenv("DATABASE");
    private static $database = "POSTGRESQL";

    public static function get_database(){
        switch (self::$database) {
            case "POSTGRESQL":
                require("database/postgresql.php");
                return  new Postgresql();

            default:
                die("Database provider couldn't find any implementation of ".self::$database);
        }
    }
}
