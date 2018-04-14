<?php
namespace app\Controllers\nddyny\ControllersApiBackend;

use R;
use app\Controllers\nddyny\Common\ControllerApiBackend;

class ProcessSshController extends ControllerApiBackend
{

    public function groupNames()
    {
        $this->isLogin();
        $post= $this->post();
        $app_id = param_uint($post, 'app_id', true);
        $group_names = $this->loader->model('nddyny\Table\ProcessSshTable', $this)->groupNames($app_id, STATUS_ALL);
        return $this->render(R::success($group_names));
    }
}