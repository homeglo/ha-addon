<?php

use yii\db\Migration;

/**
 * Class m230221_144721_db_cache
 */
class m230221_144721_db_cache extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE cache (
                        id char(128) NOT NULL PRIMARY KEY,
                        expire integer,
                        data BLOB
                    );");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('cache');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230221_144721_db_cache cannot be reverted.\n";

        return false;
    }
    */
}
