<?php
require __DIR__ . '/vendor/autoload.php';

define('_THIS', '$_this');
define('NO_FOUND', 'noFound');
define('IS_TRUE', '1');
define('IS_FALSE', '0');
define('STATUS_ALL', 'all');
define('STATUS_NORMAL', '0');
define('STATUS_DELETE', '-1');

define('LOG_MYSQL', 'mysql');
define('LOG_PROCESS_TASK', 'process_task');

define('APP_ID', 1);
define('APP_NAME', 'nddyny');

define('REDIS_APP_LIST', 'nddyny:app:list');

openlog("php_nddyny", LOG_ODELAY, LOG_USER);

Config::setConfigs(require_once __DIR__ . '/config/common.php');
Config::setConfigs(require_once __DIR__ . '/config/ports.php');
Config::setConfigs(require_once __DIR__ . '/config/custom.php');

class NddynyException extends Exception
{
    public function __construct(ResultObject $Result)
    {
        parent::__construct($Result->message, $Result->code);
    }
}