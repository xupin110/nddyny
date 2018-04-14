<?php

class R extends ResultCode
{

    const NONE = 'none';

    const SUCCESS = 'success';

    const ERROR = 'error';

    const FAIL = 'fail';

    const FALSE = 'ResultFalse';

    public static function isSelf($Result)
    {
        return is_object($Result) && (get_class($Result) == 'ResultObject') ? true : false;
    }

    public static function success($result = null, $code = null, $message = null, $assoc = null)
    {
        return self::returns(self::SUCCESS, $result, $code, $message, $assoc);
    }

    public static function error($result = null, $code = null, $message = null, $assoc = null)
    {
        return self::returns(self::ERROR, $result, $code, $message, $assoc);
    }

    public static function fail($result = null, $code = null, $message = null, $assoc = null)
    {
        return self::returns(self::FAIL, $result, $code, $message, $assoc);
    }

    public static function none($result = null, $code = null, $message = null, $assoc = null)
    {
        return self::returns(self::NONE, $result, $code, $message, $assoc);
    }

    public static function returns($status, $result, $code, $message, $assoc = null)
    {
        if ($message == null && is_numeric($code) && isset(self::$code_messages[$code])) {
            $message = self::$code_messages[$code];
        }
        if ($status != R::SUCCESS && $message == null) {
            $message = $result;
            $result = null;
        }
        if ($assoc === true) {
            $resultArray['status'] = $status;
            if ($result != null) {
                $resultArray['result'] = $result;
            }
            if ($code != null) {
                $resultArray['code'] = $code;
            }
            if ($message != null) {
                $resultArray['message'] = $message;
            }
            return $resultArray;
        }
        return new ResultObject($result, $code, $message, $status);
    }

    public static function isSuccess($Result)
    {
        $Result = R::castResultObject($Result);
        return self::status($Result) == self::SUCCESS ? $Result->result : self::FALSE;
    }

    public static function noSuccess($Result)
    {
        $Result = R::castResultObject($Result);
        return self::status($Result) != self::SUCCESS ? true : false;
    }

    private static function status(ResultObject $Result, $default_status = self::FAIL)
    {
        if (empty($Result->status)) {
            $status = $default_status;
        } else {
            switch ($Result->status) {
                case self::SUCCESS:
                    $status = self::SUCCESS;
                    break;
                case self::ERROR:
                    $status = self::ERROR;
                    break;
                case self::FAIL:
                    $status = self::FAIL;
                    break;
                case self::NONE:
                    $status = self::NONE;
                    break;
                default:
                    $status = $default_status;
            }
        }
        return $status;
    }

    public static function findResultValue($data, $keys)
    {
        if (!isset($keys)) {
            return null;
        }
        if (is_array($keys)) {
            $result = [];
            foreach ($keys as $key) {
                $result[$key] = $data[$key];
            }
        } else {
            $result = $data[$keys];
        }
        return $result;
    }

    public static function merge(ResultObject $Result, $result = null, $code = null, $message = null, $status = null)
    {
        $Result->status = $status == null ? $Result->status : $status;
        $Result->result = $result == null ? $Result->result : $result;
        $Result->code = $code == null ? $Result->code : $code;
        $Result->message = $message == null ? $Result->message : $message;
        return $Result;
    }

    public static function extend(ResultObject $Result, $array)
    {
        foreach ($array as $key => $info) {
            $Result->$key = $info;
        }
        return $Result;
    }

    /**
     * 任意格式转换成ResultObject格式
     *
     * @param unknown $data
     * @param string $default_status
     */
    public static function castResultObject($data, $default_status = self::FAIL)
    {
        if (!is_string($data) && !is_numeric($data)) {
            $data = (array)$data;
        } elseif (($array = json_decode($data, true)) != null) {
            $data = $array;
        } else {
            // $data = string
        }
        if (isset($data['status'])) {
            $result = isset($data['result']) ? $data['result'] : null;
            $code = isset($data['code']) ? $data['code'] : null;
            $message = isset($data['message']) ? $data['message'] : null;
        } else {
            $result = null;
            $code = null;
            $message = $data;
            $data = [
                'status' => $default_status
            ];
        }
        switch ($data['status']) {
            case self::SUCCESS:
                $ResultObject = self::success($result, $code, $message);
                break;
            case self::ERROR:
                $ResultObject = self::error($result, $code, $message);
                break;
            case self::FAIL:
                $ResultObject = self::fail($result, $code, $message);
                break;
            default:
                $ResultObject = self::{$default_status}($result, $code, $message);
        }
        $ResultObject instanceof ResultObject;
        return $ResultObject;
    }
}

