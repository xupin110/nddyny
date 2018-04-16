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
        $s = ' * &nbsp* &nbsp';
        while (true) {
            $prompt = <<<EOF
-------------------------
请输入编号
  1.打开页面
  2.登录
  3.查找、输入
  4.{$s}、点击
  5.{$s}、按键
  6.等待、查找、输入
  7.{$s}、{$s}、点击
  8.{$s}、{$s}、按键
  9.显示图片
  0.退出本次循环输入
  -.清空cookie
-------------------------
EOF;
            $this->process->renderGroup(R::none($this->colorBlue($prompt)));
            $type = $this->input();
            try {
                switch ($type) {
                    case '1':
                        $this->get();
                        break;
                    case '2':
                        $this->login();
                        break;
                    case '3':
                        $this->findInput();
                        break;
                    case '4':
                        $this->findClick();
                        break;
                    case '5':
                        $this->findPressKey();
                        break;
                    case '6':
                        $this->waitFindInput();
                        break;
                    case '7':
                        $this->waitFindClick();
                        break;
                    case '8':
                        $this->waitFindPressKey();
                        break;
                    case '9':
                        $this->image();
                        break;
                    case '0':
                        break 2;
                    case '-':
                        $this->delCookie();
                        break;
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
        $this->process->renderGroup(R::none($this->colorBlue('请输入<span style="color:#F56C6C">登录</span>页面地址')));
        $login_url = $this->input();
        $this->process->renderGroup(R::none($this->colorBlue('请输入<span style="color:#F56C6C">登录后</span>页面地址')));
        $home_url = $this->input();
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

    private function findPressKey($by2 = null) {
        $this->findInput($by2);
        $this->process->renderGroup(R::none($this->colorBlue('请输入大写的按键名')));
        $key = $this->input();
        $this->driver->getKeyboard()->pressKey(constant("\Facebook\WebDriver\WebDriverKeys::$key"));
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

    private function waitFindPressKey() {
        $this->waitFindInput();
        $this->process->renderGroup(R::none($this->colorBlue('请输入大写的按键名')));
        $key = $this->input();
        $this->driver->getKeyboard()->pressKey(constant("\Facebook\WebDriver\WebDriverKeys::$key"));
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

    private function delCookie()
    {
        $this->redis_pool->getCoroutine()->del($this->getCookieRedisKey());
        $this->driver->manage()->deleteAllCookies();
        $this->process->renderGroup(R::none('<span style="color:#F56C6C">成功清空cookie</span>'));
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