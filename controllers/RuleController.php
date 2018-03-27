<?php

namespace wind\rest\controllers;

use wind\rest\models\BizRule;
use wind\rest\models\searchs\BizRule as BizRuleSearch;
use wind\rest\components\Helper;
use wind\rest\controllers\base\ApiController;
use Yii;
use yii\filters\VerbFilter;

/**
 * Description of RuleController
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since  1.0
 */
class RuleController extends ApiController
{

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
                    'assign' => ['post', 'get'],
                    'remove' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Creates a new AuthItems model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new BizRule(null);
        if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
            Helper::invalidate();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates an existing AuthItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param  string $id
     *
     * @return mixed
     */
    public function actionUpdate()
    {
        $name = Yii::$app->request->getBodyParam("name");
        $delete_result = $this->actionDelete();
        if ($delete_result == false) {
            return $delete_result;
        }
        $create_result = $this->actionCreate();

        return $create_result;
    }

    /**
     * Deletes an existing AuthItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param  string $id
     *
     * @return mixed
     */
    public function actionDelete()
    {
        $name = Yii::$app->request->getBodyParam("name");
        $model = $this->findModel($name);

        if ($model != false) {
            Yii::$app->authManager->remove($model->item);
            Helper::invalidate();

            return true;
        }

        return false;
    }

    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $id
     *
     * @return AuthItem      the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $item = Yii::$app->authManager->getRule($id);
        if ($item) {
            return new BizRule($item);
        } else {
            return false;
//            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionGetRules()
    {
        $rules = Yii::$app->getAuthManager()->getRules();
        $result_rules = [];
        foreach ($rules as $rule_name => $rule_obj) {
            $r_obj["rule_name"] = $rule_name;
            $r_obj["rule_class"] = $rule_obj->className();
            $result_rules[] = $r_obj;
        }

        return $result_rules;
    }
}
