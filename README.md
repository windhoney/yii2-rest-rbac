# yii2-rest-rbac

```PHP
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
```
