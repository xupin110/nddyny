<?php
use Server\CoreBase\PortManager;

$ports['ports'][] = [
    'socket_type' => PortManager::SOCK_TCP,
    'socket_name' => '0.0.0.0',
    'socket_port' => config_get('tcp.port'),
    'pack_tool' => 'LenJsonPack',
    'route_tool' => 'nddyny\NddynyRoute',
    'middlewares' => ['MonitorMiddleware']
];

$ports['ports'][] = [
    'socket_type' => PortManager::SOCK_HTTP,
    'socket_name' => '0.0.0.0',
    'socket_port' => config_get('http.port'),
    'route_tool' => 'nddyny\NddynyRoute',
    'middlewares' => ['MonitorMiddleware', 'NormalHttpMiddleware'],
    'method_prefix' => 'http_'
];

$ports['ports'][] = [
    'socket_type' => PortManager::SOCK_WS,
    'socket_name' => '0.0.0.0',
    'socket_port' => config_get('http.port'),
    'route_tool' => 'nddyny\NddynyRoute',
    'pack_tool' => 'NonJsonPack',
    'opcode' => PortManager::WEBSOCKET_OPCODE_TEXT,
    'middlewares' => ['MonitorMiddleware', 'NormalHttpMiddleware']
];
return $ports;