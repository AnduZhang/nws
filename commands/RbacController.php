<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use \app\rbac\UserGroupRule;

class RbacController extends Controller
{
    public function actionInit()
    {
        $authManager = \Yii::$app->authManager;

        // Create roles
        $guest  = $authManager->createRole('guest');
        $guest->description = 'Guest';
        $registered  = $authManager->createRole('registered');


        // Create simple, based on action{$NAME} permissions

//        $security = $authManager->createPermission('security');
        $index = $authManager->createPermission('index');
        $about = $authManager->createPermission('about');
        $login = $authManager->createPermission('login');
        $logout = $authManager->createPermission('logout');
        $confirm = $authManager->createPermission('confirm');

        // Add permissions in Yii::$app->authManager

        $authManager->add($login);
        $authManager->add($logout);
        $authManager->add($index);
        $authManager->add($confirm);
        $authManager->add($about);



        // Add rule, based on UserExt->group === $user->group
        $userGroupRule = new UserGroupRule();
        $authManager->add($userGroupRule);

        // Add rule "UserGroupRule" in roles
        $guest->ruleName  = $userGroupRule->name;

        $registered->ruleName = $userGroupRule->name;


        // Add roles in Yii::$app->authManager
        $authManager->add($guest);
        $authManager->add($registered);

        // Add permission-per-role in Yii::$app->authManager
        // Guest
        $authManager->addChild($guest, $login);
        $authManager->addChild($guest, $index);
        $authManager->addChild($guest, $confirm);



        // Registered
        $authManager->addChild($registered, $logout);
        $authManager->addChild($registered, $about);
        $authManager->addChild($registered, $guest);


    }
}