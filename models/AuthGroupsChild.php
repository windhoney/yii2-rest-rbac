<?php

namespace wind\rest\models;

use Yii;

/**
 * Class AuthGroups
 *
 * @property string $user_id
 * @property string $group_id
 * @package  wind\rest\models
 */
class AuthGroupsChild extends \yii\db\ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Yii::$app->authManager->groupChildTable;
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id'], 'string', 'max' => 50],
            [['user_id'], 'string', 'max' => 64],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'group_id' => 'GROUP ID',
            'user_id' => '用户id',
        ];
    }
    
    /**
     * 关联用户表
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    /**
     *  添加分组下用户
     *
     * @param $data
     *
     * @return bool
     */
    public function add($group_id, $user_id)
    {
        $model = clone $this;
        $model->group_id = $group_id;
        $model->user_id = $user_id;
        
        return $model->save();
    }
    
    /**
     *  移除分组下用户
     *
     * @param $data
     *
     * @return bool
     */
    public function remove($group_id, $user_id)
    {
        if (empty($user_id)) {
            return false;
        }
        $model = clone $this;
        $result = $model->deleteAll(['user_id' => $user_id, 'group_id' => $group_id]);
        
        return $result;
    }
    
    /**
     * 分组下用户
     *
     * @param $group_id
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function assigned($group_id)
    {
        $model = clone $this;
        $query = $model->find();
        $query->joinWith('user', false, 'RIGHT JOIN');
        $query->select(['user.id', 'realname']);
        $query->andWhere(['group_id' => $group_id]);
        $result = $query->asArray()->all();
        
        return $result;
    }
    
    /**
     * 所有已分组的用户
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function assignedAll()
    {
        $model = clone $this;
        $query = $model->find();
        
        return $query->asArray()->all();
    }
}
