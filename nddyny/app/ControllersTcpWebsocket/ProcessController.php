<?php

namespace app\Controllers\nddyny\ControllersTcpWebsocket;

use R;
use app\Controllers\nddyny\Common\Process;
use app\Controllers\nddyny\Common\ControllerTcpWebsocket;
use app\Controllers\nddyny\Common\IM;

class ProcessController extends ControllerTcpWebsocket
{

    public function index()
    {
        $this->isLogin();
        $_uid = null; if(isset($this->rpc_request_id)) {
            $_uid = $this->uid;
        }
        $IM = new IM($this->fd, null, $this->attach, $_uid);
        $Process = new Process($IM, $this->data);
        $Result = $this->loader->model('nddyny\Process\ProcessModel', $this)->run($Process, $this->client_data);
        $Result = $Process->getRender($Result);
        return $this->render($Result);
    }
}