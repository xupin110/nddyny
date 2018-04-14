<?php
namespace app\Controllers\nddyny\ControllersApi;

use R;
use app\Controllers\nddyny\Common\ControllerApi;

class IndexController extends ControllerApi
{

    public function index()
    {
        $this->render(R::success());
    }
}