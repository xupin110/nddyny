<?php

namespace app\Models\nddyny\WebDriver;

use R;
use app\Controllers\nddyny\Common\ModelWebdriver;
use app\Controllers\nddyny\Common\Process;

class ManualWebDriver extends ModelWebdriver
{

    public function index(Process $process)
    {
        $this->input_maxtimes = 900;
        $this->init($process);
        $this->phantomjs(true, true);
        $this->whileInput();
        return R::success();
    }
    
    private function whileInput()
    {
        while (true) {
            $prompt = <<<EOF
-------------------------
请输入编号
    0.打开页面
    1.登录
    2.查找、输入
    3.查找、点击
    4.等待、查找、输入
    5.等待、查找、点击
    6.显示图片
    7.退出本次循环输入
-------------------------
EOF;
            $this->process->renderGroup(R::none($this->colorBlue($prompt)));
            $type = $this->input();
            try {
                switch ($type) {
                    case '0':
                        $this->get();
                        break;
                    case '1':
                        $this->login();
                        break;
                    case '2':
                        $this->findInput();
                        break;
                    case '3':
                        $this->findClick();
                        break;
                    case '4':
                        $this->waitFindInput();
                        break;
                    case '5':
                        $this->waitFindClick();
                        break;
                    case '6':
                        $this->image();
                        break;
                    case '7':
                        break 2;
                    default:
                        $this->process->renderGroup(R::none($this->colorWarning('无效编号, 请重新输入')));
                }
            }catch(\Exception $e) {
                $this->process->renderGroup(R::none($this->colorWarning($e->getMessage())));
            }
        }
    }

    private function get()
    {
        $this->process->renderGroup(R::none($this->colorBlue('请输入页面地址')));
        $url = $this->input();
        $this->driver->get($url);
    }

    private function login()
    {
        $this->process->renderGroup(R::none($this->colorBlue('请输入<span style="#F56C6C">登录页面地址</span>')));
        $login_url = $this->input();
        $this->driver->get($login_url);
        $this->process->renderGroup(R::none($this->colorBlue('请输入<span style="#F56C6C">登录后</span>页面地址')));
        $home_url = $this->input();
        $this->driver->get($home_url);
        return $this->baseLogin($login_url, $home_url, function () {
            $this->whileInput();
            return R::success();
        }, function () use ($home_url) {
            $currentUrl = substr($this->driver->getCurrentURL(), 0, strlen($home_url));
            if ($currentUrl != $home_url) {
                return R::fail("当前地址: $currentUrl, 期望地址: $home_url");
            }
            return R::success();
        });
    }

    private function findInput($by2 = null)
    {
        $message = '请输入输入框的css定位';
        if (isset($by2)) {
            $message .= ', 为空则使用上一个输入的内容';
        }
        $this->process->renderGroup(R::none($this->colorBlue($message)));
        $by = $this->input();
        if (isset($by2) && empty(trim($by))) {
            $by = $by2;
        }
        $element = $this->findElement($by);
        $this->process->renderGroup(R::none($this->colorBlue('请输入内容')));
        $value = $this->input();
        $element->sendKeys($value);
    }

    private function findClick($by2 = null)
    {
        $message = '请输入css定位';
        if (isset($by2)) {
            $message .= ', 为空则使用上一个输入的内容';
        }
        $this->process->renderGroup(R::none($this->colorBlue($message)));
        $by = $this->input();
        if (isset($by2) && empty(trim($by))) {
            $by = $by2;
        }
        $this->findElement($by)->click();
    }

    private function waitFindInput()
    {
        $this->process->renderGroup(R::none($this->colorBlue('请输入等待显示的css定位')));
        $by = $this->input();
        $this->waitVisibility($by);
        $this->findInput($by);
    }

    private function waitFindClick()
    {
        $this->process->renderGroup(R::none($this->colorBlue('请输入等待显示的css定位')));
        $by = $this->input();
        $this->waitVisibility($by);
        $this->findClick($by);
    }

    private function image()
    {
        $this->process->renderGroup(R::none($this->colorBlue('请输入截取图片的css定位, 为空则截整个页面')));
        if (empty(trim($by = $this->input()))) {
            $this->takeScreenshot();
        } else {
            $this->takeScreenshot($by);
        }
    }

    protected function getCookieRedisKey()
    {
        return 'webdriver.manual';
    }

    private function colorBlue($str)
    {
        return "<span style=\"color: #409EFF\">$str</span>";
    }

    private function colorWarning($str)
    {
        return "<span style=\"color: #E6A23C\">$str</span>";
    }
}