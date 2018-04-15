<?php

namespace app\Controllers\nddyny\Process;

use R;
use Server\Components\Process\Process as ProcessServer;
use app\Controllers\nddyny\Common\Process;
use nddyny\Utils\SshUtils;
use Server\Asyn\Mysql\MysqlAsynPool;
use Server\Asyn\Redis\RedisAsynPool;
use Server\Components\Process\ProcessManager;

class ManageProcess extends ProcessServer
{
    protected $redisPool;
    protected $mysqlPool;

    public function run(Process $Process)
    {
        $pid = posix_getpid();
        $Process->process_id = $this->params['key'];
        get_instance()->process_table->set($this->params['key'], [
            'name' => $Process->process_name,
            'pid' => $pid
        ]);
        if ($Process->current_task_type == Process::TASK_STATUS_NORMAL) {
            $Process->renderGroup(R::success(null, Process::TASK_ACTION_CREATE), true, true);
        }
        $this->webdriverKill($Process);
        try {
            switch ($Process->category) {
                case 'ssh':
                    $Result = $this->ssh($Process);
                    break;
                case 'method':
                    $Result = $this->method($Process);
                    break;
                default:
                    throw new \NddynyException(R::fail('没有这个方式的进程'));
            }
        } catch (\Exception $e) {
            $Result = R::error($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
        if (R::noSuccess($Result)) {
            log_error(LOG_PROCESS_TASK, $Result);
            $Process->renderGroup($Result);
            $Result = R::success(R::FAIL, Process::TASK_ACTION_CLOSE);
        } else {
            $Result = R::success(R::SUCCESS, Process::TASK_ACTION_CLOSE);
        }
        $Process->renderGroup($Result, true, true);
        if ($Process->category == 'ssh') {
            return get_instance()->stopProcess15($pid);
        }
        $this->webdriverKill($Process);
        $this->onShutDown();
    }

    private function webdriverKill(Process $Process)
    {
        $pid = ProcessManager::getInstance()->getRpcCallWorker(0)->getWebdriverPid($Process->process_id);
        if(empty($pid)) {
            return;
        }
        $pid2 = shell_exec("ps -ef|grep 'p\hantomjs --ssl-protocol=any' | awk '{print $2}' | grep $pid");
        if(trim($pid2) == $pid) {
            shell_exec("kill {$pid}");
        }
    }

    private function ssh(Process $Process)
    {
        $data = $Process->data;
        $command = $data['run'] . ' ' . escapeshellcmd($data['uri']);
        (new SshUtils())->shell($data['address'], $data['port'], $data['account'], $data['password'], $command, function ($content) use ($Process) {
            $Process->renderGroup(R::none($content), false);
        });
        return R::success();
    }

    private function method(Process $Process)
    {
        $data = $Process->data;
        $class_name = 'app\Models\\' . $data['class_name'];
        if(!class_exists($class_name)) {
            return R::fail('找不到类: ' . $class_name);
        }
        $class = new $class_name();
        $method_name = $data['method_name'] ? : 'index';
        if(!method_exists($class, $method_name)) {
            return R::fail('找不到方法: ' . $data['method_name']);
        }
        $Result = $class->$method_name($Process);
        return $Result;
    }

    protected function onShutDown()
    {
        get_instance()->process_table->del($this->params['key']);
        get_instance()->process_not_use_table->set($this->params['key'], []);
    }

    public function start($process)
    {
        $this->onShutDown();
        $this->redisPool = new RedisAsynPool($this->config, $this->config->get('redis.active'));
        $this->mysqlPool = new MysqlAsynPool($this->config, $this->config->get('mysql.active'));
        get_instance()->addAsynPool("redisPool",$this->redisPool);
        get_instance()->addAsynPool("mysqlPool",$this->mysqlPool);
        $this->mysqlPool->installDbBuilder();
    }
}