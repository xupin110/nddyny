<?php
namespace app\Models;

use R;
use app\Controllers\nddyny\Common\Process;
use app\Controllers\nddyny\Common\Model;

class TestModel extends Model
{

    public function run(Process $Process)
    {
        $Process->while($Process->data['params'], function($run_times) use ($Process) {
            $Process->renderGroup(R::none('test' . $run_times));
            sleep(1);
        });
        return R::success();
    }
}