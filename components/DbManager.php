<?php

namespace wind\rest\components;

use yii\db\Expression;
use yii\db\Query;
use yii\rbac\Item;
use yii\rbac\Permission;
use yii\rbac\Role;

/**
 * DbManager represents an authorization manager that stores authorization information in database.
 *
 * The database connection is specified by [[$db]]. The database schema could be initialized by applying migration:
 *
 * ```
 * yii migrate --migrationPath=@yii/rbac/migrations/
 * ```
 *
 * If you don't want to use migration and need SQL instead, files for all databases are in migrations directory.
 *
 * You may change the names of the three tables used to store the authorization data by setting
 * [[\yii\rbac\DbManager::$itemTable]],
 * [[\yii\rbac\DbManager::$itemChildTable]] and [[\yii\rbac\DbManager::$assignmentTable]].
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since  1.0
 */
class DbManager extends \yii\rbac\DbManager
{
    
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * After the DbManager object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db = 'db';
    /**
     * @var string the name of the table storing authorization items. Defaults to "auth_item".
     */
    public $itemTable = 'auth_item';
    /**
     * @var string the name of the table storing authorization item hierarchy. Defaults to "auth_item_child".
     */
    public $itemChildTable = 'auth_item_child';
    /**
     * @var string the name of the table storing authorization item assignments. Defaults to "auth_assignment".
     */
    public $assignmentTable = 'auth_assignment';
    /**
     * @var string the name of the table storing rules. Defaults to "auth_rule".
     */
    public $ruleTable = 'auth_rule';
    /**
     * @var string the name of the table storing groups. Defaults to "auth_groups".
     */
    public $groupTable = 'auth_groups';
    /**
     * @var string the name of the table storing groups. Defaults to "auth_groups_child".
     */
    public $groupChildTable = 'auth_groups_child';
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * After the DbManager object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
//    public $db = 'db';
    /**
     * Memory cache of assignments
     *
     * @var array
     */
    private $_assignments = [];
    private $_childrenList;
    
    /**
     * @inheritdoc
     */
    public function getAssignments($userId)
    {
        if ( !isset($this->_assignments[$userId])) {
            $this->_assignments[$userId] = parent::getAssignments($userId);
        }
        
        return $this->_assignments[$userId];
    }
    
    /**
     * @inheritdoc
     */
    protected function getChildrenList()
    {
        if ($this->_childrenList === null) {
            $this->_childrenList = parent::getChildrenList();
        }
        
        return $this->_childrenList;
    }
    
    /**
     * Populates an auth item with the data fetched from database
     *
     * @param array $row the data from the auth item table
     *
     * @return Item the populated auth item instance (either Role or Permission)
     */
    protected function populateItem($row)
    {
        $class = $row['type'] == Item::TYPE_PERMISSION ? \wind\rest\models\Permission::className() : \wind\rest\models\Role::className();
        
        if ( !isset($row['data']) || ($data = @unserialize($row['data'])) === false) {
            $data = null;
        }
        
        return new $class([
            'name' => $row['name'],
            'type' => $row['type'],
            'description' => $row['description'],
            'parent_name' => $row['parent_name'],
            'ruleName' => $row['rule_name'],
            'data' => $data,
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ]);
    }
    
    /**
     * @inheritdoc
     */
    public function getChildren($name)
    {
        $query = (new Query)
            ->select(['name', 'type', 'description', 'rule_name', 'parent_name', 'data', 'created_at', 'updated_at'])
            ->from([$this->itemTable, $this->itemChildTable])
            ->where(['parent' => $name, 'name' => new Expression('[[child]]')]);
        
        $children = [];
        foreach ($query->all($this->db) as $row) {
            $children[$row['name']] = $this->populateItem($row);
        }
        
        return $children;
    }
    
    /**
     * @inheritdoc
     */
    protected function addItem($item)
    {
        $time = time();
        if ($item->createdAt === null) {
            $item->createdAt = $time;
        }
        if ($item->updatedAt === null) {
            $item->updatedAt = $time;
        }
        $this->db->createCommand()
            ->insert($this->itemTable, [
                'name' => $item->name,
                'type' => $item->type,
                'description' => $item->description,
                'parent_name' => $item->parent_name ?? '',
                'rule_name' => $item->ruleName,
                'data' => $item->data === null ? null : serialize($item->data),
                'created_at' => $item->createdAt,
                'updated_at' => $item->updatedAt,
            ])->execute();
        
        $this->invalidateCache();
        
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function createPermission($name)
    {
        $permission = new \wind\rest\models\Permission();
        $permission->name = $name;
        
        return $permission;
    }
    
    /**
     * 路由父级名称
     *
     * @return array
     */
    public function getRouteParent()
    {
        $query = (new Query)
            ->select('parent_name')
            ->from([$this->itemTable])
            ->distinct()
            ->where('parent_name is not null');
        $result = $query->all($this->db);
        
        return $result;
    }
    
    
    public function getAssignmentsUsers($item)
    {
        $query = (new Query)
            ->select(['id', 'realname'])
            ->from([$this->assignmentTable])
            ->leftJoin(Configs::instance()->userTable, $this->assignmentTable . '.user_id=id')
            ->andWhere(['status' => 10])
            ->andWhere(['item_name' => $item]);
        $result = $query->all($this->db);
        
        return $result;
    }
    
    /**
     * 分组列表
     *
     * @return array
     */
    public function getGroups($group_name = null)
    {
        $query = (new Query)
            ->select(['group_id', 'group_name'])
            ->from([$this->groupTable])
            ->andFilterWhere(['group_name' => $group_name])
            ->andWhere(['group_status' => 0]);
        $result = $query->all($this->db);
        
        return $result;
    }
    
    /**
     * 用户所在组
     *
     * @param $user_id
     *
     * @return array
     */
    public function getGroupChild($user_id)
    {
        $query = (new Query)
            ->select(['group_id'])
            ->from([$this->groupChildTable])
            ->andWhere(['user_id' => $user_id]);
        $result = $query->all($this->db);
        
        return $result;
    }
    
    /**
     * 添加分组下用户
     *
     * @param $data
     *
     * @return bool
     */
    public function assignGroup($group_id, $user_id)
    {
        $this->db->createCommand()
            ->insert($this->groupChildTable, [
                'group_id' => $group_id,
                'user_id' => $user_id
            ])->execute();
        
        return true;
    }
    
    /**
     * 添加分组下用户
     *
     * @param $data
     *
     * @return bool
     */
    public function revokeGroup($group_id, $user_id)
    {
        $this->db->createCommand()
            ->delete($this->groupChildTable, [
                'group_id' => $group_id,
                'user_id' => $user_id
            ])->execute();
        
        return true;
    }
    
    /**
     * @inheritdoc
     */
    protected function getItems($type)
    {
        $query = (new Query)
            ->from($this->itemTable)
            ->orderBy(['updated_at'=>SORT_DESC])
            ->where(['type' => $type]);
        
        $items = [];
        foreach ($query->all($this->db) as $row) {
            $items[$row['name']] = $this->populateItem($row);
        }
        
        return $items;
    }
}
