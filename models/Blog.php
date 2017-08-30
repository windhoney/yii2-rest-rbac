<?php

namespace wind\rest\models;

use Yii;

/**
 * This is the model class for table "blog".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property integer $views
 * @property integer $is_delete
 * @property string $created_at
 * @property string $update_at
 */
class Blog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'blog';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content', 'views', 'is_delete', 'created_at', 'update_at'], 'required'],
            [['content'], 'string'],
            [['views', 'is_delete'], 'integer'],
            [['created_at', 'update_at'], 'safe'],
            [['title'], 'string', 'max' => 11],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'content' => 'Content',
            'views' => 'Views',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'update_at' => 'Update At',
        ];
    }
    
    public function lists()
    {
        $query = $this->find();
        $res = $query->asArray()->all();
        return $res;
    }
}
