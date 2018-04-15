<?php

namespace app\Controllers\nddyny\Common;

use Facebook\WebDriver\WebDriverBy;
use R;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use app\Controllers\nddyny\Common\Process;

abstract class ModelWebdriver extends Model
{

    /** @var Process $process */
    protected $process;

    /** @var RemoteWebDriver */
    protected $driver;

    protected $cookies;

    protected $input_maxtimes = 120;

    abstract protected function getCookieRedisKey();

    public function init(Process $Process)
    {
        $this->process = $Process;
    }

    protected function phantomjs($enabledImg = true, $enabledJs = true)
    {
        $Capability = DesiredCapabilities::phantomjs();
        $Capability->setCapability('phantomjs.binary.path', EXTRA_DIR . '/webdriver/phantomjs');
        $Capability->setCapability('phantomjs.page.customHeaders.Accept-Language', 'zh-CN,zh;q=0.8');
        $Capability->setCapability('phantomjs.page.settings.userAgent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/59.0.3071.109 Chrome/59.0.3071.109 Safari/537.36');
        $Capability->setCapability('phantomjs.page.settings.loadImages', $enabledImg);
        $Capability->setCapability('phantomjs.page.settings.javascriptEnabled', $enabledJs);
        $Capability->setCapability('phantomjs.cli.args', ['--ssl-protocol=any', '--ignore-ssl-errors=true']);
        $this->process->renderGroup(R::none('打开浏览器'));
        $this->driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $Capability);
    }

    protected function baseLogin($login_url, $home_url, $actionLogin, $checkLogin)
    {
        if (R::isSuccess($this->setCookies()) !== R::FALSE) {
            $this->process->renderGroup(R::none('进入首页'));
            $this->driver->get($home_url);
            $this->process->renderGroup(R::none('验证登录状态'));
            if (!R::noSuccess($Result = $checkLogin())) {
                $this->process->renderGroup(R::success('登录成功'));
                return $Result;
            }
            $this->process->renderGroup(R::fail('未登录状态'));
            $this->redis_pool->getCoroutine()->del($this->getCookieRedisKey());
        }
        $this->process->renderGroup(R::none('登录'));
        $this->driver->get($login_url);
        if (R::noSuccess($Result = $actionLogin())) {
            return $Result;
        }
        $this->process->renderGroup(R::none('验证登录状态'));
        if (R::noSuccess($Result = $checkLogin())) {
            $this->process->renderGroup($Result);
            $this->process->renderGroup(R::fail('登录失败'));
            return $Result;
        }
        $this->addCookies();
        return R::success();
    }

    protected function takeScreenshot($by = 'body')
    {
        $this->process->renderGroup(R::none('截取整个页面'));
        $screenshot = $this->driver->takeScreenshot();
        try {
            $this->process->renderGroup(R::none('截取页面指定位置'));
            $this->waitVisibility($by);
            $element = $this->findElement($by);
        } catch (\Exception $e) {
            $this->process->renderGroup(R::fail('定位不到指定区域'));
            $this->process->renderGroup(R::none(base64_encode($screenshot), $this->process::CODE_BASE64));
            return;
        }
        $element_width = $element->getSize()->getWidth();
        $element_height = $element->getSize()->getHeight();
        $element_src_x = $element->getLocation()->getX();
        $element_src_y = $element->getLocation()->getY();
        $src = imagecreatefromstring($screenshot);
        $dest = imagecreatetruecolor($element_width, $element_height);
        imagecopy($dest, $src, 0, 0, $element_src_x, $element_src_y, $element_width, $element_height);
        imagedestroy($src);
        $src = $dest;
        $maxSize = 400;
        while ($maxSize) {
            $this->process->renderGroup(R::none("调整图片大小, 最大{$maxSize}px"));
            $dest = thumbnail($src, $element_width, $element_height, $maxSize);
            ob_start();
            imagepng($dest, null, 9);
            $img = ob_get_contents();
            ob_end_clean();
            imagedestroy($dest);
            $str = base64_encode($img);
            if(strlen(base64_encode($str)) > 80000) {
                $maxSize -= 25;
                continue;
            }
            imagedestroy($src);
            $this->process->renderGroup(R::none("请等待图片显示"));
            $this->process->renderGroup(R::none($str, $this->process::CODE_BASE64));
            break;
        }
    }

    protected function input()
    {
        $i = 0;
        $this->process->delInput();
        // 等待输入
        $this->process->renderGroup(R::none(null, $this->process::CODE_INPUT_SHOW), false);
        while (($input = $this->process->getInput()) === '') {
            if ($i++ == $this->input_maxtimes) {
                throw new \NddynyException(R::fail('长时间未输入，关闭进程'));
            }
            $this->process->renderGroup(R::none('.'), false);
            sleep(1);
            continue;
        }
        $this->process->renderGroup(R::none(PHP_EOL . '<span style="color: #67C23A">输入内容: ' . $input . '</span>', $this->process::CODE_INPUT_HIDE));
        return $input;
    }

    protected function setCookies()
    {
        $this->process->renderGroup(R::none('设置cookies'));
        if (empty($this->cookies = json_decode($this->redis_pool->getCoroutine()->get($this->getCookieRedisKey()), true))) {
            $this->process->renderGroup(R::none('没有可用的cookies'));
            return R::fail();
        }
        try {
            foreach ($this->cookies as $cookie) {
                if (substr($cookie['domain'], 0, 1) != '.') {
                    $cookie['domain'] = '.' . $cookie['domain'];
                }
                $this->driver->manage()->addCookie($cookie);
            }
            return R::success();
        } catch (\Exception $e) {
            $this->process->renderGroup(R::error($e->getMessage(), $e->getCode(), $e->getPrevious()));
            return R::fail();
        }
    }

    protected function addCookies()
    {
        $this->process->renderGroup(R::none('添加cookies'));
        $this->redis_pool->getCoroutine()->set($this->getCookieRedisKey(), json_encode(array_map(function ($cookie) {
            return $cookie->toArray();
        }, $this->driver->manage()->getCookies())));
    }

    protected function by($css_selector)
    {
        return WebDriverBy::cssSelector($css_selector);
    }

    protected function waitVisibility($by, $timeout_in_second = 5, $interval_in_millisecond = 500)
    {
        $WebDriverExpectedCondition = WebDriverExpectedCondition::visibilityOfElementLocated($this->by($by));
        $this->driver->wait($timeout_in_second, $interval_in_millisecond)->until($WebDriverExpectedCondition);
    }

    protected function findElement($by)
    {
        return $this->driver->findElement($this->by($by));
    }
}