# yii2-rest-rbac


> 开发中.....


###使用
* **配置oauth2和rbac**
```php
   'modules' => [
        'rbac' => [
            'class' => 'wind\rest\modules'
        ],
        'oauth2' => [
            'class' => 'filsh\yii2\oauth2server\Module',
            'tokenParamName' => 'access_token',
            'tokenAccessLifetime' => 3600 * 24,
            'storageMap' => [
                'user_credentials' => 'backend\models\User',
            ],
            'grantTypes' => [
                'user_credentials' => [
                    'class' => 'OAuth2\GrantType\UserCredentials',
                ],
                'client_credentials' => [
                    'class' => 'OAuth2\GrantType\ClientCredentials',
                ],
                'refresh_token' => [
                    'class' => 'OAuth2\GrantType\RefreshToken',
                    'always_issue_new_refresh_token' => true
                ],
                'authorization_code' => [
                    'class' => 'OAuth2\GrantType\AuthorizationCode'
                ],
            ]
        ]
    ],
```

* **添加路由配置**

将yii2-rest-rbac/example/rbac_route.php文件内容配置到项目的urlManager的rules规则下
也可在main.php文件中 添加
```
$dir = __DIR__ . "/route";
$main = RbacHelper::addRoute($dir, $main);
return $main;
```
直接将此文件放到config/route/rbac_route.php

* **接口文档参考**

[文档](https://windhoney.gitbooks.io/yii2-rest-rbac/)

