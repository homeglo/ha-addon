<?php

use yii\db\Migration;

/**
 * Class m230120_145901_motion_scene_group
 */
class m230120_145901_motion_scene_group extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {


        $this->execute("alter table hg_hub_action_map
    add hg_home_id int null;

");

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('hg_hub_action_map_hg_home_null_fk','hg_hub_action_map');
        $this->dropColumn('hg_hub_action_map','hg_home_id');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230120_145901_motion_scene_group cannot be reverted.\n";

        return false;
    }
    */
}
