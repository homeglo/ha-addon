<?php

use yii\db\Migration;

/**
 * Class m221104_165040_light_serial
 */
class m221104_165040_light_serial extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("alter table hg_device_light
    add serial varchar(255) null;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221104_165040_light_serial cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221104_165040_light_serial cannot be reverted.\n";

        return false;
    }
    */
}
