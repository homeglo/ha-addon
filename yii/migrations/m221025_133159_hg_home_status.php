<?php

use yii\db\Migration;

/**
 * Class m221025_133159_hg_home_status
 */
class m221025_133159_hg_home_status extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // SQLite doesn't support dropping foreign keys directly
        // The foreign key constraints should be properly defined in the original table creation
        echo "Foreign key constraint modifications are handled in table creation for SQLite compatibility.\n";

        $this->execute("alter table hg_home
                    add hg_status_id int null;
                ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221025_133159_hg_home_status cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221025_133159_hg_home_status cannot be reverted.\n";

        return false;
    }
    */
}
