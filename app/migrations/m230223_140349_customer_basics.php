<?php

use yii\db\Migration;

/**
 * Class m230223_140349_customer_basics
 */
class m230223_140349_customer_basics extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("create table hg_user_home
(
    id           integer primary key autoincrement,
    created_at   int          null,
    updated_at   int          null,
    user_id      int          null,
    hg_home_id   int null,
    hg_status_id int null,
    metadata     text         null,
    constraint hg_user_home_hg_home_null_fk
        foreign key (hg_home_id) references hg_home (id)
            on update cascade on delete cascade,
    constraint hg_user_home_user_null_fk
        foreign key (user_id) references user (id)
            on update cascade on delete cascade
);

");


        $auth = Yii::$app->authManager;
        $role = $auth->createRole('customer');
        $role->description = 'Customer';
        $auth->add($role);


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('hg_user_home');

        $auth = Yii::$app->authManager;
        $role = $auth->getRole('customer');
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
        echo "m230223_140349_customer_basics cannot be reverted.\n";

        return false;
    }
    */
}
