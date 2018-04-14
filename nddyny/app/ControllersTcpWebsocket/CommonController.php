<?php

namespace app\Controllers\nddyny\ControllersTcpWebsocket;

use R;
use app\Controllers\nddyny\Common\ControllerTcpWebsocket;
use app\Controllers\nddyny\Common\IM;

class CommonController extends ControllerTcpWebsocket
{

    public function i404()
    {
        return $this->render(R::error(null, R::METHOD_NOTFOUND));
    }

    public function heartbeat()
    {
        return $this->render(R::success());
    }

    public function bindUid($fd = null, $uid = null, $isKick = true)
    {
        $this->isLogin();
        new IM($this->fd, $this->uid);
        return $this->render(R::success(array_keys(config_get('servers')), null, config_get('swoole.ip')));
    }

    public function removeGroups()
    {
        $this->isLogin();
        $groups = param_array($this->data, 'groups', false, []);
        foreach($groups as $group) {
            get_instance()->removeSub($group, $this->uid);
        }
        return $this->render(R::success());
    }
}