<?php

namespace wind\rest\models;

use yii\helpers\ArrayHelper;
use Yii;
use yii\base\Model;
use wind\rest\components\Helper;
use yii\rbac\Item;
use yii\data\ActiveDataProvider;

/**
 * Description of Assignment
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since  2.5
 */
class Assignment extends Model
{
    
    /**
     * @var integer User id
     */
    public $id;
    /**
     * @var \yii\web\IdentityInterface User
     */
    public $user;
    
    /**
     * @inheritdoc
     */
    public function __construct($id, $user = null, $config = array())
    {
        $this->id = $id;
        $this->user = $user;
        parent::__construct($config);
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('rbac-admin', 'ID'),
            'username' => Yii::t('rbac-admin', 'Username'),
            'name' => Yii::t('rbac-admin', 'Name'),
        ];
    }
    
    /**
     * Grands a roles from a user.
     *
     * @param array $items
     *
     * @return integer number of successful grand
     */
    public function assign($items)
    {
        $manager = Yii::$app->getAuthManager();
        foreach ($items as $name) {
            try {
                //先查分组 暂未解决重名问题
                if ($manager->getGroups($name)) {
                    $manager->assignGroup($manager->getGroups($name)[0]['group_id'], $this->id);
                } else {
                    $item = $manager->getRole($name);
                    $item = $item ?: $manager->getPermission($name);
                    $manager->assign($item, $this->id);
                }
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
                
                return false;
            }
        }
        Helper::invalidate();
        
        return true;
    }
    
    /**
     * Revokes a roles from a user.
     *
     * @param array $items
     *
     * @return integer number of successful revoke
     */
    public function revoke($items)
    {
        $manager = Yii::$app->getAuthManager();
        $success = 0;
        foreach ($items as $name) {
            try {
                if ($manager->getGroups($name)) {
                    $manager->revokeGroup($manager->getGroups($name)[0]['group_id'], $this->id);
                    $success++;
                } else {
                    $item = $manager->getRole($name);
                    $item = $item ?: $manager->getPermission($name);
                    $manager->revoke($item, $this->id);
                    $success++;
                }
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
        
        return $success;
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
        //角色
        foreach ($manager->getRoles() as $name) {
            $available[$name->name]['type'] = 'role';
            $available[$name->name]['description'] = $name->description;
            $available[$name->name]['check'] = 0;
        }
        //权限
        foreach ($manager->getPermissions() as $name) {
            $available[$name->name]['type'] = $name->name[0] == '/' ? 'route' : 'permission';
            $available[$name->name]['description'] = $name->description;
            $available[$name->name]['check'] = 0;
        }
        //分组
        $user_groups = array_column($manager->getGroupChild($this->id), 'group_id');
        $groups = $manager->getGroups();
        foreach ($groups as &$v) {
            $v['name'] = $v['group_name'];
            $v['check'] = 0;
            $v['description'] = $v['group_id'];
            if ($user_groups && in_array($v['group_id'], $user_groups)) {
                $v['check'] = 1;
            }
            unset($v['group_name']);
            unset($v['group_id']);
        }
        $group_list[""] = $groups;
        
        foreach ($manager->getAssignments($this->id) as $key => $item) {
            $available[$key]['check'] = 1;
        }
        unset($available[$this->name]);
        $i = $j = $k = 0;
        $role_list = $permission_list = [];
        foreach ($available as $key => $val) {
            if ($val['type'] == 'role') {
                $role_list[$i]['name'] = $key;
                $role_list[$i]['check'] = $val['check'];
                $role_list[$i]['description'] = $val['description'];
                $i++;
            }
            if ($val['type'] == 'permission') {
                $permission_list[$j]['name'] = $key;
                $permission_list[$j]['check'] = $val['check'];
                $permission_list[$j]['description'] = $val['description'];
                $j++;
            }
        }
        $role_list = ArrayHelper::index($role_list, null, 'parent_name');
        $permission_list = ArrayHelper::index($permission_list, null, 'parent_name');
        $result = ['角色' => $role_list, '权限' => $permission_list, '分组' => $group_list];
        
        return $result;
    }
    
    /**
     * Get all avaliable and assigned roles/permission
     *
     * @return array
     */
    public function getItems()
    {
        $manager = Yii::$app->getAuthManager();
        $avaliable = [];
        foreach (array_keys($manager->getRoles()) as $name) {
            $avaliable[$name] = 'role';
        }
        
        foreach (array_keys($manager->getPermissions()) as $name) {
            if ($name[0] != '/') {
                $avaliable[$name] = 'permission';
            }
        }
        
        $assigned = [];
        foreach ($manager->getAssignments($this->id) as $item) {
            $assigned[$item->roleName] = $avaliable[$item->roleName];
            unset($avaliable[$item->roleName]);
        }
        
        return [
            'avaliable' => $avaliable,
            'assigned' => $assigned
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if ($this->user) {
            return $this->user->$name;
        }
    }
    
    public function findModel($id, $class)
    {
        if (($user = $class::findIdentity($id)) !== null) {
            return new Assignment($id, $user);
        } else {
            return false;
        }
    }
    
    public function Lists($params, $usernameField)
    {
        $query = $this->find;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        if ( !($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $query->andFilterWhere(['like', $usernameField, $this->username]);
        
        return $dataProvider;
    }
    
    /**
     * 角色批量分配给用户
     *
     * @param $users
     *
     * @return int
     */
    public function assignBatch($users)
    {
        $manager = Yii::$app->getAuthManager();
        $success = 0;
        foreach ($users as $user_id) {
            try {
                $item = $manager->getRole($this->id);
                $manager->assign($item, $user_id);
                $success++;
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
        
        return $success;
    }
    
    /**
     * 可选用户|已分配用户
     *
     * @return array
     */
    public function assignUser()
    {
        $manager = Yii::$app->getAuthManager();
        $assign = $manager->getAssignmentsUsers($this->id);
        $user_id_arr = array_filter(array_column($assign, 'id'));
        $user_model = new \wind\rest\models\searchs\User();
        $all = $user_model->allUsers(['id', 'realname']);
        foreach ($all as $key => $val) {
            if (in_array($val['id'], $user_id_arr)) {
                unset($all[$key]);
            }
        }
        sort($all);
        
        return compact(['all', 'assign']);
    }
    
    /**
     * 角色批量删除用户
     *
     * @param $users
     *
     * @return int
     */
    public function assignRemoveUsers($users)
    {
        $manager = Yii::$app->getAuthManager();
        $success = 0;
        foreach ($users as $user_id) {
            try {
                $item = $manager->getRole($this->id);
                $manager->revoke($item, $user_id);
                $success++;
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
        
        return $success;
    }
}
