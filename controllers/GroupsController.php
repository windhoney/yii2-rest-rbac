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
    
    
}
