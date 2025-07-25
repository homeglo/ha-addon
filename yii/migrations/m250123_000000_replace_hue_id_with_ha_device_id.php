<?php

use yii\db\Migration;

/**
 * Class m250123_000000_replace_hue_id_with_ha_device_id
 * Refactor to replace hue_id with ha_device_id across all tables
 */
class m250123_000000_replace_hue_id_with_ha_device_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add new ha_device_id columns
        $this->addColumn('hg_device_group', 'ha_device_id', $this->string(255)->null());
        $this->addColumn('hg_device_light', 'ha_device_id', $this->string(255)->null());
        $this->addColumn('hg_device_sensor', 'ha_device_id', $this->string(255)->null());
        $this->addColumn('hg_hub_action_trigger', 'ha_device_id', $this->string(255)->null());

        // Copy data from hue_id to ha_device_id (convert int to string)
        $this->execute("UPDATE hg_device_group SET ha_device_id = CAST(hue_id AS VARCHAR(255)) WHERE hue_id IS NOT NULL");
        $this->execute("UPDATE hg_device_light SET ha_device_id = CAST(hue_id AS VARCHAR(255)) WHERE hue_id IS NOT NULL");
        $this->execute("UPDATE hg_device_sensor SET ha_device_id = CAST(hue_id AS VARCHAR(255)) WHERE hue_id IS NOT NULL");
        $this->execute("UPDATE hg_hub_action_trigger SET ha_device_id = CAST(hue_id AS VARCHAR(255)) WHERE hue_id IS NOT NULL");

        // TODO: Manual review required - Verify data migration completed successfully
        // TODO: Update application code to use ha_device_id before dropping hue_id columns
        
        // For now, keep both columns during transition period
        // Drop hue_id columns in a future migration after code is updated
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remove ha_device_id columns
        $this->dropColumn('hg_device_group', 'ha_device_id');
        $this->dropColumn('hg_device_light', 'ha_device_id');
        $this->dropColumn('hg_device_sensor', 'ha_device_id');
        $this->dropColumn('hg_hub_action_trigger', 'ha_device_id');
    }
}