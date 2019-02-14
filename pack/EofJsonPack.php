<?php
namespace Pack;

use Server\Server;
use Pack\IPack;

class EofJsonPack implements IPack
{
    protected $package_eof = "\r\n";

    protected $last_data = null;
    protected $last_data_result = null;

    /**
     * 数据包编码
     * @param $buffer
     * @return string
     * @throws SwooleException
     */
    public function encode($buffer)
    {
        return $buffer . $this->package_eof;
    }

    /**
     * @param $buffer
     * @return string
     */
    public function decode($buffer)
    {
        $data = str_replace($this->package_eof, '', $buffer);
        return $data;
    }

    public function pack($data, $topic = null)
    {
        if ($this->last_data != null && $this->last_data == $data) {
            return $this->last_data_result;
        }
        $this->last_data = $data;
        $this->last_data_result = $this->encode(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $this->last_data_result;
    }

    public function unPack($data)
    {
        $value = json_decode($this->decode($data));
        if (empty($value)) {
            throw new \Exception('Json unPack failed');
        }
        return $value;
    }

    public function getPackSet()
    {
        return [
            'open_eof_split' => true,
            'package_eof' => $this->package_eof,
            'package_max_length' => 2000000
        ];
    }

    public function errorHandle($e, $fd)
    {
        Server::$application->send($fd, "Error:" . $e->getMessage(), true);
		Server::$application->close($fd);
    }
}