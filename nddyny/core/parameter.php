<?php

function xss_clean($str)
{
    static $xss_clean;
    if (! $xss_clean) {
        $xss_clean = new \voku\helper\AntiXSS();
    }
    return $xss_clean->xss_clean($str);
}

function param_result_message($default, $name, $code){
    return empty($default) ? $name . ' ' . R::$code_messages[$code] : $default;
}

/**
 * 未设置参数时 如果是必填返回错误 不是必填返回默认值
 */
function param_value($scope, $name, $required = false, $default = null, $code = R::ARGUMENT_MISSING)
{
    if (isset($scope[$name])) {
        return xss_clean($scope[$name]);
    }
    if ($required) {
        throw new NddynyException(R::fail(null, $code, param_result_message($default, $name, $code)));
    }
    return xss_clean($default);
}

/**
 * 设置了参数时，如果不是数组或必填的情况下是空数组，返回错误，是数组且是空数组的时候返回默认值
 */
function param_array($scope, $name, $required = false, $default = [])
{
    $array = param_value($scope, $name, $required, $default);
    if (is_array($array) == false || ($required && count($array) == 0)) {
        $code = R::ARGUMENT_INVALID_ARRAY_EMPTY;
        throw new NddynyException(R::fail(null, $code, $required ? param_result_message($default, $name, $code) : param_result_message(null, $name, $code)));
    }
    return count($array) == 0 ? $default : $array;
}

/**
 * 已设置参数但值为空时 如果是必填返回错误 不是必填返回默认值
 */
function param_empty($scope, $name, $required = false, $default = null, $code = R::ARGUMENT_MISSING)
{
    $value = param_value($scope, $name, $required, $default, $code);
    if (is_string($value)) {
        $value = trim($value);
    }
    if (empty($value) == false || is_numeric($value)) {
        return $value;
    }
    if ($required) {
        throw new NddynyException(R::fail(null, $code, param_result_message($default, $name, $code)));
    }
    return $default;
}

/**
 * 字符串
 */
function param_string($scope, $name, $required = false, $default = '', $code = R::ARGUMENT_INVALID)
{
    $value = param_empty($scope, $name, $required, $default, $code);
    if (is_string($value)) {
        return $value;
    }
    if ($required) {
        throw new NddynyException(R::fail(null, $code, param_result_message($default, $name, $code)));
    }
    return $default;
}

/**
 * 整数
 */
function param_int($scope, $name, $required = false, $default = 0)
{
    $value = param_value($scope, $name, $required, $default);
    if (is_null($value) || $value == '') {
        if ($required) {
            $code = R::ARGUMENT_MISSING;
            throw new NddynyException(R::fail(null, $code, param_result_message($default, $name, $code)));
        }
        return $default;
    }
    if (ctype_digit($value) || is_int($value)) {
        return intval($value);
    }
    if ($value[0] == '-' && ctype_digit(substr($value, 1))) {
        return intval($value);
    }
    $code = R::ARGUMENT_INVALID;
    throw new NddynyException(R::fail(null, $code, param_result_message($default, $name, $code)));
}

/**
 * 自然数
 */
function param_uint($scope, $name, $required = false, $default = 0)
{
    $value = param_int($scope, $name, $required, $default);
    if ($value >= 0) {
        return $value;
    }
    $code = R::ARGUMENT_INVALID;
    throw new NddynyException(R::fail(null, $code, param_result_message($default, $name, $code)));
}

/**
 * 正整数
 */
function param_pint($scope, $name, $required = false, $default = 0)
{
    $value = param_int($scope, $name, $required, $default);
    if ($value > 0) {
        return $value;
    }
    $code = R::ARGUMENT_INVALID;
    throw new NddynyException(R::fail(null, $code, param_result_message($default, $name, $code)));
}

/**
 * 浮点数
 */
function param_float($scope, $name, $required = false, $default = 0.0)
{
    $value = param_value($scope, $name, $required, null);
    if (is_null($value) || $value == '') {
        if ($required)
            $code = R::ARGUMENT_MISSING;
            throw new NddynyException(R::fail(null, $code, param_result_message($default, $name, $code)));
        return $default;
    }
    if (preg_match('/^[0-9\.]*$/i', $value)) {
        return floatval($value);
    }
    $code = R::ARGUMENT_INVALID;
    throw new NddynyException(R::fail(null, $code, param_result_message($default, $name, $code)));
}