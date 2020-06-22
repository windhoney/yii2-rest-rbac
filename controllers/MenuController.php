<?php

namespace wind\rest\controllers;

use yii\helpers\ArrayHelper;
use wind\rest\components\MenuHelper;
use wind\rest\controllers\base\ApiController;
use wind\rest\models\Menu;
use Yii;
use yii\filters\VerbFilter;
use wind\rest\components\Helper;

class MenuController extends ApiController
{
    
    /**
     * @var \wind\rest\models\Menu $menu_model $menu_model
     */
    public $menu_model;
    
    public function init()
    {
        parent::init();
        $this->menu_model = new Menu();
    }
    
    /**
     * 用户菜单
     *
     * @return array
     */
    public function actionUserMenu()
    {
        $callback = function ($menu) {
            $data = json_decode($menu['data'], true);
            $items = $menu['children'];
            $return['label'] = $menu['name'];
            $return['url'] = $menu['route'] ? substr($menu['route'], 1) : '';
            $return['icon'] = $data['icon'] ?? 'fa fa-circle-o';
            $return['visible'] = $data['visible'] ?? true;
            $items && $return['items'] = $items;
            
            return $return;
        };
        $refresh = Yii::$app->request->get('refresh', false);
        $result = MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback, $refresh, Yii::$app->request->get('client'));
        
        return $result;
    }
    
    /**
     * 所有菜单列表
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $res = $this->menu_model->getMenuSource();
        $is_tree = Yii::$app->request->get('is_tree');
        if ($is_tree) {
            return Helper::makeTree($res, 'name', 'parent_name', 'child', null);
        }
    
        return $res;
    }
    
    /**
     * 菜单详情
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $res = $this->menu_model->findModel($id);
        $parent_name = $res->parent_name;
        $description = $res->description;
        $res = ArrayHelper::toArray($res);
        $res['parent_name'] = $parent_name;
        $res['description'] = $description;
        
        return $res;
    }
    
    /**
     * 添加菜单
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $result = $this->menu_model->create();
        
        return $this->menu_model->findModel($result['id']);
    }
    
    /**
     * 更新
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $result = $this->menu_model->updateMenu($id);
        
        return $this->menu_model->findModel($result['id']);
    }
    
    /**
     * 删除
     *
     * @param $id
     *
     * @return array
     */
    public function actionDelete($id)
    {
        $this->menu_model->deleteMenu($id);
        Helper::invalidate();
        
        return $this->menu_model->getMenuSource();
    }
}