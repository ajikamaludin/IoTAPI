<?php
use \Tuupola\Base62;
use \Firebase\JWT\JWT;

class User 
{
    private $id;

    private $username;

    private $password;

    private $db;

    public function __construct($mDb, $id = null){
        $this->db = $mDb;
        $this->id = $id;
    }

    public function __set( $mKey , $value ){
        $this->$mKey = $value;
    }

    public function __get($key){
        return $this->$key;
    }

    public function login(){

        $encryptPassword = md5($this->password);

        $query = "SELECT `users`.`username`,`users`.`password` FROM `users` WHERE 
        `users`.`username` = '$this->username' AND `users`.`password` = '$encryptPassword'";

        $user = $this->db->query($query);

        $row = $user->rowCount();

        if($row == 1){
            return $this->getToken();
        }else{
            return "auth not acceptable";
        }

    }

    private function getToken(){
        $now = new DateTime();
        $future = new DateTime("now -1 minutes");
        $jti = new Base62;
        $jti = $jti->encode(random_bytes(16));

        $secret = getenv('API_KEY');

        $payload = [
            "jti" => $jti,
            "iat" => $now->getTimestamp(),
            "nbf" => $future->getTimestamp()
        ];

        $token = JWT::encode($payload, $secret, "HS256");
        return $token;
    }


}