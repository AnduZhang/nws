<?php
namespace app\rbac;

use Yii;
use yii\rbac\Rule;

class UserGroupRule extends Rule
{
    public $name = 'userGroup';

    public function execute($user, $item, $params)
    {

        if (!\Yii::$app->user->isGuest) {
            //check the role from table user
            if(isset(\Yii::$app->user->identity->group)) {
                $group = \Yii::$app->user->identity->group;
            } else {
                return false;
            }

            return $group == $item->name;
        }
        return true;
    }
}