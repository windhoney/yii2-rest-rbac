<?php

namespace wind\rest\models\searchs;

use Yii;
use yii\base\Model;
use wind\rest\models\User as UserModel;

/**
 * User represents the model behind the search form about `wind\rest\models\User`.
 */
class User extends UserModel
{
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'auth_key', 'password_hash', 'password_reset_token', 'email'], 'safe'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }
    
    /**
     * @param $params
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function search($params)
    {
        $query = UserModel::find();
        $this->setAttributes($params);
        $status = $this->status ?: parent::STATUS_ACTIVE;
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
        $query->select([
            "id",
            "username",
            "realname",
            "email",
            "status",
            "created_at",
        ]);
        $query->andFilterWhere(['like', 'username', $this->username]);
        $query->andFilterWhere(['like', 'email', $this->email]);
        $query->andFilterWhere(['status' => $status]);
        $query->orderBy(['id' => 'asc']);
        $result = $query->asArray()->all();
        foreach ($result as $key => $value) {
            if ($value['created_at']) {
                $result[$key]['created_at'] = date('Y年m月d日', $value['created_at']);
            } else {
                $result[$key]['created_at'] = '--';
            }
        }
        
        return $result;
    }
    
    public function allUsers($select = '*')
    {
        $query = UserModel::find();
        $query->select($select);
        $query->andWhere(['status' => parent::STATUS_ACTIVE]);
        $result = $query->asArray()->all();
        
        return $result;
    }
}
