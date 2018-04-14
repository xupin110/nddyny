<?php
namespace app\Models\nddyny\User;

use R;

class AuthPasswordUser extends AuthUser
{

    public function verify($account, $password)
    {
        if (empty($user_info = $this->loader->model('nddyny\Table\UserTable', $this)->info($account))) {
            return $this->failLogin();
        }
        $user_id = $user_info['user_id'];
        if (R::noSuccess($Result = $this->failTImesVerify($user_id))) {
            return $Result;
        }
        if ($this->getTruePassword($password) != $user_info['password']) {
            return $this->failLogin($user_id);
        }
        $this->failTImesClear($user_id);
        return R::success($user_info);
    }

    public function getTruePassword($password)
    {
        return md5(md5($password) . config_get('salt'));
    }
}