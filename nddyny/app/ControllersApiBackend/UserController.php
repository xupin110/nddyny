<?php
namespace app\Controllers\nddyny\ControllersApiBackend;

use R;
use app\Controllers\nddyny\Common\ControllerApiBackend;

class UserController extends ControllerApiBackend
{

    public function login()
    {
        $post = $this->post();
        $account = param_string($post, 'account', true);
        $code = param_string($post, 'password', true);
        if (($result = R::isSuccess($Result = $this->loader->model('nddyny\User\AuthTokenUser', $this)->login($account, $code))) === R::FALSE) {
            return $this->render($Result);
        }
        $result['user_info'] = [
            'user_id' => $result['user_info']['user_id'],
            'nickname' => $result['user_info']['nickname'],
            'role' => $result['user_info']['role'],
        ];
        return $this->render(R::success($result));
    }
}