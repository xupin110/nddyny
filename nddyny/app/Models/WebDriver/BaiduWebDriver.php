<?php

namespace app\Models\nddyny\WebDriver;

use R;
use app\Controllers\nddyny\Common\IM;
use app\Controllers\nddyny\Common\ModelWebdriver;

class BaiduWebDriver extends ModelWebdriver
{

    private $username = '';

    private $password = '';

    private $login_url = 'https://passport.baidu.com/v2/?login';

    private $home_url = 'https://passport.baidu.com/center';

    public function index(IM $im)
    {
        $this->init($im);
        $this->phantomjs(true, true);
        if (R::noSuccess($Result = $this->login())) {
            return $Result;
        }
        $title = $this->driver->getTitle();
        $url = $this->driver->getCurrentURL();
        return R::success([
            $title, $url
        ]);
    }

    private function login()
    {
        $driver = $this->driver;
        return $this->baseLogin($this->login_url, $this->home_url, function () use ($driver) {
            $this->waitVisibility('#TANGRAM__PSP_3__userName');
            $this->findElement('#TANGRAM__PSP_3__userName')->sendKeys($this->username);
            $this->waitVisibility('#TANGRAM__PSP_3__password');
            $this->findElement('#TANGRAM__PSP_3__password')->sendKeys($this->password);
            $this->findElement('#TANGRAM__PSP_3__submit')->click();
            sleep(5);
            $this->takeScreenshot('/nddyny/1.png');
            return R::success();
        }, function () use ($driver) {
            if (($currentUrl = $driver->getCurrentURL()) != $this->home_url) {
                $this->render(R::none("当前地址: $currentUrl, 期望地址: $this->home_url"));
                return R::fail();
            }
            return R::success();
        });
    }

    protected function getCookieRedisKey()
    {
        return 'test.test.test';
    }

    protected function render($data)
    {
        $this->im->send($data);
    }

    protected function renderUid($data)
    {
        $this->im->send($data);
    }
}