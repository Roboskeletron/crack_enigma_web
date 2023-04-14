<?php

class Mysql implements IDatabase
{
    private $database = null;
    private $connection_string = null;

    public function __construct()
    {
        $this->connection_string = $_ENV["MYSQL"];
    }

    public function sql_query($query, $params)
    {
        $param_array = array();
        $new_query = '';
        $types = '';

        foreach (explode(' ', $query) as $word) {
            $position = strpos($word, '$');
            if (gettype($position) == "integer"){
                $index = intval(substr($word, $position + 1)) - 1;
                array_push($param_array, $params[$index]);
                $new_query = $new_query.' '.substr($word, 0, $position).' '.'?';
                $types = $types.'s';
                continue;
            }

            $new_query = $new_query.' '.$word;
        }

        $statement = $this->database->prepare($new_query);

        $statement->execute($param_array);
        return $statement;
        // TODO: Implement sql_query() method.
    }

    public function connect()
    {
        $params = explode(' ', $this->connection_string);
        $host = explode('=', $params[0])[1];
        $port = explode('=', $params[1])[1];
        $name = explode('=', $params[2])[1];
        $user = explode('=', $params[3])[1];
        $password = explode('=', $params[4])[1];

        $dst = 'mysql:host='.$host.';dbname='.$name.';port='.$port;
        $this->database = new PDO($dst, $user, $password);
        // TODO: Implement connect() method.
    }

    public function close()
    {
        $this->database = null;
        // TODO: Implement close() method.
    }

    public function get_error()
    {
        return $this->database->errorInfo();
        // TODO: Implement get_error() method.
    }

    public function get_array($response)
    {
        return $response->fetchAll();
        // TODO: Implement get_array() method.
    }
}