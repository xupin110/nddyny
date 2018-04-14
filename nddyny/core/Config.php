<?php

function config_get($key, $default = NO_FOUND)
{
    $value = Config::getInstance()->get($key);
    if ($value !== NO_FOUND) {
        return $value;
    }
    if ($default == NO_FOUND) {
        throw new Exception($key, R::CONFIG_NOTFOUND);
    }
    return $default;
}

function config_set($key, $value)
{
    Config::getInstance()->set($key, $value);
}

class Config
{

    private static $_instance;

    private $config_list = [];

    private function __construct()
    {}

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Config();
        }
        return self::$_instance;
    }

    public static function setConfigs($config_list)
    {
        self::getInstance()->config_list += $config_list;
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->config_list)) {
            return $this->config_list[$key];
        }
        return NO_FOUND;
    }

    public function set($key, $value)
    {
        $this->config_list[$key] = $value;
    }
}