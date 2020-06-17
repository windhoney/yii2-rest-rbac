<?php

namespace wind\rest\models;

use wind\rest\components\Helper;
use Yii;

/**
 * AuthGroups model
 *
 * @property string $group_name
 * @property string $group_id
 * @property string $group_status
 * @package  wind\rest\models
 */
class AuthGroups extends \yii\db\ActiveRecord
{
    
    const STATUS_OPEN = '0';
    const STATUS_CLOSE = '1';
    public $id;
    
    public function __construct($id = null, array $config = [])
    {
        parent::__construct($config);
        $this->id = $id;
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Yii::$app->authManager->groupTable;
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id'], 'string', 'max' => 50],
            [['group_name'], 'string', 'max' => 100],
            [['group_status'], 'string', 'max' => 50],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'group_id' => '分组ID',
            'group_name' => '分组名称',
            'group_status' => '分组状态',
        ];
    }
    
    /**
     * 可用列表
     * @param null $is_all 是否查询所有分组
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function lists($is_all = null)
    {
        $model = clone $this;
        $query = $model->find();
        $query->select(['group_id', 'group_name']);
        if ( !$is_all) {
            $query->andWhere(['group_status' => self::STATUS_OPEN]);
        }
        $result = $query->asArray()->all();
        
        return $result;
    }
    
    /**
     * 分配添加
     *
     * @param $id
     *
     * @return bool
     */
    public function assign()
    {
        $data = Yii::$app->getRequest()->post();
        foreach ($data as $val) {
            try {
                $group_child_model = new AuthGroupsChild();
                $group_child_model->add($this->id, $val);
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
        
        return true;
    }
    
    /**
     * 分配删除
     *
     * @param $id
     *
     * @return bool
     */
    public function revoke()
    {
        $data = Yii::$app->getRequest()->post();
        foreach ($data as $val) {
            try {
                $group_child_model = new AuthGroupsChild();
                $group_child_model->remove($this->id, $val);
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
        
        return true;
    }
    
    /**
     * 分配用户列表
     *
     * @return array
     */
    public function assignUser($single_group = null)
    {
        $group_child_model = new AuthGroupsChild();
        //assign 已分配的
        $assign = $group_child_model->assigned($this->id);
        if ($single_group) {
            $assign_filter = $group_child_model->assignedAll();
        } else {
            $assign_filter = $assign;
        }
        $user_id_arr = array_filter(array_column($assign_filter, 'id'));
        //all  所有
        $user_model = new \wind\rest\models\searchs\User();
        $all = $user_model->allUsers(['id', 'realname']);
        foreach ($all as $key => $val) {
            if (in_array($val['id'], $user_id_arr)) {
                unset($all[$key]);
            }
        }
        sort($all);
        
        return compact(['all', 'assign']);
    }
    
    /**
     * 添加
     *
     * @param $group_id
     * @param $group_name
     *
     * @return bool
     */
    public function create($group_id, $group_name)
    {
        $model = $this::findOne(['group_id' => $group_id]) ?: clone $this;
        $model->group_id = $group_id;
        $model->group_name = $group_name;
        if ( !$model->save()) {
            \Yii::error($model->errors);
            
            return false;
        }
        
        return true;
    }
    
    /**
     * 停用|启用
     *
     * @param $group_id
     * @param $group_status
     *
     * @return bool
     */
    public function status($group_id, $group_status)
    {
        $model = $this::findOne(['group_id' => $group_id]);
        $model->group_id = $group_id;
        $model->group_status = $group_status;
        if ( !$model->save()) {
            \Yii::error($model->errors);
            
            return false;
        }
        
        return true;
    }
}
