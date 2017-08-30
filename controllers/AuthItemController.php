<?php

namespace wind\rest\controllers;

use wind\rest\controllers\base\ApiController;
use wind\rest\models\AuthItem;
use yii\filters\VerbFilter;

class AuthItemController extends ApiController
{
    
    /**
     * @var \wind\rest\models\AuthItem $auth_item_model $auth_item_model
     */
    public $auth_item_model;
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
    
    public function init()
    {
        parent::init();
        $this->auth_item_model = new AuthItem();
    }
    
    public function actionIndex()
    {
        $res = $this->auth_item_model->getItems();
        
        return $res;
    }
    
    public function actionCreate()
    {
        $res = $this->auth_item_model->save();
        
        return $res;
    }
    
}
