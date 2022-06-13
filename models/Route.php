<?php

namespace wind\rest\models;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use Yii;
use wind\rest\components\Helper;
use yii\caching\TagDependency;
use wind\rest\components\RouteRule;
use wind\rest\components\Configs;
use yii\helpers\VarDumper;
use Exception;

/**
 * Description of Route
 *
 */
class Route extends BaseObject
{
    
    const CACHE_TAG = 'mdm.admin.route';
    
    /**
     * Assign or remove items（分配或者刪除項目）
     *
     * @param array $routes
     *
     * @return array
     */
    public function addNew($routes)
    {
        $manager = Yii::$app->getAuthManager();
        foreach ($routes as $val) {
            $route = $val['name'];
            try {
                $r = explode('&', $route);
                $item = $manager->createPermission('/' . trim($route, '/'));
                if (count($r) > 1) {
                    $action = '/' . trim($r[0], '/');
                    if (($itemAction = $manager->getPermission($action)) === null) {
                        $itemAction = $manager->createPermission($action);
                        $manager->add($itemAction);
                    }
                    unset($r[0]);
                    foreach ($r as $part) {
                        $part = explode('=', $part);
                        $item->data['params'][$part[0]] = isset($part[1]) ? $part[1] : '';
                    }
                    $this->setDefaultRule();
                    $item->ruleName = RouteRule::RULE_NAME;
                    $item->description = $val['description'];
                    $item->parent_name = $val['parent_name'];
                    $manager->add($item);
                    $manager->addChild($item, $itemAction);
                } else {
                    $item->description = $val['description'];
                    $item->parent_name = $val['parent_name'];
                    $manager->add($item);
                }
            } catch (Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
    }
    
    /**
     * Assign or remove items(删除节点)
     *
     * @param array $routes
     *
     * @return array
     */
    public function remove($routes)
    {
        $manager = Yii::$app->getAuthManager();
        foreach ($routes as $route) {
            try {
                //获取auth_item 表中全部信息
                $item = $manager->createPermission('/' . trim($route, '/'));
                $manager->remove($item);
            } catch (Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
    }
    
    /**
     * Get avaliable and assigned routes（获得指定/可使用的route）
     *
     * @return array
     */
    public function getRoutes()
    {
        $manager = Yii::$app->getAuthManager();
//        $routes = $this->getAppRoutes();
        $exists = [];
        foreach ($manager->getPermissions() as $name) {
            $name = ArrayHelper::toArray($name);
            if ($name['name'][0] !== '/') {
                continue;
            }
            $route['name'] = $name['name'];
            $route['description'] = $name['description'];
            $route['parent_name'] = $name['parent_name'];
            $exists[] = $route;
//            unset($routes[$name['name']]);
        }
        $exists = ArrayHelper::index($exists, null, 'parent_name');
        
        return [
//            'avaliable' => array_keys($routes),
            'assigned' => $exists
        ];
    }

//    /**
//     * Get avaliable and assigned routes（获得指定/可使用的route）
//     *
//     * @return array
//     */
//    public function getAll()
//    {
//        $manager = Yii::$app->getAuthManager();
//        $routes = $this->getAppRoutes();
//        $exists = [];
//        foreach ($manager->getPermissions() as $name) {
//            $route['name'] = $name->name;
//            $route['description'] = $name->description;
//            $exists[] = $route;
//            unset($routes[$name->name]);
//        }
//
//        return array_keys($routes);
//    }
    
    /**
     * Get list of application routes
     *
     * @return array
     */
    public function getAppRoutes($module = null)
    {
        if ($module === null) {
            $module = Yii::$app;
        } elseif (is_string($module)) {
            $module = Yii::$app->getModule($module);
        }
        $key = [__METHOD__, $module->getUniqueId()];
        $cache = Configs::instance()->cache;
        if ($cache === null || ($result = $cache->get($key)) === false) {
            $result = [];
            $this->getRouteRecrusive($module, $result);
            if ($cache !== null) {
                $cache->set($key, $result, Configs::instance()->cacheDuration, new TagDependency([
                    'tags' => self::CACHE_TAG,
                ]));
            }
        }
        
        return $result;
    }
    
    /**
     * Get route(s) recrusive
     *
     * @param \yii\base\Module $module
     * @param array            $result
     */
    protected function getRouteRecrusive($module, &$result)
    {
        $token = "Get Route of '" . get_class($module) . "' with id '" . $module->uniqueId . "'";
        Yii::beginProfile($token, __METHOD__);
        try {
            foreach ($module->getModules() as $id => $child) {
                if (($child = $module->getModule($id)) !== null) {
                    $this->getRouteRecrusive($child, $result);
                }
            }
            
            foreach ($module->controllerMap as $id => $type) {
                $this->getControllerActions($type, $id, $module, $result);
            }
            
            $namespace = trim($module->controllerNamespace, '\\') . '\\';
            $this->getControllerFiles($module, $namespace, '', $result);
            $all = '/' . ltrim($module->uniqueId . '/*', '/');
            $result[$all] = $all;
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
        Yii::endProfile($token, __METHOD__);
    }
    
    /**
     * Get list controller under module
     *
     * @param \yii\base\Module $module
     * @param string           $namespace
     * @param string           $prefix
     * @param mixed            $result
     *
     * @return mixed
     */
    protected function getControllerFiles($module, $namespace, $prefix, &$result)
    {
        $path = Yii::getAlias('@' . str_replace('\\', '/', $namespace), false);
        $token = "Get controllers from '$path'";
        Yii::beginProfile($token, __METHOD__);
        try {
            if ( !is_dir($path)) {
                return;
            }
            foreach (scandir($path) as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($path . '/' . $file) && preg_match('%^[a-z0-9_/]+$%i', $file . '/')) {
                    $this->getControllerFiles($module, $namespace . $file . '\\', $prefix . $file . '/', $result);
                } elseif (strcmp(substr($file, -14), 'Controller.php') === 0) {
                    $baseName = substr(basename($file), 0, -14);
                    $name = strtolower(preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $baseName));
                    $id = ltrim(str_replace(' ', '-', $name), '-');
                    $className = $namespace . $baseName . 'Controller';
                    if (strpos($className, '-') === false && class_exists($className) && is_subclass_of($className,
                            'yii\base\Controller')
                    ) {
                        $this->getControllerActions($className, $prefix . $id, $module, $result);
                    }
                }
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
        Yii::endProfile($token, __METHOD__);
    }
    
    /**
     * Get list action of controller
     *
     * @param mixed            $type
     * @param string           $id
     * @param \yii\base\Module $module
     * @param string           $result
     */
    protected function getControllerActions($type, $id, $module, &$result)
    {
        $token = "Create controller with cofig=" . VarDumper::dumpAsString($type) . " and id='$id'";
        Yii::beginProfile($token, __METHOD__);
        try {
            /* @var $controller \yii\base\Controller */
            $controller = Yii::createObject($type, [$id, $module]);
            $this->getActionRoutes($controller, $result);
            $all = "/{$controller->uniqueId}/*";
            $result[$all] = $all;
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
        Yii::endProfile($token, __METHOD__);
    }
    
    /**
     * Get route of action
     *
     * @param \yii\base\Controller $controller
     * @param array                $result all controller action.
     */
    protected function getActionRoutes($controller, &$result)
    {
        $token = "Get actions of controller '" . $controller->uniqueId . "'";
        Yii::beginProfile($token, __METHOD__);
        try {
            $prefix = '/' . $controller->uniqueId . '/';
            foreach ($controller->actions() as $id => $value) {
                $result[$prefix . $id] = $prefix . $id;
            }
            $class = new \ReflectionClass($controller);
            foreach ($class->getMethods() as $method) {
                $name = $method->getName();
                if ($method->isPublic() && !$method->isStatic() && strpos($name,
                        'action') === 0 && $name !== 'actions'
                ) {
                    $name = strtolower(preg_replace('/(?<![A-Z])[A-Z]/', ' \0', substr($name, 6)));
                    $id = $prefix . ltrim(str_replace(' ', '-', $name), '-');
                    $result[$id] = $id;
                }
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
        Yii::endProfile($token, __METHOD__);
    }
    
    /**
     * Ivalidate cache
     */
    public static function invalidate()
    {
        if (Configs::cache() !== null) {
            TagDependency::invalidate(Configs::cache(), self::CACHE_TAG);
        }
    }
    
    /**
     * Set default rule of parameterize route.
     */
    protected function setDefaultRule()
    {
        if (Yii::$app->getAuthManager()->getRule(RouteRule::RULE_NAME) === null) {
            Yii::$app->getAuthManager()->add(new RouteRule());
        }
    }
    
    /**
     * 父级路由名称
     *
     * @return array
     */
    public function parentList()
    {
        $manager = Yii::$app->getAuthManager();
        $result = $manager->getRouteParent();
        $result = array_column($result, 'parent_name');
        
        return $result;
    }
}
