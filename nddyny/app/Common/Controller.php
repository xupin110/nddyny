<?php

namespace app\Controllers\nddyny\Common;

use R;
use ResultObject;
use Server\CoreBase\Controller as SdController;

abstract class Controller extends SdController
{

    protected $auth_token;

    protected $user_id;

    protected $user_role;

    abstract protected function render(ResultObject $Result);

    public function defaultMethod()
    {
        $this->render(R::error(null, R::METHOD_NOTFOUND));
    }

    public function onExceptionHandle(\Throwable $e, $handle = null)
    {
        parent::onExceptionHandle($e, function (\Throwable $e) {
            $this->render($this->exceptionResult($e));
            if ($e->getCode() == R::AUTH_VERIFY_FAIL && !empty($this->fd)) {
                $this->close($this->fd);
            }
        });
    }

    protected function isLogin()
    {
        if (empty($this->auth_token) || ($user_info = R::isSuccess($Result = $this->loader->model('nddyny\User\AuthTokenUser', $this)->verify($this->auth_token))) === R::FALSE) {
            throw new \Exception(null, R::AUTH_VERIFY_FAIL);
        }
        $this->user_id = $user_info['user_id'];
        $this->user_role = $user_info['role'];
        return $user_info;
    }

    protected function isAdmin()
    {
        $user_info = $this->isLogin();
        if($this->user_role != 'admin') {
            throw new \Exception(null, R::AUTH_VERIFY_FAIL);
        }
        return $user_info;
    }

    protected function exceptionResult($e)
    {
        $message = $e->getMessage();
        $code = $e->getCode();
        if (empty($message)) {
            $message = null;
        }
        if (empty($code)) {
            $code = null;
        }
        return in_array($code, [
            R::ARGUMENT_MISSING,
            R::ARGUMENT_INVALID,
            R::AUTH_VERIFY_FAIL
        ]) ? R::fail(null, $code, $message) : R::error(null, $code, $message);
    }
}