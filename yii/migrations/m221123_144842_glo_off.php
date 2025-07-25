<?php

use yii\db\Migration;

/**
 * Class m221123_144842_glo_off
 */
class m221123_144842_glo_off extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            INSERT INTO hg_glo (id, created_at, updated_at, base_hg_glo_id, name, hub_name, display_name, hg_status_id, hg_glozone_id, hg_hub_id, hg_version_id, write_to_hue, hue_ids, `rank`, ct, hue_x, hue_y, brightness, metadata) VALUES (5, NULL, NULL, null, 'hg_off', 'Off', 'Off', 100, 1, null, 1, 0, null, null, 0, 0.0000, 0.0000, 0, null);");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221123_144842_glo_off cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221123_144842_glo_off cannot be reverted.\n";

        return false;
    }
    */
}
