<?php

namespace wind\rest\helper;

use yii\log\FileTarget;

/**
 * 公共方法
 *
 * @author  windhoney
 * @package wind\rest\helper
 */
class RbacHelper
{
    
    /**
     * 接口返回格式
     *
     * @param int    $code
     * @param string $message
     * @param array  $date
     *
     * @return mixed
     */
    public static function resultMessage($code = 400, $message = 'ok')
    {
        return compact('code', 'message');
    }
    
    /**
     * 参数验证方法
     *
     * @param array $arr
     * @param int   $code
     */
    public static function paramVerify($arr, $code = 400)
    {
        $diff = array_diff($arr, array_filter($arr));
        $key_arr = array_keys($diff);
        $key_str = implode(',', $key_arr);
        if ($diff) {
            header("Content-type: application/json; charset=utf-8");
            echo json_encode(self::resultMessage($code, '参数错误:缺少 ' . $key_str), JSON_UNESCAPED_UNICODE);
            die;
        }
    }
    
    /**
     * 参数验证
     *
     * @param array $arr
     * @param array $need
     */
    public static function paramRequire($arr, $need = [])
    {
        foreach ($need as $k) {
            if (empty($arr[$k])) {
                header("Content-type: application/json; charset=utf-8");
                echo json_encode(self::resultMessage(400, '参数错误:缺少 ' . $k), JSON_UNESCAPED_UNICODE);
                die;
            }
        }
    }
    
    /**
     * 错误验证
     *
     * @param int    $code
     * @param string $msg
     */
    public static function error($code = 400, $msg = '非法参数')
    {
        header("Content-type: application/json; charset=utf-8");
        echo json_encode(self::resultMessage($code, $msg), JSON_UNESCAPED_UNICODE);
        die;
    }
    
    /**
     * 记录错误日志
     *
     * @param string|array $message
     * @param string       $action
     * @param string       $file_name
     */
    public static function recordLog($message, $action = 'application', $file_name = "rbac")
    {
        $message = is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message;
        $time = microtime(true);
        $log = new FileTarget();
        $log->logFile = \Yii::$app->getRuntimePath() . '/logs/' . $file_name . '.log';
        $log->messages[] = [$message, 1, $action, $time];
        $log->export();
    }
    
    /**
     * 添加路由配置
     *
     * @param $dir
     * @param $main
     *
     * @return mixed
     */
    public static function addRoute($dir, $main)
    {
        $file = scandir($dir);
        $file = array_filter($file, function ($k) {
            return strpos($k, '.php');
        });
        $route_arr = [];
        foreach ($file as $item) {
            $route_arr[] = require($dir . '/' . $item);
        }
        foreach ($route_arr as $route) {
            if ( !empty($route)) {
                foreach ($route as $v) {
                    array_push($main['components']['urlManager']['rules'], $v);
                }
            }
        }
        
        return $main;
    }
}
