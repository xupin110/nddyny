<?php
namespace app\Route\nddyny;

use Server\Route\IRoute;

class NddynyRoute implements IRoute
{

    private $client_data;

    private $websocket_services = [
        '0,0' => 'Common,i404',
        '1,0' => 'Common,heartbeat',
        '1,100' => 'Common,bindUid',
        '1,101' => 'Common,removeGroups',
        '1,102' => 'Common,setUidInput',
        '2,0' => 'Process,index',
        // ----
        'wow' => 'Wow,index',
    ];

    public function __construct()
    {
        $this->client_data = new \stdClass();
    }

    public function handleClientData($request)
    {
        if (!(isset($request->service) && isset($this->websocket_services[$request->service]))) {
            $request->service = '0,0';
        }
        list ($request->controller_name, $request->method_name) = explode(',', $this->websocket_services[$request->service]);
        $request->controller_name = 'nddyny\ControllersTcpWebsocket\\' . $request->controller_name . 'Controller';
        if (!isset($request->data) || !is_object($request->data)) {
            $request->data = new \stdClass();
        }
        if (!isset($request->attach) || !is_object($request->attach)) {
            $request->attach = new \stdClass();
        }
        $request->attach->__service = $request->service;
        return $this->client_data = $request;
    }

    public function handleClientRequest($request)
    {
        $this->client_data->path = $request->server['path_info'];
        $route = explode('/', $request->server['path_info']);
        if (count($route) == 3) {
            if (empty($route[2])) {
                $route[1] .= '\Index';
                $route[2] = 'index';
            } else {
                $route[3] = 'index';
            }
        }
        $this->client_data->method_name = array_pop($route);
        $route = array_map(function ($value) {
            return ucfirst($value);
        }, $route);
        $this->client_data->controller_name = 'nddyny' . implode('\\', $route) . 'Controller';
    }

    /**
     * 获取控制器名称
     * @return string
     */
    public function getControllerName()
    {
        return $this->client_data->controller_name;
    }

    /**
     * 获取方法名称
     * @return string
     */
    public function getMethodName()
    {
        return $this->client_data->method_name;
    }

    public function getPath()
    {
        return $this->client_data->path ?? "";
    }

    public function getParams()
    {
        return $this->client_data->params ?? null;
    }

    public function errorHandle(\Exception $e, $fd)
    {
        get_instance()->send($fd, "Error:" . $e->getMessage(), true);
        get_instance()->close($fd);
    }

    public function errorHttpHandle(\Exception $e, $request, $response)
    {
        $response->header('Content-Type', 'text/html; charset=UTF-8');
        $response->end(\R::error(null, \R::METHOD_NOTFOUND)->json());
    }
}