<?php
/**
 * 路由配置
 * 将此文件内容配置到项目的urlManager的rules规则下
 * 也可在main.php文件中 添加
 * ```
 * $dir = __DIR__ . "/route";
 * $main = RbacHelper::addRoute($dir, $main);
 * return $main;
 * ```
 * 引入路由文件 直接将此文件放到config/route/rbac_route.php
 */
$rules = [
    //权限
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['rbac/permission'],
        'extraPatterns' => [
            'GET view' => 'view',
            'DELETE delete' => 'delete',
            'POST update' => 'update',
            'POST assign' => 'assign',
            'POST remove' => 'remove',
            'GET assign-list' => 'assign-list',
        ]
    ],
    //菜单
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['rbac/menu'],
        'extraPatterns' => [
            'GET parent' => 'parent',
            'POST create' => 'create',
            'POST update' => 'update',
            'GET user' => 'user-menu'
        ]
    ],
    //路由
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['rbac/route'],
        'extraPatterns' => [
            'POST remove' => 'remove',
            'GET  all' => 'all',
            'GET  parent' => 'parent',
        ]
    ],
    //角色
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['rbac/role'],
        'extraPatterns' => [
            'GET view' => 'view',
            'DELETE delete' => 'delete',
            'POST update' => 'update',
            'POST assign' => 'assign',
            'GET assign-list' => 'assign-list',
            'POST remove' => 'remove',
        ]
    ],
    //分配
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['rbac/assignment'],
        'extraPatterns' => [
            'GET view' => 'view',
            'POST assign' => 'assign',
            'POST revoke' => 'revoke',
            'GET assign-list' => 'assign-list',
            'POST remove' => 'remove',
            'POST assign-batch' => 'assign-batch',
            'POST assign-remove' => 'remove-users',
            'GET assign-users' => 'assign-users',
        ]
    ],
    //用户
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['rbac/user'],
        'extraPatterns' => [
            'GET view' => 'view',
            'POST activate' => 'activate',
        ]
    ],
    //规则
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['rbac/rule'],
        'extraPatterns' => [
            'GET index' => 'get-rules',
            'POST create' => 'create',
            'POST delete' => 'delete',
            'POST update' => 'update',
        ]
    ],
    //分组
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['rbac/groups'],
        'extraPatterns' => [
            'POST assign' => 'assign',
            'POST revoke' => 'revoke',
            'GET assign-user' => 'assign-user',
            'POST status' => 'status',
        ]
    ]
];

return $rules;
