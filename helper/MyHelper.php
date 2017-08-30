<?php
/**
 * 公共方法
 *
 * @author zhanghongwei
 */

namespace wind\rest\helper;

use yii\helpers\ArrayHelper;
use yii\log\FileTarget;
use yii\web\UploadedFile;

class MyHelper
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
     * @param array  $arr 传过来的参数数组
     * @param string $code
     * @param string $msg param  verify
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
     * @param       $arr
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
    
}