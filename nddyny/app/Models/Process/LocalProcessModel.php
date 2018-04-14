<?php
namespace app\Models\nddyny\Process;

use R;
use app\Controllers\nddyny\Common\Model;
use app\Controllers\nddyny\Common\Process;

class LocalProcessModel extends Model
{

    public function bind(Process $Process)
    {
        $Process->addGroup($Process->IM->uid);
        return R::success();
    }

    public function status(Process $Process)
    {
        return R::success([
            'process_amount' => count(get_instance()->getProcessList($Process->process_name))
        ]);
    }

    public function create(Process $Process)
    {
        $process_amount = param_pint($Process->params, 'process_amount', false, 1);
        return R::success(get_instance()->createProcess($Process, $process_amount));
    }

    public function close(Process $Process)
    {
        $process_amount = param_uint($Process->params, 'process_amount', false, 1);
        $type = param_int($Process->params, 'type', true);
        $process_key = param_string($Process->params, 'process_key', false, null);
        $i = 0;
        foreach (get_instance()->getProcessList($Process->process_name) as $key => $info) {
            if(isset($process_key) && $process_key != $key) {
                continue;
            }
            if (++ $i > $process_amount) {
                break;
            }
            $pid = $info['pid'];
            if ($type == SIGKILL) {
                get_instance()->stopProcess9($pid);
                $Process->key = $key;
                $Process->renderGroup(R::success(R::SUCCESS, Process::TASK_ACTION_CLOSE), true, true);
                continue;
            }
            get_instance()->process_table->set($key, [
                'exit_loop' => IS_TRUE
            ]);
        }
        return R::success();
    }
}