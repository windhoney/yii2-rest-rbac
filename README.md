# yii2-rest-rbac

> Yii2权限系统，rest版，根据[yii2-admin（https://github.com/mdmsoft/yii2-admin）](https://github.com/mdmsoft/yii2-admin)修改

* **安装:**
```php
composer require windhoney/yii2-rest-rbac
```

### **使用**

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
    'components' => [
        'authManager' => [
            'class' => 'wind\rest\components\DbManager', //配置文件
        ],
    ]
```
* **配置权限**
```php
    'as access' => [
        'class' => 'wind\rest\components\AccessControl',
        'allowActions' => [
            'site/*',//允许访问的节点，可自行添加
            'rbac/menu/user-menu',
            'oauth2/*',
        ]
    ],
```


* **创建所需要的表**
> ###### 1. 用户表user和菜单表menu
```php
yii migrate --migrationPath=@vendor/windhoney/yii2-rest-rbac/migrations
```
> ###### 2. rbac相关权限表
```php
yii migrate --migrationPath=@yii/rbac/migrations/
```

> **_**`auth_item` 表添加一个字段 `parent_name` varchar(30) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '父级名称'**_**,
###### 3. oauth2相关表

```php
yii migrate --migrationPath=@vendor/filsh/yii2-oauth2-server/migrations
```

> ###### 4. 新增分组表

```mysql
CREATE TABLE `auth_groups` (
  `group_id` varchar(50) NOT NULL COMMENT '分组id',
  `group_name` varchar(100) NOT NULL DEFAULT '' COMMENT '分组名称',
  `group_status` varchar(50) NOT NULL DEFAULT '' COMMENT '状态（开启，关闭）',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分组';
```

```mysql
CREATE TABLE `auth_groups_child` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` varchar(50) NOT NULL COMMENT '分组id',
  `user_id` varchar(64) NOT NULL COMMENT '用户id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id_2` (`group_id`,`user_id`),
  KEY `group_id` (`group_id`),
  KEY `user_group_id` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=795 DEFAULT CHARSET=utf8 COMMENT='分组子集';
```



* **添加路由配置**

> * 将yii2-rest-rbac/example/rbac_route.php文件内容配置到项目的urlManager的rules规则下
> * 或者在main.php文件中 添加
```php
$dir = __DIR__ . "/route";
$main = wind\rest\helper\RbacHelper::addRoute($dir, $main);
return $main;
```
>>  并将此文件放到config/route/rbac_route.php

* **接口文档参考**

*  [文档](https://windhoney.gitbooks.io/yii2-rest-rbac/)

