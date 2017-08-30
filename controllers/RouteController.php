<?php

namespace wind\rest\controllers;

use yii\helpers\ArrayHelper;
use wind\rest\controllers\base\ApiController;
use wind\rest\models\Route;
use Yii;
use yii\filters\VerbFilter;

/**
 * Description of RuleController
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since  1.0
 */
class RouteController extends ApiController
{
    
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['post'],
                    'assign' => ['post'],
                    'remove' => ['post'],
                ],
            ],
        ]);
    }
    
    /**
     * 已分配的路由列表
     *
     * @return array
     */
    public function actionIndex()
    {
        $model = new Route();
        $result = $model->getRoutes();
        
        return $result['assigned'];
    }
    
    /**
     * 可用路由列表
     *
     * @return array
     */
    public function actionAll()
    {
        $model = new Route();
        $result = $model->getRoutes();
        
        return $result['avaliable'];
    }
    
    /**
     * 添加路由
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $routes = Yii::$app->getRequest()->post();
        $model = new Route();
        $model->addNew($routes);
        
        return $model->getRoutes()['assigned'];
    }
    
    /**
     * 删除路由
     *
     * @return array
     */
    public function actionRemove()
    {
        $routes = Yii::$app->getRequest()->post();
        $model = new Route();
        $model->remove($routes);
        Yii::$app->getResponse()->format = 'json';
        
        return $model->getRoutes()['assigned'];
    }
    
    /**
     * 父级路由列表
     *
     * @return array
     */
    public function actionParent()
    {
        $model = new Route();
        $result = $model->parentList();
        $result = array_filter($result);
        sort($result);
        
        return $result;
    }
    
}
