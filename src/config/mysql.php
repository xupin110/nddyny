<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午4:49
 */
$config['mysql']['enable'] = true;
$config['mysql']['active'] = 'default';
$config['mysql']['default']['host'] = config_get('mysql.host');
$config['mysql']['default']['port'] = config_get('mysql.port');
$config['mysql']['default']['user'] = config_get('mysql.user');
$config['mysql']['default']['password'] = config_get('mysql.password');
$config['mysql']['default']['database'] = config_get('mysql.database');
$config['mysql']['default']['charset'] = 'utf8';
$config['mysql']['asyn_max_count'] = config_get('mysql.asyn_max_count');

return $config;