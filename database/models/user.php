<?php
require("model.php");
class User implements IModel{
    private $name = null;
    private $email = null;
    private $password = null;

    public function __construct($name, $email, $password, $is_hash = false){
        $this->name = $name;
        if (!$is_hash){
            $this->set_password($password);
        }
        else {
            $this->password = $password;
        }
        $this->email = $email;
    }

    public function get_name(){
        return $this->name;
    }

    public function get_email(){
        return $this->email;
    }

    public function get_password(){
        return $this->password;
    }

    public function set_name($name){
        $this->name = $name;
    }

    public function set_password($password){
        $this->password = password_hash($password, null);
    }

    public static function fetch($data): User
    {
        return new User($data["name"], $data["email"], $data["password"], true);
    }

    public static function get_from_database($database, $email)
    {
        $response = $database->sql_query('select * from users where email = $1', array($email));
        $data = $database->get_array($response);

        if (count($data) == 1)
            return User::fetch($data[0]);
        elseif (count($data) > 1)
            return -1;

        return null;
    }
}
