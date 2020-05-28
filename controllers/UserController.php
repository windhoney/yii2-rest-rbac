<?php

namespace wind\rest\controllers;

use yii\helpers\ArrayHelper;
use wind\rest\controllers\base\ApiController;
use Yii;
use wind\rest\models\form\Login;
use wind\rest\models\form\PasswordResetRequest;
use wind\rest\models\form\ResetPassword;
use wind\rest\models\form\Signup;
use wind\rest\models\form\ChangePassword;
use wind\rest\models\User;
use wind\rest\models\searchs\User as UserSearch;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\mail\BaseMailer;

/**
 * User controller
 */
class UserController extends ApiController
{
    
    /**
     * 用户列表
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $result = $searchModel->search(Yii::$app->request->queryParams);
        
        return $result;
    }
    
    /**
     * 用户详情
     *
     * @param $id
     *
     * @return \wind\rest\models\User
     */
    public function actionView($id)
    {
        $select = [
            "id",
            "username",
            "realname",
            "email",
            "status",
            "created_at",
            "updated_at"
        ];
        $result = $this->findModel($id, $select);
        $result->created_at = date('Y年m月d日', $result->created_at);
        $result->updated_at = date('Y年m月d日', $result->updated_at);
    
        return $result;
    }
    
    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        
        return $this->redirect(['index']);
    }
    
    /**
     * Login
     *
     * @return string
     */
    public function actionLogin()
    {
        if ( !Yii::$app->getUser()->isGuest) {
            return $this->goHome();
        }
        
        $model = new Login();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * Logout
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->getUser()->logout();
        
        return $this->goHome();
    }
    
    /**
     * Signup new user
     *
     * @return string
     */
    public function actionSignup()
    {
        $model = new Signup();
        if ($model->load(Yii::$app->getRequest()->post())) {
            if ($user = $model->signup()) {
                return $this->goHome();
            }
        }
        
        return $this->render('signup', [
            'model' => $model,
        ]);
    }
    
    /**
     * Request reset password
     *
     * @return string
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequest();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');
                
                return $this->goHome();
            } else {
                Yii::$app->getSession()->setFlash('error',
                    'Sorry, we are unable to reset password for email provided.');
            }
        }
        
        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }
    
    /**
     * Reset password
     *
     * @return string
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPassword($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        
        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', 'New password was saved.');
            
            return $this->goHome();
        }
        
        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
    
    /**
     * Reset password
     *
     * @return string
     */
    public function actionChangePassword()
    {
        $model = new ChangePassword();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->change()) {
            return $this->goHome();
        }
        
        return $this->render('change-password', [
            'model' => $model,
        ]);
    }
    
    /**
     * 修改用户状态
     *
     * @param $id
     *
     * @return bool
     */
    public function actionActivate($id)
    {
        /* @var $user User */
        $user = $this->findModel($id);
        if ($user->status == User::STATUS_INACTIVE) {
            $user->status = User::STATUS_ACTIVE;
            if ($user->save()) {
                return true;
//                return $this->goHome();
            } else {
                $errors = $user->firstErrors;
                
                return false;
//                throw new UserException(reset($errors));
            }
        }
        
        return true;
//        return $this->goHome();
    }
    
    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $select = ['*'])
    {
        if (($model = User::find()->andWhere(['id' => $id])->select($select)->one()) !== null) {
            return $model;
        } else {
            return false;
        }
    }
}
