<?php

function uuid()
{
    $uuid = \Ramsey\Uuid\Uuid::uuid1();
    return $uuid->toString();
}

function str2int($string)
{
    return crc32(md5($string));
}

function unknown2array($unknown, $pure_string_execution = true)
{
    if (is_string($unknown)) {
        if (($array = json_decode($unknown, true)) != null) {
            return $array;
        }
        if ($pure_string_execution == false) {
            return $unknown;
        }
    }
    return (array)$unknown;
}

function eol($value = '', $amount = 5)
{
    $amount_start = $amount - 1;
    while ($amount_start--) {
        echo PHP_EOL;
    }
    if (is_array($value) || is_object($value)) {
        dump($value);
    } else {
        echo $value;
    }
    while ($amount--) {
        echo PHP_EOL;
    }
}

function dump($var, $echo = true, $label = null, $strict = true)
{
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if ($strict == false) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = "<pre>" . $label . htmlspecialchars($output, ENT_QUOTES) . "</pre>";
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (extension_loaded('xdebug') == false) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    } else
        return $output;
}

function mb_str_pad($str, $len, $mb_factor = 2)
{
    if (mb_strlen($str) * 3 - strlen($str) == 0) {
        return str_pad($str, $len - mb_strlen($str) * $mb_factor + strlen($str));
    } else {
        $str_count = (mb_strlen($str) * 3 - strlen($str)) / 2;
        $mb_str_count = (mb_strlen($str) - $str_count);
        return str_pad($str, $len - ($str_count + $mb_str_count * $mb_factor) + strlen($str));
    }
}

function mb_str_plite($string, $len = 1)
{
    $start = 0;
    $strlen = mb_strlen($string);
    while ($strlen) {
        $array[] = mb_substr($string, $start, $len, "utf8");
        $string = mb_substr($string, $len, $strlen, "utf8");
        $strlen = mb_strlen($string);
    }
    return $array;
}

function super_array_unique($array)
{
    $result = array_map("unserialize", array_unique(array_map("serialize", $array)));

    foreach ($result as $key => $value) {
        if (is_array($value)) {
            $result[$key] = super_array_unique($value);
        }
    }

    return $result;
}

function thumbnail($src, $width, $height, $maxSize = 100)
{
    $wRatio = $maxSize / $width;
    $hRatio = $maxSize / $height;
    if (($width <= $maxSize) && ($height <= $maxSize)) {
        return $src;
    } elseif (($wRatio * $height) < $maxSize) {
        $tHeight = ceil($wRatio * $height);
        $tWidth = $maxSize;
    } else {
        $tWidth = ceil($hRatio * $width);
        $tHeight = $maxSize;
    }
    $dest = imagecreatetruecolor($tWidth, $tHeight);
    imagecopyresized($dest, $src, 0, 0, 0, 0, $tWidth, $tHeight, $width, $height);
    return $dest;
}

function json_format($json)
{
    return _json_format($json, '', '', true);
}

function json_format_html($json)
{
    return _json_format($json, '<br>', '&nbsp;&nbsp;&nbsp;&nbsp;', false);
}

function json_format_string($json)
{
    return _json_format($json, PHP_EOL, '    ', false);
}

function _json_format($json, $br, $tab, $pure_json)
{
    if (!is_string($json)) {
        $json = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!$json) {
            return $json;
        }
    }
    $result = '';
    $indent_level = 0;
    for ($i = 0; $i < strlen($json); $i += 1) {
        $current_char = $json[$i];
        switch ($current_char) {
            case '{':
            case '[':
                if ($pure_json == false) {
                    $result .= $current_char . $br . str_repeat($tab, $indent_level + 1);
                    $indent_level += 1;
                } else {
                    $result .= $current_char;
                }
                break;
            case '}':
            case ']':
                if ($pure_json == false) {
                    $result .= $br . str_repeat($tab, $indent_level == 0 ? 0 : --$indent_level) . $current_char;
                } else {
                    $result .= $current_char;
                }
                break;
            case ',':
                if ($pure_json == false) {
                    $result .= "," . $br . str_repeat($tab, $indent_level);
                } else {
                    $result .= $current_char;
                }
                break;
            case ':':
                if ($pure_json == false) {
                    $result .= ": ";
                } else {
                    $result .= $current_char;
                }
                break;
            case ' ':
            case "\n":
            case "\t":
                if ($pure_json) {
                    $result .= $current_char;
                }
                break;
            case '"':
                if ($i > 0 && $json[$i - 1] !== '\\') {
                    $pure_json = !$pure_json;
                }
                $result .= $current_char;
                break;
            default:
                $result .= $current_char;
                break;
        }
    }
    return $result;
}