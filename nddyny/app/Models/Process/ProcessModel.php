<?php

namespace app\Models\nddyny\Process;

use R;
use app\Controllers\nddyny\Common\Model;
use app\Controllers\nddyny\Common\Process;

class ProcessModel extends Model
{

    public function run(Process $Process, $client_data)
    {
        $servers = config_get('servers');
        if(!isset($servers[$Process->server])) {
            return R::fail('找不到接收方');
        }
        if (
            $servers[$Process->server]['ip'] == config_get('swoole.ip')
            || $Process->service == 'bind'
            || (defined('DEV') && isset($client_data->rpc_request_id))
        ) {
            return $this->loader->model('nddyny\Process\LocalProcessModel', $this)->{$Process->service}($Process);
        }
        $Result = get_instance()->getAsynPool($Process->server)->coroutineSend([
            'rpc_request_id' => $this->context['request_id'],
            'path' => ''
        ] + json_decode(json_encode($client_data), true));
        $Result = R::castResultObject($Result);
        if(R::noSuccess($Result)) {
            $Process->renderGroup($Result);
        }
        return $Result;
    }
}