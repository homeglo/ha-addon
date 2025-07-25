<?php

use yii\db\Migration;

/**
 * Class m230117_214955_execute_tries
 */
class m230117_214955_execute_tries extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("alter table hg_glozone_smart_transition_execute
    add attempt int null;");

        $this->insert('hg_status',[
            'id'=>555,
            'name'=>'retry',
            'display_name'=>'Retry',
            'category_name'=>'smart_transition_execute'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('hg_glozone_smart_transition_execute','attempt');
        $this->delete('hg_status',['id'=>555]);

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230117_214955_execute_tries cannot be reverted.\n";

        return false;
    }
    */
}
