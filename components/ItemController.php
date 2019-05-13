<?php

namespace wind\rest\components;

use wind\rest\helper\RbacHelper;
use yii\helpers\ArrayHelper;
use wind\rest\controllers\base\ApiController;
use Yii;
use wind\rest\models\AuthItem;
use wind\rest\models\searchs\AuthItem as AuthItemSearch;
use yii\base\NotSupportedException;
use yii\filters\VerbFilter;

/**
 * AuthItemController implements the CRUD actions for AuthItem model.
 *
 * @property integer $type
 * @property array   $labels
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since  1.0
 */
class ItemController extends ApiController
{
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'assign' => ['post', 'get'],
                    'remove' => ['post'],
                ],
            ],
        ]);
    }
    
    /** @type  \wind\rest\models\AuthItem $auth_item_model */
    public $auth_item_model;
    
    public function init()
    {
        parent::init();
        $this->auth_item_model = new AuthItem();
    }
    
    /**
     * 列表.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AuthItemSearch(['type' => $this->type]);
        $res = $searchModel->search(Yii::$app->request->getQueryParams());
        
        return $res;
    }
    
    /**
     * 详情.
     *
     * @param  string $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->auth_item_model->findModel($id, $this->type);
        if ( !$model) {
            return false;
        }
        
        return ArrayHelper::toArray($model->_item);
    }
    
    /**
     * 权限 | 角色 添加
     *
     * @return array|bool|\wind\rest\models\AuthItem
     */
    public function actionCreate()
    {
        $res = $this->auth_item_model->addPermission($this->type);
        Helper::invalidate();
        if ( !$res) {
            RbacHelper::error();
        }
        if ($this->type == 1) {
            return ArrayHelper::toArray($res['_item']);
        }
        
        return $this->auth_item_model->getItems();
    }
    
    /**
     * 修改 权限 |角色
     *
     * @param $id
     *
     * @return array
     */
    public function actionUpdate($id)
    {
        $this->auth_item_model->updatePermission($id, $this->type);
        Helper::invalidate();
        if ($this->type == 1) {
            $res = $this->auth_item_model->findModel($id, $this->type);
            
            return ArrayHelper::toArray($res['_item']);
        }
        
        return $this->auth_item_model->getItems();
    }
    
    /**
     * 删除权限
     *
     * @param $id
     *
     * @return array|bool
     */
    public function actionDelete($id)
    {
        if ( !$this->auth_item_model->deletePermission($id, $this->type)) {
            return false;
        }
        Helper::invalidate();
        
        return $this->auth_item_model->getItems();
    }
    
    /**
     * 对于某个权限添加路由（左边到右边）
     *
     * @param string $id
     *
     * @return array
     */
    public function actionAssign($id)
    {
        $this->auth_item_model->assignItem($id, $this->type, 'add');

        return true;
    }
    
    /**
     * 对于某个权限删除路由（左边到右边）
     *
     * @param $id
     *
     * @return array
     */
    public function actionRemove($id)
    {
        $this->auth_item_model->assignItem($id, $this->type, 'remove');
        
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function getViewPath()
    {
        return $this->module->getViewPath() . DIRECTORY_SEPARATOR . 'item';
    }
    
    /**
     * Label use in view
     *
     * @throws NotSupportedException
     */
    public function labels()
    {
        throw new NotSupportedException(get_class($this) . ' does not support labels().');
    }
    
    /**
     * 可分配列表
     *
     * @param $id
     *
     * @return array|bool
     */
    public function actionAssignList($id)
    {
        $model = $this->auth_item_model->findModel($id, $this->type);
        Yii::$app->getResponse()->format = 'json';
        
        if ( !$model) {
            return false;
        }
        
        return $model->getItemsList();
    }
}
