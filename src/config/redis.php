<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-14
 * Time: 下午1:58
 */

/**
 * 选择数据库环境
 */
$config['redis']['enable'] = true;
$config['redis']['active'] = 'default';

/**
 * 本地环境
 */
$config['redis']['default']['ip'] = config_get('redis.ip');
$config['redis']['default']['port'] = config_get('redis.port');
$config['redis']['default']['select'] = config_get('redis.select');
$config['redis']['default']['password'] = config_get('redis.password');

$config['redis']['asyn_max_count'] = config_get('redis.asyn_max_count');
/**
 * 最终的返回，固定写这里
 */
return $config;
