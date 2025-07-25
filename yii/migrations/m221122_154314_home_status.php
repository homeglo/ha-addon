<?php

use yii\db\Migration;

/**
 * Class m221122_154314_home_status
 */
class m221122_154314_home_status extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
                        INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (200, 'active', 'Active', 'home', null);
                        INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (210, 'inactive', 'Inactive', 'home', null);
                        ");

        $this->execute("UPDATE hg_home SET hg_status_id = 200");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221122_154314_home_status cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221122_154314_home_status cannot be reverted.\n";

        return false;
    }
    */
}
