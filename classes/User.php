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
        return $this->getToken();
    }

    private function getToken(){
        $now = new DateTime();
        $future = new DateTime("now -1 minutes");
        $jti = new Base62;
        $jti = $jti->encode(random_bytes(16));

        $secret = "secretAbc";

        $payload = [
            "jti" => $jti,
            "iat" => $now->getTimestamp(),
            "nbf" => $future->getTimestamp()
        ];

        $token = JWT::encode($payload, $secret, "HS256");
        return $token;
    }


}