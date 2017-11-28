<?php

class Device
{
    private $mDb;

    public $id;

    public $device_name;

    public $ip_address;

    public $port;

    public $status;

    public function __construct($db, $id = null){
        $this->mDb = $db;
        $this->id = $id;
    }

    public function find($parameter = null){
        $data = $this->mDb->query("SELECT id,device_name,port,status FROM device1");
        while($result = $data->fetch()){
            $datas[] = $result;
        }
        $data = $this->toJson($datas);
        echo $data;
    }

    private function toJson($data){
        return json_encode($data);
    }

    public function save(){
        if($this->ip_address == null){
            echo "IP Address Can't be null";
        }else{
            $sql = $this->mDb->query("SELECT id,device_name,ip_address,status FROM device1 WHERE ip_address = '$this->ip_address'");
            $device = $sql->fetch();
            if($device['ip_address'] == $this->ip_address){
                echo '200';
            }else{
                $sql = $this->mDb->query("INSERT INTO `device1` (`device_name`, `ip_address`, `port`, `status`) 
                    VALUES ('$this->device_name', '$this->ip_address','$this->port', '$this->status')");
                if($sql){
                    echo "200";
                }else{
                    echo "Terjadi Kesalahan";   
                }
            }
        }
    }

    public function deviceOn(){
        $sql = $this->mDb->query("SELECT ip_address FROM device1 WHERE id = '$this->id'");
        $device = $sql->fetch();
        $this->ip_address = $device['ip_address'];
        if($this->ip_address != null){
            $result = file_get_contents('http://'.$this->ip_address.'/io'.$this->port.'On');
            if($result == '200'){
                return '200';
            }else{
                return '404';
            }
        }else{
            return '404';
        }
    }

    public function deviceOff()
    {       
        $sql = $this->mDb->query("SELECT ip_address FROM device1 WHERE id = '$this->id'");
        $device = $sql->fetch();
        $this->ip_address = $device['ip_address'];
        if($this->ip_address != null){
            $result = file_get_contents('http://'.$this->ip_address.'/io'.$this->port.'Off');
            if($result == '200'){
                return '200';
            }else{
                return '404';
            }
        }else{
            return '404';
        }
    }

    public function deviceStatus()
    {       
        $sql = $this->mDb->query("SELECT ip_address FROM device1 WHERE id = '$this->id'");
        $device = $sql->fetch();
        $this->ip_address = $device['ip_address'];
        if($this->ip_address != null){
            $result = file_get_contents('http://'.$this->ip_address.'/io'.$this->port.'Status');
            return $result;
        }else{
            return '404';
        }
    }

    public function delete()
    {
        $sql = $this->mDb->prepare("UPDATE `device1` SET `status` = 'notifed' WHERE `device1`.`id` = '$this->id'");
        if(!$sql->execute()){
            return '404';
        }else{
            return '200';
        }
    }

    public function Rdelete()
    {
        $sql = $this->mDb->prepare("DELETE FROM `device1` WHERE `device1`.`id` = '$this->id'");
        if(!$sql->execute()){
            return '404';
        }else{
            return '200';
        }
    }

    public function editName()
    {
        $sql = $this->mDb->query("UPDATE `device1` SET `device_name` = '$this->device_name' WHERE `device1`.`id` = '$this->id'");
        if(!$sql){
            return "404";
        }else{
            return "200";
        }
    }

    public function editStatus()
    {
        $sql =  $this->mDb->query("UPDATE `device1` SET `status` = 'added' WHERE `device1`.`id` = '$this->id';");
        if(!$sql){
            return "404";
        }else{
            return "200";
        }
    }
}