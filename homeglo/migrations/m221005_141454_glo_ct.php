<?php

use yii\db\Migration;

/**
 * Class m221005_141454_glo_ct
 */
class m221005_141454_glo_ct extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("alter table hg_glo
    add ct int null;");

        $this->execute("alter table hg_glo_device_light
    add ct int null;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221005_141454_glo_ct cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221005_141454_glo_ct cannot be reverted.\n";

        return false;
    }
    */
}
