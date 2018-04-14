<?php

namespace app\Controllers\nddyny\Common;

use R;
use ResultObject;

class Process
{
    const REDIS_KEY_GROUP_UIDS = 'process.group.uids.';

    const SERVICE_BIND = 'bind';

    const SERVICE_STATUS = 'status';

    const SERVICE_CREATE = 'create';

    const SERVICE_CLOSE = 'close';

    const TASK_NORMAL = 'normal';

    const TASK_STATUS_NORMAL = 'normal';

    const TASK_STATUS_RELOAD = 'reload';

    const TASK_ACTION_CREATE = 'create';

    const TASK_ACTION_CLOSE = 'close';

    public $current_task_type = 'normal';

    public $service;

    public $process_name;

    public $server;

    public $category;

    public $params;

    public $data;

    public $process_id;

    public $key;

    /** @var IM $IM */
    public $IM;

    public function __construct(IM $IM, $data)
    {
        $this->IM = $IM;
        $this->service = param_string($data, 'service', true);
        $this->process_name = param_string($data, 'process_name', true);
        $this->server = param_string($data, 'server', true);
        $this->category = param_string($data, 'category', true);
        $this->params = param_array($data, 'params', false, []);
        $this->data = param_array($data, 'data', false, []);
    }

    public function addGroup($uid)
    {
        get_instance()->addSub($this->process_name, $uid);
    }

    public function removeGroup($uid)
    {
        get_instance()->removeSub($this->process_name, $uid);
    }

    public function renderGroup(ResultObject $Result, $isEnd = true, $destroy = false)
    {
        if(isset($Result->message) && $isEnd) {
            $Result->message .= PHP_EOL;
        }
        $Result = $this->getRender($Result, $destroy);
        get_instance()->pub($this->process_name, $Result->result());
    }

    public function getRender(ResultObject $Result, $destroy = true)
    {
        $Result->destroy = $destroy;
        $Result->attach = $this->IM->attach;
        $Result->attach['process_key'] = $this->key;
        return $Result;
    }

    public function while($max_run_times, $callback)
    {
        $run_times = 0;
        while (true) {
            if (++$run_times > $max_run_times) {
                break;
            }
            if (get_instance()->process_table->get($this->process_id, 'exit_loop') == IS_TRUE) {
                break;
            }
            if (R::isSelf($Result = $callback($run_times))) {
                return $Result;
            }
        }
        return R::success();
    }
}