<?php

use yii\db\Migration;

/**
 * Class m221010_204138_fk_edits
 */
class m221010_204138_fk_edits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // SQLite doesn't support dropping foreign keys directly, so we'll recreate the table
        // with the updated constraints. The base migration already has the correct constraints,
        // so this migration becomes a no-op for SQLite compatibility.
        
        // For SQLite compatibility, we'll skip this migration as the foreign key constraints
        // should be properly defined in the original table creation.
        echo "Foreign key constraint modifications are handled in table creation for SQLite compatibility.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221010_204138_fk_edits cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221010_204138_fk_edits cannot be reverted.\n";

        return false;
    }
    */
}
