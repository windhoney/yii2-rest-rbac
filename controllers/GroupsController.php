<?php
/**
 * 用户分组
 */

namespace wind\rest\controllers;

use wind\rest\controllers\base\ApiController;
use wind\rest\models\AuthGroups;

class GroupsController extends ApiController
{
    
    /**
     * @var \wind\rest\models\AuthGroups
     */
    public $group_model;
    
    public function init()
    {
        parent::init();
        $id = \Yii::$app->request->get('id');
        $this->group_model = new AuthGroups($id);
    }
    
    /**
     * 可用店铺列表
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionIndex()
    {
        $result = $this->group_model->lists();
        
        return $result;
    }
    
    /**
     * 分配添加
     *
     * @return bool
     */
    public function actionAssign()
    {
        $result = $this->group_model->assign();
        
        return $result;
    }
    
    /**
     * 分配删除
     *
     * @return bool
     */
    public function actionRevoke()
    {
        $result = $this->group_model->revoke();
        
        return $result;
    }
    
    /**
     * 分配列表
     *
     * @return array
     */
    public function actionAssignUser()
    {
        $result = $this->group_model->assignUser();
        
        return $result;
    }
    
    /**
     * 添加|修改
     * 只能修改名称
     *
     * @return bool
     */
    public function actionCreate()
    {
        $group_id = \Yii::$app->request->post('group_id');
        $group_name = \Yii::$app->request->post('group_name');
        
        return $this->group_model->create($group_id, $group_name);
    }
    
    /**
     * 停用|启用
     *
     * @return bool
     */
    public function actionStatus()
    {
        $group_id = \Yii::$app->request->post('group_id');
        $group_status = \Yii::$app->request->post('group_status');
        
        return $this->group_model->status($group_id, $group_status);
    }
}
