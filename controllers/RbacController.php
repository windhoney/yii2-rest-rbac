<?php

namespace work\modules\rbac\controllers;

use work\modules\rbac\controllers\base\ApiController;
use work\modules\rbac\models\Blog;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;

/**
 * MenuController implements the CRUD actions for Menu model.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since  1.0
 */
class RbacController extends Controller
{

    public $modelClass = '';
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            //附加行为
            'as access' => [
                'class'=>'backend\components\AccessControl',
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        //添加"/blog/index"权限---auth_item
        $blogIndex = $auth->createPermission('/rbac/rbac/index');
        $blogIndex->description = '列表';
        $auth->add($blogIndex);
        //创建一个角色blogManage。并为该角色分配"/blog/index"权限
        $blogManager = $auth->createRole('管理');
        $auth->add($blogManager);
        $auth->addChild($blogManager, $blogIndex);
        //为用户test1（user_id=4） 分配“管理” 权限
        $auth->assign($blogManager, 4);
    }
    
    public function actionInit2()
    {
        $auth = Yii::$app->authManager;
        //添加权限---auth_item
        $blogCreate = $auth->createPermission('/rbac/rbac/create');
        $auth->add($blogCreate);
        $blogUpdate = $auth->createPermission('/rbac/rbac/update');
        $auth->add($blogUpdate);
        $blogDelete = $auth->createPermission('/rbac/rbac/delete');
        $auth->add($blogDelete);
        //创建一个角色blogManage。并为该角色分配"/blog/index"权限
        $blogManage = $auth->getRole('管理');
        $auth->addChild($blogManage, $blogCreate);
        $auth->addChild($blogManage, $blogUpdate);
        $auth->addChild($blogManage, $blogDelete);
    }
    
    /**
     * Lists all Menu models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        if ( !Yii::$app->user->can('/rbac/rbac/index')) {
            throw new ForbiddenHttpException("没权限访问");
        }
        return "这是index方法";
    }
    
    public function actionDelete()
    {
        return "这是delete方法";
    }
    public function actionUpdate()
    {
        return "这是update方法";
    }
    public function actionCreate()
    {
        return "这是create方法";
    }
}
