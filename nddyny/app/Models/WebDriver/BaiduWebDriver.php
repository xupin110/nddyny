<?php

namespace app\Models\nddyny\WebDriver;

use R;
use app\Controllers\nddyny\Common\ModelWebdriver;
use app\Controllers\nddyny\Common\Process;

class BaiduWebDriver extends ModelWebdriver
{

    private $login_url = 'https://passport.baidu.com/v2/?login';

    private $home_url = 'https://passport.baidu.com/center';

    public function index(Process $Process)
    {
        $this->init($Process);
        $this->phantomjs(true, true);
        if (R::noSuccess($Result = $this->login())) {
            return $Result;
        }
        return R::success();
    }

    private function login()
    {
        return $this->baseLogin($this->login_url, $this->home_url, function () {
            $this->takeScreenshot('#TANGRAM__PSP_3__qrcode');
            $this->process->renderGroup(R::none('请扫码，然后随便输个东西'));
            $this->input();
            return R::success();
        }, function () {
            $currentUrl = substr($this->driver->getCurrentURL(), 0, strlen($this->home_url));
            if ($currentUrl != $this->home_url) {
                return R::fail("当前地址: $currentUrl, 期望地址: $this->home_url");
            }
            return R::success();
        });
    }

    protected function getCookieRedisKey()
    {
        return 'webdriver.baidu';
    }
}