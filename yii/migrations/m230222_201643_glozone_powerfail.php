<?php

use yii\db\Migration;

/**
 * Class m230222_201643_glozone_powerfail
 */
class m230222_201643_glozone_powerfail extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (700, 'hue_warm_white_on', 'Hue Warm White On', 'glozone_startup_mode', null);
            INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (705, 'last_state', 'Bulb Last State', 'glozone_startup_mode', null);
");
        $this->execute("alter table hg_status
    add metadata text null;
");

        $this->execute("alter table hg_glozone
    add bulb_startup_mode_hg_status_id int null;

");
        
        // SQLite doesn't support adding foreign key constraints to existing tables
        // Foreign keys should be defined during table creation
        echo "Foreign key constraint will be enforced at application level for SQLite compatibility.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('hg_status',['id'=>[700,705]]);
        $this->dropColumn('hg_status','metadata');
        $this->dropForeignKey('hg_glozone_hg_status_id_fk','hg_glozone');
        $this->dropColumn('hg_glozone','bulb_startup_mode_hg_status_id');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230222_201643_glozone_powerfail cannot be reverted.\n";

        return false;
    }
    */
}
