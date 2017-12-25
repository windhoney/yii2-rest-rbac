<?php

namespace wind\rest\controllers;

use wind\rest\controllers\base\ApiController;
use wind\rest\models\AuthItem;
use Yii;
use wind\rest\models\Assignment;
use wind\rest\models\searchs\Assignment as AssignmentSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AssignmentController implements the CRUD actions for Assignment model.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since  1.0
 */
class AssignmentController extends ApiController
{
    
    public $userClassName;
    public $idField = 'id';
    public $usernameField = 'username';
    public $fullnameField;
    public $searchClass;
    public $extraColumns = [];
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->userClassName === null) {
            $this->userClassName = Yii::$app->getUser()->identityClass;
            $this->userClassName = $this->userClassName ?: 'rbac\models\User';
        }
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'assign' => ['post'],
                    'revoke' => ['post'],
                ],
            ],
        ];
    }
    
    /**
     * 可分配列表
     *
     * @param $id
     *
     * @return array
     */
    public function actionAssignList($id)
    {
        $model = new Assignment($id);
        Yii::$app->getResponse()->format = 'json';
        
        return $model->getItemsList();
    }
    
    /**
     * Lists all Assignment models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        if ($this->searchClass === null) {
            $searchModel = new AssignmentSearch;
            $dataProvider = $searchModel->search(Yii::$app->getRequest()->getQueryParams(), $this->userClassName,
                $this->usernameField);
        } else {
            $class = $this->searchClass;
            $searchModel = new $class;
            $dataProvider = $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        }
        
        return $dataProvider;
    }
    
    /**
     * Displays a single Assignment model.
     *
     * @param  integer $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $assignment_model = new Assignment($id);
        $res = $assignment_model->findModel($id, $this->userClassName);
        $data['id'] = $res->user['id'];
        $data['username'] = $res->user['username'];
        
        return $data;
    }
    
    /**
     * Assign items
     *
     * @param string $id
     *
     * @return array
     */
    public function actionAssign($id)
    {
        $items = Yii::$app->getRequest()->post();
        $model = new Assignment($id);
        $success = $model->assign($items);
//        Yii::$app->getResponse()->format = 'json';

//        return array_merge($model->getItems(), ['success' => $success]);
        return boolval($success);
    }
    
    /**
     * Assign items
     *
     * @param string $id
     *
     * @return array
     */
    public function actionRevoke($id)
    {
        $items = Yii::$app->getRequest()->post();
        $model = new Assignment($id);
        $success = $model->revoke($items);
        Yii::$app->getResponse()->format = 'json';

//        return array_merge($model->getItems(), ['success' => $success]);
        return boolval($success);
    }
    
    /**
     * Finds the Assignment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  integer $id
     *
     * @return Assignment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $class = $this->userClassName;
        if (($user = $class::findIdentity($id)) !== null) {
            return new Assignment($id, $user);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    /**
     * 批量分配角色到人
     *
     * @param array $id
     *
     * @return array
     */
    public function actionAssignBatch($id)
    {
        $users = $this->params;
        $model = new Assignment($id);
        $success = $model->assignBatch($users);
        
        return boolval($success);
    }
    
    /**
     * 权限|角色 已分配人，未分配人
     *
     * @param $id
     *
     * @return array
     */
    public function actionAssignUsers($id)
    {
        $model = new Assignment($id);
        $result = $model->assignUser();
        
        return $result;
    }
    
    /**
     * 批量删除角色的人
     *
     * @param array $id
     *
     * @return array
     */
    public function actionRemoveUsers($id)
    {
        $users = $this->params;
        $model = new Assignment($id);
        $success = $model->assignRemoveUsers($users);
        
        return boolval($success);
    }
}
