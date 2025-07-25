<?php

use yii\db\Migration;

/**
 * Class m231002_192620_diy_user
 */
class m231002_192620_diy_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $role = $auth->createRole('cloud_user');
        $role->description = 'Cloud User';
        $auth->add($role);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('cloud_user');
        $auth->remove($role);

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231002_192620_diy_user cannot be reverted.\n";

        return false;
    }
    */
}
