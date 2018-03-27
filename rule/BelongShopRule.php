<?php
/**
 * 规则--用户所属店铺
 */

namespace  wind\rest\rule;

use yii\rbac\Rule;
use yii;

class BelongShopRule extends Rule
{
    
    /**
     * 用户是否属于当前请求的数据的店铺
     *
     * @param string|integer $user   用户 ID.
     * @param Item           $item   该规则相关的角色或者权限
     * @param array          $params 传给 ManagerInterface::checkAccess() 的参数
     *
     * @return boolean 代表该规则相关的角色或者权限是否被允许
     */
    public function execute($user, $item, $params)
    {
        //当前店铺id
        $shop_id = Yii::$app->request->get('shop_id');
        if (empty($shop_id)) {
            return true;
        }
        $shop_ids = Yii::$app->user->identity->groups;
        if ( !strstr($shop_ids, $shop_id)) {
            return false;
        }
        
        return true;
    }
}