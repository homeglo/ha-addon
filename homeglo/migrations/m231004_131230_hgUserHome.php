<?php

use yii\db\Migration;

/**
 * Class m231004_131230_hgUserHome
 */
class m231004_131230_hgUserHome extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('hg_status',[
            'id'=>800,
            'name'=>'active',
            'display_name'=>'Active',
            'category_name'=>'hg_user_home'
        ]);

        $this->insert('hg_status',[
            'id'=>810,
            'name'=>'inactive',
            'display_name'=>'Inactive',
            'category_name'=>'hg_user_home'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('hg_status',['id'=>[800,810]]);

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231004_131230_hgUserHome cannot be reverted.\n";

        return false;
    }
    */
}
