<?php
class Postgresql implements IDatabase{
    private $database = null;
    private $connection_string = null;

    public function __construct(){
        $this->connection_string = $_ENV["POSTGRESQL"];
    }


    public function sql_query($query, $params){
        return pg_query_params($this->database, $query, $params);
    }
    
    public function connect(){
        $this->database = pg_connect($this->connection_string) or die("Can't connect to database".pg_last_error());
    }

    public function close(){
        pg_close($this->database);
    }

    public function get_error(): string
    {
        return pg_last_error($this->database);
    }

    public function get_array($response) : array
    {
        $result = pg_fetch_all($response);
        return $result ?: array();
    }
}
