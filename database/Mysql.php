<?php

class Mysql implements IDatabase
{
    private $database = null;
    private $connection_string = null;
    private $error = null;

    public function __construct()
    {
        $this->connection_string = $_ENV["MYSQL"];
    }

    public function sql_query($query, $params)
    {
        $param_array = array();
        $new_query = '';

        foreach (explode(' ', $query) as $word) {
            $position = strpos($word, '$');
            if (gettype($position) == "integer"){
                $number = '';
                $rest_str = '';
                foreach (str_split(substr($word, $position + 1)) as $symbol){
                    $code = ord($symbol);
                    if ($code >= ord('0') && $code <= ord('9'))
                        $number .=$symbol;
                    else
                        $rest_str .= $symbol;
                }

                $index = intval($number) - 1;

                array_push($param_array, $params[$index]);
                $new_query = $new_query.' '.substr($word, 0, $position).' '.'?'.$rest_str;
                continue;
            }

            $new_query = $new_query.' '.$word;
        }

        $new_query = str_replace('"', '`', $new_query);
        $statement = $this->database->prepare($new_query);

        for ($i = 0; $i < count($param_array); $i++){
            $type = PDO::PARAM_STR;

            if (gettype($param_array[$i]) == "integer"){
                $type = PDO::PARAM_INT;
            }

            $statement->bindValue($i + 1, $param_array[$i], $type);
        }

        if (!$statement->execute())
        {
            $this->error = $statement->errorInfo();
            return false;
        }
        return $statement;
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
    }

    public function close()
    {
        $this->database = null;
    }

    public function get_error()
    {
        return $this->error[2];
    }

    public function get_array($response)
    {
        return $response->fetchAll();
    }
}