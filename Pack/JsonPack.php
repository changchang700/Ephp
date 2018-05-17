<?php
namespace Pack;

use Components\Console\Console;
class JsonPack implements IPack{
    protected $last_data;
    protected $last_data_result;


    public function pack($data, $topic = null){
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
            throw new \Exception('json unPack 失败');
        }
        return $value;
    }

    public function encode($buffer){

    }

    public function decode($buffer){

    }

    public function getProbufSet(){
        return null;
    }

    public function errorHandle($e, $fd){
		Console::warning("unpack fail");
    }
}