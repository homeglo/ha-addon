<?php

use yii\db\Migration;

/**
 * Class m230117_151339_uniqueid
 */
class m230117_151339_uniqueid extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("alter table hg_device_sensor
    add hue_uniqueid varchar(255) null;
");

        $this->execute("alter table hg_device_light
    add hue_uniqueid varchar(255) null;
");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('hg_device_sensor','hue_uniqueid');
        $this->dropColumn('hg_device_light','hue_uniqueid');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230117_151339_uniqueid cannot be reverted.\n";

        return false;
    }
    */
}
