<?php

namespace app\Controllers\nddyny\Common;

use R;
use ResultObject;
use Server\Memory\Cache;

class IM
{

    public $uid;

    public $attach;

    public function __construct($fd, $uid = null, $attach = [], $_uid = null)
    {
        if(isset($_uid)) {
            $uid = $_uid;
        } else if (isset($uid)) {
            // $uid有值，强制绑定
            $this->uid = $uid;
            get_instance()->bindUid($fd, $this->uid);
        } else if (!($uid = get_instance()->getUidFromFd($fd))) {
            // $uid无值，并且当前连接未绑定，抛出异常
            throw new \Exception('未绑定通讯身份', R::AUTH_VERIFY_FAIL);
        }
        $this->uid = $uid;
        $this->attach = $attach;
    }

    public function send(ResultObject $Result, $isAddAttach = true)
    {
        if($isAddAttach) {
            $Result->attach = $this->attach;
        }
        get_instance()->sendToUid($this->uid, $Result->result());
    }

    public function isOnline()
    {
        return get_instance()->coroutineUidIsOnline($this->uid);
    }
}