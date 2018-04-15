<?php
namespace nddyny\app;

use R;
use Server\Asyn\TcpClient\SdTcpRpcPool;
use Server\Components\Process\ProcessManager;
use app\Controllers\nddyny\Process\ManageProcess;
use app\Controllers\nddyny\Common\Process;
use Server\SwooleDistributedServer;

/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-9-19
 * Time: 下午2:36
 */
abstract class AppServer extends SwooleDistributedServer
{

    public $process_table;
    public $process_not_use_table;
    public $process_not_use_lock;

    public $process_webdriver_pid = [];
    public $process_webdriver_lock;

    public function getWebdriverPid($process_id) {
        return $this->process_webdriver_pid[$process_id] ?? null;
    }

    public function setWebdriverPid($process_id, $pid) {
        $this->process_webdriver_pid[$process_id] = $pid;
    }

    public function onOpenServiceInitialization()
    {
        parent::onOpenServiceInitialization();
        /**
         * 装载app列表到缓存 - web用的
         */
        $this->mysql_pool->installDbBuilder();
        $this->loader->model('nddyny\Table\AppTable', $this)->setList();
        exec("ps -ef|grep 'phantomjs --ssl-protocol=any' | awk '{print $2}' | xargs kill");
        exec('rm -rf ' . NDDYNY_DIR . '/tmp/*');
    }

    public function initAsynPools($workerId)
    {
        parent::initAsynPools($workerId);
        foreach (config_get('servers') as $server_key => $server_info) {
            $ip = $server_info['ip'];
            if ($ip != config_get('swoole.ip')) {
                $this->addAsynPool($server_key, new SdTcpRpcPool($this->config, 'consul', $ip . ':' . config_get('tcp.port')));
            }
        }
    }

    public function startProcess()
    {
        parent::startProcess();
        for($key = 0; $key < config_get('process.amount'); $key++) {
            ProcessManager::getInstance()->addProcess(ManageProcess::class, $key, [
                'key' => $key
            ]);
        }
    }

    public function beforeSwooleStart()
    {
        parent::beforeSwooleStart();
        $this->process_not_use_lock = new \swoole_lock(SWOOLE_MUTEX);
        $this->process_webdriver_lock = new \swoole_lock(SWOOLE_MUTEX);
        $this->process_table = new \swoole_table(1024);
        $this->process_table->column('name', \swoole_table::TYPE_STRING, 255);
        $this->process_table->column('pid', \swoole_table::TYPE_INT, 8);
        $this->process_table->column('exit_loop', \swoole_table::TYPE_STRING, 1);
        $this->process_table->column('input', \swoole_table::TYPE_STRING, 255);
        $this->process_table->create();
        $this->process_not_use_table = new \swoole_table(1024);
        $this->process_not_use_table->create();
        for($key = 0; $key < config_get('process.amount'); $key++) {
            $this->process_not_use_table->set($key, []);
        }
    }

    public function createProcess(Process $Process, $process_amount = 1) {
        $this->process_not_use_lock->lock();
        if($process_amount > ($count_process = count($this->process_not_use_table))) {
            $this->process_not_use_lock->unlock();
            throw new \NddynyException(R::fail("需要{$process_amount}个进程, {$count_process}个进程可用"));
        }
        $keys = [];
        while ($process_amount --) {
            foreach($this->process_not_use_table as $key => $info) {
                $this->process_not_use_table->del($key);
                $keys[] = $key;
                break;
            }
        }
        $this->process_not_use_lock->unlock();
        try {
            foreach($keys as $key) {
                ProcessManager::getInstance()->getRpcCall(ManageProcess::class . $key, true)->run($Process);
            }
            return $keys;
        }catch(\Exception $e) {
            $message = $e->getMessage() == '不存在app\Controllers\nddyny\Process\Process 进程' ? '没有可用的进程了' : $e->getMessage();
            throw new \NddynyException(R::fail($message));
        }
    }

    public function getProcessList($name)
    {
        $tasks = [];
        foreach ($this->process_table as $key => $row) {
            if ($row['name'] == $name) {
                $tasks[$key] = $row;
            }
        }
        return $tasks;
    }

    public function stopProcess15($pid)
    {
        posix_kill($pid, SIGTERM);
    }

    public function stopProcess9($pid)
    {
        posix_kill($pid, SIGKILL);
    }
}