# yii2-rest-rbac
**感谢JetBrains对开源软件的支持**[![JetBrains Logo](jetbrains.png)](https://www.jetbrains.com/?from=yii2-rest-rbac)

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
            'class' => 'wind\rest\Modules'
        ],
        'oauth2' => [
            'class' => 'filsh\yii2\oauth2server\Module',
            //'class' => 'wind\oauth2\Module',相对filsh\yii2\oauth2server做了一点优化，增加了可修改oauth2表的db name
            'tokenParamName' => 'access_token',
            'tokenAccessLifetime' => 3600 * 24,
            'storageMap' => [
                'user_credentials' => 'common\models\User',//可自定义
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
            ],
            //选填，oauth2组件版本问题可能导致错误时可添加
            'components' => [
                    'request' => function () {
                        return \filsh\yii2\oauth2server\Request::createFromGlobals();
                    },
                    'response' => [
                        'class' => \filsh\yii2\oauth2server\Response::class,
                    ],
            ],
        ]
    ],
    'components' => [
        'authManager' => [
            'class' => 'wind\rest\components\DbManager', //配置文件
            'defaultRoles' => ['普通员工'] //选填，默认角色（默认角色下->公共权限（登陆，oauth2，首页等公共页面））
            'groupTable' => 'auth_groups',//选填，分组表(已默认，可根据自己表名修改)
            'groupChildTable' => 'auth_groups_child',//选填，分组子表(已默认，可根据自己表名修改)
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            //'cookieValidationKey' => 'xxxxxxxx',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json' => 'yii\web\JsonParser',
            ],
        ],
    ]
```
* **配置权限**

```php
    'as access' => [
        'class' => 'wind\rest\components\AccessControl',
        'allowActions' => [
            'site/*',//允许访问的节点，可自行添加
            'rbac/menu/user-menu',//可将路由配置到“普通员工”（默认角色）下
            'oauth2/*',//可将路由配置到“普通员工”（默认角色）下
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

> **`auth_item` 表添加一个字段 `parent_name` varchar(30) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '父级名称'**,
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
> ###### 5. 权限控制相关路由可以参考 example/auth_item_sql 其中包含权限相关的insert语句

```mysql
INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `parent_name`, `data`, `created_at`, `updated_at`) VALUES ('/rbac/menu/index', '2', '接口-菜单接口', NULL, '权限控制', '', '1526377735', '1526377269');
INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `parent_name`, `data`, `created_at`, `updated_at`) VALUES ('/rbac/role/index', '2', '接口-角色接口', NULL, '权限控制', '', '1526377735', '1526377269');
......
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

*  [文档](https://windhoney.gitbook.io/yii2-rest-rbac/)

