<?php

namespace wind\rest\controllers\base;

use wind\rest\helper\RbacHelper;
use yii\filters\AccessControl;
use yii\rest\ActiveController;
use yii\helpers\ArrayHelper;

use yii\filters\auth\QueryParamAuth;
use filsh\yii2\oauth2server\filters\auth\CompositeAuth;
use yii\web\UnauthorizedHttpException;

/**
 * 所有接口基类，授权验证，格式设定
 *
 * @author windhoney
 * @package wind\rest\controllers\base
 */
class ApiController extends ActiveController
{
    
    protected $user_id;
    public $modelClass = '';
    public $params;
    
    public function init()
    {
        parent::init();
        $this->user_id = \Yii::$app->user->id;
        $this->getRequestParam();
    }
    
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => CompositeAuth::className(),
                'authMethods' => [
                    ['class' => QueryParamAuth::className(), 'tokenParam' => 'access_token'],
                ]
            ]
        ]);
    }
    
    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        
        return $actions;
    }
    
    /**
     * 接收请求参数
     */
    public function getRequestParam()
    {
        if (\Yii::$app->request->isPost) {
            $data = \Yii::$app->request->getRawBody();
            $post = json_decode($data, true);
            if ( !$post && $data) {
                RbacHelper::error(400, '数据格式不正确');
            } else {
                $this->params = $post;
            }
        } else {
            $this->params = \Yii::$app->request->queryParams;
        }
    }
    
    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'PUT', 'POST'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        \Yii::$app->response->format = "json";
        $result = parent::afterAction($action, $result);
        $data['code'] = isset($result['code']) ? $result['code'] : 200;
        $data['message'] = isset($result['message']) ? $result['message'] : '操作成功';
        if ($result === null) {
            $result = [];
        }
        if ($data['code'] == 200 && (is_array($result))) {
            $data['data'] = $result;
        }
        if ($result === false) {
            $data['code'] = 400;
            $data['message'] = '操作失败';
        }
        
        return $this->serializeData($data);
    }
}
