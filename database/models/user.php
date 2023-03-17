<?php
require("model.php");
class User implements IModel{
    private $name = null;
    private $email = null;
    private $password = null;

    public function __construct($name, $email, $password, $is_hash = true){
        $this->name = $name;
        if ($is_hash){
            set_password($password);
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

    public static function fetch($data){
        return new User($data["name"], $data["email"], $data["password"], false);
    }
}
?>