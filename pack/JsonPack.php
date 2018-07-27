<?php
namespace Pack;

use Server\Server;
use Pack\IPack;

class JsonPack implements IPack{
    protected $last_data;
    protected $last_data_result;


    public function pack($data){
        if ($this->last_data != null && $this->last_data == $data) {
            return $this->last_data_result;
        }
        $this->last_data = $data;
        $this->last_data_result = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $this->last_data_result;
    }

    public function unPack($data){
        $value = json_decode($data);
        if (empty($value)) {
            throw new \Exception('Json unPack failed');
        }
        return $value;
    }

    public function encode($buffer){

    }

    public function decode($buffer){

    }

    public function getProbufSet(){
        return [];
    }

    public function errorHandle($e, $fd){
		Server::$application->send($fd, "Error:" . $e->getMessage(), true);
		Server::$application->close($fd);
    }
}