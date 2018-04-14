<?php
namespace app\Models\nddyny\User;

use R;
use nddyny\Utils\GoogleAuthenticatorUtils;

class AuthTokenUser extends AuthUser
{

    public function verify($auth_token)
    {
        $auth_token = explode(',', $auth_token);
        $hash = param_string($auth_token, 0, true, '', R::AUTH_VERIFY_FAIL);
        $expires = param_string($auth_token, 1, true, '', R::AUTH_VERIFY_FAIL);
        $account = param_string($auth_token, 2, true, '', R::AUTH_VERIFY_FAIL);
        if (time() > $expires) {
            return R::fail();
        }
        if (empty($user_info = $this->loader->model('nddyny\Table\UserTable', $this)->info($account))) {
            return R::fail();
        }
        if (! password_verify($this->pureAuthToken($user_info['user_id'], $user_info['google_auth_secret'], $expires), $hash)) {
            return R::fail();
        }
        return R::success($user_info);
    }

    public function login($account, $code)
    {
        if (empty($user_info = $this->loader->model('nddyny\Table\UserTable', $this)->info($account))) {
            return $this->failLogin();
        }
        $user_id = $user_info['user_id'];
        $secret = $user_info['google_auth_secret'];
        $redis_user_auth_token_key = self::REDIS_USER_AUTH_TOKEN_ . $user_id . '_' . $code;
        if (R::noSuccess($Result = $this->failTImesVerify($user_id))) {
            return $Result;
        }
        if (IS_TRUE == $this->redis_pool->getCoroutine()->get($redis_user_auth_token_key)) {
            return R::fail('已使用此密码登录过');
        }
        if(!defined('DEV') || $account != 'testtt') {
            $GoogleAuthenticatorUtils = new GoogleAuthenticatorUtils();
            if (! $GoogleAuthenticatorUtils->verifyCode($secret, $code, 0)) {
                return $this->failLogin($user_id);
            }
        }
        $this->failTImesClear($user_id);
        // 设置code已被使用和此设置的过期时间
        $this->redis_pool->getCoroutine()->set($redis_user_auth_token_key, IS_TRUE);
        $this->redis_pool->getCoroutine()->expire($redis_user_auth_token_key, ceil(time() / 30) * 30 - time());
        // 设置登录过期时间
        $expires = time() + 3600 * 24 * 7;
        $hash = password_hash($this->pureAuthToken($user_id, $secret, $expires), PASSWORD_DEFAULT);
        return R::success([
            'auth_token' => $hash . ',' . $expires . ',' . $account,
            'user_info' => $user_info
        ]);
    }

    private function pureAuthToken($user_id, $secret, $expires)
    {
        return md5($user_id * 2 + 88) . $expires . md5($secret);
    }
}