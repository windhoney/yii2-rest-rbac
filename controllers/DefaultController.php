<?php

namespace work\modules\rbac\controllers;

use work\modules\rbac\components\MenuHelper;
use Yii;

/**
 * DefaultController
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class DefaultController extends \yii\web\Controller
{

    /**
     * Action index
     */
    public function actionIndex($page = 'README.md')
    {
//        $controller = $this->context;
//        $menus = $this->module->menus;
//        $route = $this->route;
//        foreach ($menus as $i => $menu) {
//            $menus[$i]['active'] = strpos($route, trim($menu['url'][0], '/')) === 0;
//        }
//        $callback = function ($menu) {
//            $data = json_decode($menu['data'], true);
//            $items = $menu['children'];
//            $return = [
//                'label' => $menu['name'],
//                'url' => [$menu['route']],
//            ];
//            //处理我们的配置
//            if ( $data ) {
//                //visible
//                isset($data['visible']) && $return['visible'] = $data['visible'];
//                //icon
//                isset($data['icon']) && $data['icon'] && $return['icon'] = $data['icon'];
//                //other attribute e.g. class...
//                $return['options'] = $data;
//            }
//            //没配置图标的显示默认图标
//            ( !isset($return['icon']) || !$return['icon']) && $return['icon'] = 'fa fa-circle-o';
//            $items && $return['items'] = $items;
//
//            return $return;
//        };
        $a = MenuHelper::getAssignedMenu(Yii::$app->user->id);
        print_r($a);die;
//        echo 12236664;die;
//        if (strpos($page, '.png') !== false) {
//            $file = Yii::getAlias("@mdm/admin/{$page}");
//            return Yii::$app->getResponse()->sendFile($file);
//        }
//        return $this->render('index', ['page' => $page]);
    }
}
