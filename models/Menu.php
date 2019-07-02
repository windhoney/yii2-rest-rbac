<?php

namespace wind\rest\models;

use wind\rest\components\Helper;
use wind\rest\helper\RbacHelper;
use Yii;
use wind\rest\components\Configs;
use yii\db\Query;

/**
 * This is the model class for table "menu".
 *
 * @property integer $id         Menu id(autoincrement)
 * @property string  $name       Menu name
 * @property integer $parent     Menu parent
 * @property string  $route      Route for this menu
 * @property integer $order      Menu order
 * @property string  $data       Extra information for this menu
 *
 * @property Menu    $menuParent Menu parent
 * @property Menu[]  $menus      Menu children
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since  1.0
 */
class Menu extends \yii\db\ActiveRecord
{
    
    public $parent_name;
    public $description;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Configs::instance()->menuTable;
    }
    
    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        if (Configs::instance()->db !== null) {
            return Configs::instance()->db;
        } else {
            return parent::getDb();
        }
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [
                ['parent_name'],
                'in',
                'range' => static::find()->select(['name'])->column(),
                'message' => 'Menu "{value}" not found.'
            ],
            [['parent', 'route', 'data', 'order', 'remark'], 'default'],
            [
                ['parent'],
                'filterParent',
                'when' => function () {
                    return !$this->isNewRecord;
                }
            ],
            [['order'], 'integer'],
            [
                ['route'],
                'in',
                'range' => static::getSavedRoutes(),
                'message' => 'Route "{value}" not found.'
            ]
        ];
    }
    
    /**
     * 添加菜单
     *
     * @return bool|\wind\rest\models\Menu
     */
    public function create()
    {
        $model = new Menu;
        $model->load(Yii::$app->request->post(), '');
        if ($model->save()) {
            Helper::invalidate();
            
            return $model;
        } else {
            RbacHelper::recordLog($model->errors, 'create', 'Menu');
            
            return false;
        }
    }
    
    /**
     * Use to loop detected.(使用循环检测)
     */
    public function filterParent()
    {
        $parent = $this->parent;
        $db = static::getDb();
        $query = (new Query)->select(['parent'])
            ->from(static::tableName())
            ->where('[[id]]=:id');
        while ($parent) {
            if ($this->id == $parent) {
                $this->addError('parent_name', 'Loop detected.');
                
                return;
            }
            $parent = $query->params([':id' => $parent])->scalar($db);
        }
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',//ii::t('rbac-admin', 'ID'),
            'name' => 'name',//Yii::t('rbac-admin', 'Name'),
            'parent' => 'parent',//Yii::t('rbac-admin', 'Parent'),
            'parent_name' => 'parent_name',//Yii::t('rbac-admin', 'Parent Name'),
            'route' => 'route',//Yii::t('rbac-admin', 'Route'),
            'order' => 'order',//Yii::t('rbac-admin', 'Order'),
            'data' => 'data',//Yii::t('rbac-admin', 'Data'),
            'remark' => 'remark',//Yii::t('rbac-admin', 'Data'),
        ];
    }
    
    /**
     * Get menu parent
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMenuParent()
    {
        return $this->hasOne(Menu::className(), ['id' => 'parent']);
    }
    
    /**
     * Get menu children
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMenus()
    {
        return $this->hasMany(Menu::className(), ['parent' => 'id']);
    }
    
    private static $_routes;
    
    /**
     * Get saved routes.
     *
     * @return array
     */
    public static function getSavedRoutes()
    {
        if (self::$_routes === null) {
            self::$_routes = [];
            foreach (Yii::$app->getAuthManager()->getPermissions() as $name => $value) {
                if ($name[0] === '/' && substr($name, -1) != '*') {
                    self::$_routes[] = $name;
                }
            }
        }
        
        return self::$_routes;
    }
    
    /**
     * 菜单列表
     *
     * @return array
     */
    public function getMenuSource()
    {
        $tableName = static::tableName();
        $res = (new \yii\db\Query())->select([
            'm.id',
            'm.name',
            'm.route',
            'parent_name' => 'p.name',
            'm.order',
            'm.remark',
            'a.description as route_name'
        ])
            ->from(['m' => $tableName])
            ->leftJoin(['p' => $tableName], '[[m.parent]]=[[p.id]]')
            ->leftJoin(['a' => 'auth_item'], '`m`.`route`= CONVERT(`a`.`name` USING utf8) COLLATE utf8_unicode_ci')
            ->all(static::getDb());
        
        return $res;
    }
    
    /**
     * 菜单详情
     *
     * @param $id
     *
     * @return bool|\wind\rest\models\Menu
     */
    public function findModel($id)
    {
        if (($model = $this::findOne($id)) !== null) {
            $model->parent_name = $model->menuParent->name ?? null;
            $manager = Yii::$app->authManager;
            if ($model->route) {
                $model->description = $manager->getPermission($model->route)->description;
            }
            
            return $model;
        } else {
            return false;
        }
    }
    
    /**
     * 更新
     *
     * @param $id
     *
     * @return bool|\wind\rest\models\Menu
     */
    public function updateMenu($id)
    {
        $model = $this->findModel($id);
        if ($model->menuParent) {
            $model->parent_name = $model->menuParent->name;
        }
        
        if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
            Helper::invalidate();
            
            return $model;
        } else {
            RbacHelper::recordLog($model->errors, 'update', 'Menu');
            
            return false;
        }
    }
    
    /**
     * 删除
     *
     * @param $id
     *
     * @return bool|int
     */
    public function deleteMenu($id)
    {
        $res = $this->findModel($id)->delete();
        
        return $res;
    }
}
