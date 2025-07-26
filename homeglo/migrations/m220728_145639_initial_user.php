<?php

use yii\db\Migration;

/**
 * Class m220728_145639_initial_user
 */
class m220728_145639_initial_user extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // create a role named "administrator"
        $administratorRole = $auth->createRole('admin');
        $administratorRole->description = 'Administrator';
        $auth->add($administratorRole);

        // create permission for certain tasks
        $permission = $auth->createPermission('user-management');
        $permission->description = 'User Management';
        $auth->add($permission);

        // let administrators do user management
        $auth->addChild($administratorRole, $auth->getPermission('user-management'));

        // create user "admin" with password "homeglo"
        $user = new \app\models\HgUser();
        $user->email = "admin@home-glo.com";
        $user->username = "admin";
        $user->setPassword("homeglo");
        $user->generateAuthKey();
        $user->save();

        // assign role to our admin-user
        $auth->assign($administratorRole, $user->id);
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        // delete permission
        $auth->remove($auth->getPermission('user-management'));
    }
}