class ResultObject
{

    public $status;

    public $result;

    public $code;

    public $message;

    public function __construct($result = null, $code = null, $message = null, $status = null)
    {
        $this->status = $status;
        $this->result = $result;
        $this->code = $code;
        $this->message = $message;
    }

    public function result()
    {
        $result = (array)$this;
        foreach ($result as $key => $value) {
            if ($value === null) {
                unset($result[$key]);
            }
        }
        return $result;
    }

    public function json()
    {
        return json_encode($this->result(), JSON_UNESCAPED_UNICODE);
    }

    public function api()
    {
        echo $this->json();
    }

    public function view()
    {
        echo $this->json();
        return false;
    }

    public function console()
    {
        echo $this->json() . PHP_EOL;
    }
}

class ResultCode
{

    /**
     * PHP错误-类不存在
     */
    const PHP_CLASS_NO_EXIST = 300;

    /**
     * PHP错误-文件上传失败
     */
    const PHP_FILES_ERROR = 700;

    /**
     * 缺少需要的参数
     */
    const ARGUMENT_MISSING = 1000;

    /**
     * 无效的参数
     */
    const ARGUMENT_INVALID = 1001;

    /**
     * 无效的参数 - 空数组
     */
    const ARGUMENT_INVALID_ARRAY_EMPTY = 1002;

    /**
     * 未找到配置
     */
    const CONFIG_NOTFOUND = 2000;

    /**
     * 未找到方法
     */
    const METHOD_NOTFOUND = 2001;

    /**
     * 未找到字典
     */
    const DICT_NTFOUND = 2002;

    /**
     * Mysql error
     */
    const MYSQL_CRUD_ERROR = 3000;

    /**
     * Redis error
     */
    const REDIS_CRUD_ERROR = 4000;

    /**
     * MQ发送error
     */
    const MQ_SEND_ERROR = 5000;

    /**
     * MQ补发到上限
     */
    const MQ_RESEND_REACH_LIMIT = 5001;

    /**
     * MQ回调返回error
     */
    const MQ_CALLBACK_RETURN_ERROR = 5002;

    /**
     * 上传文件失败
     */
    const UPLOAD_FILE_ERROR = 6000;

    /**
     * 上传的文件超出了设定的最大值
     */
    const UPLOAD_FILE_MAXIMIZE = 6001;

    /**
     * 上传的文件格式不支持
     */
    const UPLOAD_FILE_TYPE_NONSUPPORT = 6002;

    /**
     * 创建文件失败
     */
    const FILE_FOLDER_CREATE_ERROR = 7001;

    /**
     * 接口返回的格式不正确
     */
    const API_RESULT_ERROR = 8000;

    /**
     * 身份验证失败 - websocket会断开链接
     */
    const AUTH_VERIFY_FAIL = 9001;

    /**
     * swoole error
     */
    const SWOOLE_ERROR = 10000;

    public static $code_messages = [
        self::PHP_CLASS_NO_EXIST => 'PHP-类不存在',
        self::PHP_FILES_ERROR => 'PHP-文件上传失败',
        self::ARGUMENT_MISSING => '缺少需要的参数',
        self::ARGUMENT_INVALID => '无效的参数',
        self::ARGUMENT_INVALID_ARRAY_EMPTY => '无效的参数 - 空数组',
        self::CONFIG_NOTFOUND => '未找到配置',
        self::METHOD_NOTFOUND => '未找到方法',
        self::DICT_NTFOUND => '未找到字典',
        self::MYSQL_CRUD_ERROR => 'Mysql error',
        self::REDIS_CRUD_ERROR => 'Redis error',
        self::MQ_SEND_ERROR => 'MQ发送error',
        self::MQ_RESEND_REACH_LIMIT => 'MQ补发到上限',
        self::MQ_CALLBACK_RETURN_ERROR => 'MQ回调返回error',
        self::SWOOLE_ERROR => 'swoole error',
        self::UPLOAD_FILE_ERROR => '上传文件失败',
        self::UPLOAD_FILE_MAXIMIZE => '上传的文件超出了设定的最大值',
        self::UPLOAD_FILE_TYPE_NONSUPPORT => '上传的文件格式不支持',
        self::API_RESULT_ERROR => '接口返回的格式不正确',
        self::AUTH_VERIFY_FAIL => '身份验证失败',
        self::FILE_FOLDER_CREATE_ERROR => '创建文件失败'
    ];
}