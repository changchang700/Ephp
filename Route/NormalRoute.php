<?php
namespace Route;

use Server\Server;
class NormalRoute implements IRoute{
    private $client_data;

    public function __construct(){
        $this->client_data = new \stdClass();
    }

    /**
     * 设置反序列化后的数据 Object
     * @param $data
     * @return \stdClass
     * @throws SwooleException
     */
    public function handleClientData($data){
        $this->client_data = $data;
        if (isset($this->client_data->controller_name) && isset($this->client_data->method_name)) {
            return $this->client_data;
        } else {
            throw new \Exception('Missing required fields');
        }

    }

    /**
     * 处理http request
     * @param $request
     */
    public function handleClientRequest($request){
        $this->client_data->path = $request->server['path_info'];
        $route = explode('/', $request->server['path_info']);
        $count = count($route);
        if ($count == 2) {
            $this->client_data->controller_name = $route[$count - 1] ?? null;
            $this->client_data->method_name = null;
            return;
        }
        $this->client_data->method_name = $route[$count - 1] ?? null;
        unset($route[$count - 1]);
        unset($route[0]);
        $this->client_data->controller_name = implode("\\", $route);
    }

    /**
     * 获取控制器名称
     * @return string
     */
    public function getControllerName(){
        return $this->client_data->controller_name;
    }

    /**
     * 获取方法名称
     * @return string
     */
    public function getMethodName(){
        return $this->client_data->method_name;
    }

	/**
	 * tcp websocket错误回调
	 * @param \Exception $e
	 * @param type $fd
	 */
    public function errorHandle(\Exception $e, $fd){
        Server::$application->send($fd, "Error:" . $e->getMessage(), true);
        Server::$application->close($fd);
    }

	/**
	 * http错误回调
	 * @param \Exception $e
	 * @param type $request
	 * @param type $response
	 */
    public function errorHttpHandle(\Exception $e, $request, $response){
        $response->end('not found');
    }
}