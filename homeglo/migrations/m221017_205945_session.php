<?php

use yii\db\Migration;

/**
 * Class m221017_205945_session
 */
class m221017_205945_session extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE session
(
    id CHAR(40) NOT NULL PRIMARY KEY,
    expire INTEGER,
    data BLOB
);");

        $this->execute("alter table hg_hub_action_map
    add preserve_hue_buttons text null;
");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221017_205945_session cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221017_205945_session cannot be reverted.\n";

        return false;
    }
    */
}
