<?php

use yii\db\Migration;

/**
 * Class m221110_200027_glo_write_to_hue
 */
class m221110_200027_glo_write_to_hue extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("alter table hg_glo
    add write_to_hue integer null;
");

        $this->execute("update hg_glo set write_to_hue = 1");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221110_200027_glo_write_to_hue cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221110_200027_glo_write_to_hue cannot be reverted.\n";

        return false;
    }
    */
}
