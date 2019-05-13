<?php

namespace wind\rest\models;

use wind\rest\components\Helper;
use wind\rest\helper\RbacHelper;
use yii\helpers\ArrayHelper;
use Yii;
use yii\rbac\Item;
use yii\helpers\Json;
use yii\base\Model;

/**
 * This is the model class for table "tbl_auth_item".
 *
 * @property string  $name
 * @property integer $type
 * @property string  $description
 * @property string  $ruleName
 * @property string  $data
 *
 * @property Item    $item
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since  1.0
 */
class AuthItem extends Model
{
    
    public $name;
    public $type;
    public $description;
    public $ruleName;
    public $data;
    public $parent;
    /**
     * @var Item
     */
    public $_item;
    
    /**
     * Initialize object
     *
     * @param Item  $item
     * @param array $config
     */
    public function __construct($item = null, $config = [])
    {
        $this->_item = $item;
        if ($item !== null) {
            $this->name = $item->name;
            $this->type = $item->type;
            $this->description = $item->description;
            $this->ruleName = $item->ruleName;
            $this->data = $item->data === null ? null : Json::encode($item->data);
        }
        parent::__construct($config);
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ruleName'], 'checkRule'],
            [['name', 'type'], 'required'],
            [
                ['name'],
                'checkUnique',
                'when' => function () {
                    return $this->isNewRecord || ($this->_item->name != $this->name);
                }
            ],
            [['type'], 'integer'],
            [['description', 'data', 'ruleName'], 'default'],
            [['name'], 'string', 'max' => 64]
        ];
    }
    
    /**
     * Check role is unique
     */
    public function checkUnique()
    {
        $authManager = Yii::$app->authManager;
        $value = $this->name;
        if ($authManager->getRole($value) !== null || $authManager->getPermission($value) !== null) {
            $this->addError('name');
        }
    }
    
    /**
     * Check for rule
     */
    public function checkRule()
    {
        $name = $this->ruleName;
        if ( !Yii::$app->getAuthManager()->getRule($name)) {
            try {
                $rule = Yii::createObject($name);
                if ($rule instanceof \yii\rbac\Rule) {
                    $rule->name = $name;
                    Yii::$app->getAuthManager()->add($rule);
                } else {
                    $this->addError('ruleName', Yii::t('rbac-admin', 'Invalid rule "{value}"', ['value' => $name]));
                }
            } catch (\Exception $exc) {
                $this->addError('ruleName', Yii::t('rbac-admin', 'Rule "{value}" does not exists', ['value' => $name]));
            }
        }
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('rbac-admin', 'Name'),
            'type' => Yii::t('rbac-admin', 'Type'),
            'description' => Yii::t('rbac-admin', 'Description'),
            'ruleName' => Yii::t('rbac-admin', 'Rule Name'),
            'data' => Yii::t('rbac-admin', 'Data'),
        ];
    }
    
    /**
     * Check if is new record.
     *
     * @return boolean
     */
    public function getIsNewRecord()
    {
        return $this->_item === null;
    }
    
    /**
     * Find role
     *
     * @param string $id
     *
     * @return null|\self
     */
    public static function find($id)
    {
        $item = Yii::$app->authManager->getRole($id);
        if ($item !== null) {
            return new self($item);
        }
        
        return null;
    }
    
    /**
     * Save role to [[\yii\rbac\authManager]]
     *
     * @return boolean
     */
    public function save()
    {
        if ($this->validate()) {
            $manager = Yii::$app->authManager;
            if ($this->_item === null) {
                if ($this->type == Item::TYPE_ROLE) {
                    $this->_item = $manager->createRole($this->name);
                } else {
                    $this->_item = $manager->createPermission($this->name);
                }
                $isNew = true;
            } else {
                $isNew = false;
                $oldName = $this->_item->name;
            }
            $this->_item->name = $this->name;
            $this->_item->description = $this->description;
            $this->_item->ruleName = $this->ruleName;
            $this->_item->data = $this->data === null || $this->data === '' ? null : Json::decode($this->data);
            if ($isNew) {
                $manager->add($this->_item);
            } else {
                $manager->update($oldName, $this->_item);
            }
            Helper::invalidate();
            
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Adds an item as a child of another item.
     *
     * @param array $items
     *
     * @return int
     */
    public function addChildren($items)
    {
        $manager = Yii::$app->getAuthManager();
        $success = 0;
        if ($this->_item) {
            foreach ($items as $name) {
                $child = $manager->getPermission($name);
                if ($this->type == Item::TYPE_ROLE && $child === null) {
                    $child = $manager->getRole($name);
                }
                try {
                    $manager->addChild($this->_item, $child);
                    $success++;
                } catch (\Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }
        if ($success > 0) {
            Helper::invalidate();
        }
        
        return $success;
    }
    
    /**
     * Remove an item as a child of another item.
     *
     * @param array $items
     *
     * @return int
     */
    public function removeChildren($items)
    {
        $manager = Yii::$app->getAuthManager();
        $success = 0;
        if ($this->_item !== null) {
            foreach ($items as $name) {
                $child = $manager->getPermission($name);
                if ($this->type == Item::TYPE_ROLE && $child === null) {
                    $child = $manager->getRole($name);
                }
                try {
                    $manager->removeChild($this->_item, $child);
                    $success++;
                } catch (\Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }
        if ($success > 0) {
            Helper::invalidate();
        }
        
        return $success;
    }
    
    /**
     * 获取可用的权限
     *
     * @return array
     */
    public function getItems()
    {
        $manager = Yii::$app->getAuthManager();
        $res = [];
        foreach ($manager->getPermissions() as $name) {
            $route['name'] = $name->name;
            $route['description'] = $name->description;
            $route['ruleName'] = $name->ruleName;
            $route['data'] = $name->data;
            $route['type'] = $name->type;
            $res[] = $route;
            $manager = Yii::$app->getAuthManager();
            $available = [];
            if ($this->type == Item::TYPE_ROLE) {
                foreach (array_keys($manager->getRoles()) as $role) {
                    $available[$role] = 'role';
                }
            }
        }
        
        return $res;
    }
    
    /**
     * 分配列表
     *
     * @return array
     */
    public function getItemsList()
    {
        $manager = Yii::$app->getAuthManager();
        $available = [];
        if ($this->type == Item::TYPE_ROLE) {
            foreach ($manager->getRoles() as $name) {
                $available[$name->name]['type'] = 'role';
                $available[$name->name]['description'] = $name->description;
                $available[$name->name]['parent_name'] = $name->parent_name;
                $available[$name->name]['check'] = 0;
            }
        }
        foreach ($manager->getPermissions() as $name) {
            $available[$name->name]['type'] = $name->name[0] == '/' ? 'route' : 'permission';
            $available[$name->name]['description'] = $name->description;
            $available[$name->name]['parent_name'] = $name->parent_name;
            $available[$name->name]['check'] = 0;
        }
        foreach ($manager->getChildren($this->_item->name) as $item) {
            $available[$item->name]['check'] = 1;
        }
        unset($available[$this->name]);
        $i = $j = $k = 0;
        $role_list = $permission_list = $route_list = [];
        foreach ($available as $key => $val) {
            if ($val['type'] == 'role') {
                $role_list[$i]['name'] = $key;
                $role_list[$i]['check'] = $val['check'];
                $role_list[$i]['description'] = $val['description'];
                $role_list[$i]['parent_name'] = $val['parent_name'];
                $role_list[$i]['show'] = $key;
                $i++;
            }
            if ($val['type'] == 'permission') {
                $permission_list[$j]['name'] = $key;
                $permission_list[$j]['check'] = $val['check'];
                $permission_list[$j]['description'] = $val['description'];
                $permission_list[$j]['parent_name'] = $val['parent_name'];
                $permission_list[$j]['show'] = $key;
                $j++;
            }
            if ($val['type'] == 'route') {
                $route_list[$k]['name'] = $key;
                $route_list[$k]['check'] = $val['check'];
                $route_list[$k]['description'] = $val['description'];
                $route_list[$k]['parent_name'] = $val['parent_name'];
                $route_list[$k]['show'] = $val['description'];
                $k++;
            }
        }
        $role_list = ArrayHelper::index($role_list, null, 'parent_name');
        $permission_list = ArrayHelper::index($permission_list, null, 'parent_name');
        $route_list = ArrayHelper::index($route_list, null, 'parent_name');
        $result = ['角色' => $role_list, '权限' => $permission_list,'路由' => $route_list];
        if ($this->type == Item::TYPE_PERMISSION) {
            unset($result['角色']);
        }

        return $result;
    }
    
    /**
     * Get item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->_item;
    }
    
    /**
     * Get type name
     *
     * @param  mixed $type
     *
     * @return string|array
     */
    public static function getTypeName($type = null)
    {
        $result = [
            Item::TYPE_PERMISSION => 'Permission',
            Item::TYPE_ROLE => 'Role'
        ];
        if ($type === null) {
            return $result;
        }
        
        return $result[$type];
    }
    
    /**
     * 权限 | 角色 添加
     *
     * @param $type
     *
     * @return bool|\wind\rest\models\AuthItem
     */
    public function addPermission($type)
    {
        $model = clone $this;
        $model->type = $type;
        if ($model->load(Yii::$app->getRequest()->post(), '') && $model->save()) {
            return $type == 1 ? $model : true;
        } else {
            RbacHelper::recordLog($model->errors, 'create', 'addPermissionRole');
            
            return false;
        }
    }
    
    /**
     * 权限详情
     *
     * @param $id
     *
     * @return bool|\wind\rest\models\AuthItem
     */
    public function findModel($id, $type = 2)
    {
        $auth = Yii::$app->getAuthManager();
        $item = $type === Item::TYPE_ROLE ? $auth->getRole($id) : $auth->getPermission($id);
        if ($item) {
            return new AuthItem($item);
        } else {
            return false;
        }
    }
    
    /**
     * 修改
     *
     * @param $id
     * @param $type
     *
     * @return bool
     */
    public function updatePermission($id, $type)
    {
        $model = $this->findModel($id, $type);
        if ($model->load(Yii::$app->getRequest()->post(), '') && $model->save()) {
            return true;
        } else {
            RbacHelper::recordLog($model->errors, 'update', 'updatePermissionRole');
            
            return false;
        }
    }
    
    /**
     * 删除权限
     *
     * @param $id
     *
     * @return bool
     */
    public function deletePermission($id, $type)
    {
        $model = $this->findModel($id, $type);
        if ( !$model) {
            return false;
        }
        $res = Yii::$app->getAuthManager()->remove($model->_item);
        
        return $res;
    }
    
    /**
     * 给权限 添加/删除 路由（左边到右边）
     *
     * @param $id
     * @param $type_id
     * @param $type
     *
     * @return array
     */
    public function assignItem($id, $type_id, $type)
    {
        $items = Yii::$app->getRequest()->post();
        $model = $this->findModel($id, $type_id);
        if ($type == 'add') {
            $model->addChildren($items);
        }
        if ($type == 'remove') {
            $model->removeChildren($items);
        }
        
        return $model->getItems();
    }
}
