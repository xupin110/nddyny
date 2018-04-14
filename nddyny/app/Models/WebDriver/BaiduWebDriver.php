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
        $driver = $this->driver;
        return $this->baseLogin($this->login_url, $this->home_url, function () use ($driver) {
            if(R::noSuccess($Result = $this->takeScreenshot('#TANGRAM__PSP_3__qrcode'))) {
                return $Result;
            }
            $this->process->renderGroup(R::none('请扫码，然后随便输个东西'));
            if(R::noSuccess($Result = $this->input())) {
                return $Result;
            }
            return R::success();
        }, function () use ($driver) {
            $currentUrl = substr($driver->getCurrentURL(), 0, strlen($this->home_url));
            if ($currentUrl != $this->home_url) {
                return R::fail("当前地址: $currentUrl, 期望地址: $this->home_url");
            }
            return R::success();
        });
    }

    protected function getCookieRedisKey()
    {
        return 'webdriver.baidu3';
    }
}