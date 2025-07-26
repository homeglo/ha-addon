<?php

use yii\db\Migration;

/**
 * Class m250123_000001_drop_hue_id_columns
 * Final migration to drop hue_id columns after ha_device_id transition is complete
 * 
 * IMPORTANT: Only run this migration AFTER confirming all application code 
 * has been updated to use ha_device_id instead of hue_id
 */
class m250123_000001_drop_hue_id_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // TODO: Manual verification required before running this migration
        // 1. Verify all application code uses ha_device_id instead of hue_id
        // 2. Verify all data has been migrated from hue_id to ha_device_id
        // 3. Run comprehensive tests to ensure functionality works with ha_device_id
        
        // SQLite doesn't support DROP COLUMN, so we need to recreate tables
        $this->recreateTableWithoutHueColumns();
    }

    private function recreateTableWithoutHueColumns()
    {
        // For hg_device_group
        $this->createTable('hg_device_group_new', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'hg_hub_id' => $this->integer()->null(),
            'hg_device_group_type_id' => $this->integer()->null(),
            'hg_glozone_id' => $this->integer()->null(),
            'room_invoke_order' => $this->integer()->null(),
            'is_room' => $this->integer()->null(),
            'ha_device_id' => $this->string(255)->null(),
            'display_name' => $this->string(255)->null(),
            'metadata' => $this->text()->null(),
        ]);
        
        $this->execute("INSERT INTO hg_device_group_new (id, created_at, updated_at, hg_hub_id, hg_device_group_type_id, hg_glozone_id, room_invoke_order, is_room, ha_device_id, display_name, metadata) 
                       SELECT id, created_at, updated_at, hg_hub_id, hg_device_group_type_id, hg_glozone_id, room_invoke_order, is_room, ha_device_id, display_name, metadata 
                       FROM hg_device_group");
        
        $this->dropTable('hg_device_group');
        $this->renameTable('hg_device_group_new', 'hg_device_group');

        // For hg_device_light  
        $this->createTable('hg_device_light_new', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'hg_hub_id' => $this->integer()->null(),
            'ha_device_id' => $this->string(255)->null(),
            'display_name' => $this->string(255)->null(),
            'primary_hg_device_group_id' => $this->integer()->null(),
            'hg_product_light_id' => $this->integer()->null(),
            'hg_device_light_fixture' => $this->integer()->null(),
            'metadata' => $this->text()->null(),
            'serial' => $this->string(255)->null(),
        ]);
        
        $this->execute("INSERT INTO hg_device_light_new (id, created_at, updated_at, hg_hub_id, ha_device_id, display_name, primary_hg_device_group_id, hg_product_light_id, hg_device_light_fixture, metadata, serial) 
                       SELECT id, created_at, updated_at, hg_hub_id, ha_device_id, display_name, primary_hg_device_group_id, hg_product_light_id, hg_device_light_fixture, metadata, serial 
                       FROM hg_device_light");
        
        $this->dropTable('hg_device_light');
        $this->renameTable('hg_device_light_new', 'hg_device_light');

        // For hg_device_sensor
        $this->createTable('hg_device_sensor_new', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'hg_hub_id' => $this->integer()->null(),
            'ha_device_id' => $this->string(255)->null(),
            'display_name' => $this->string(255)->null(),
            'hg_device_group_id' => $this->integer()->null(),
            'hg_product_sensor_id' => $this->integer()->null(),
            'hg_device_sensor_placement_id' => $this->integer()->null(),
            'switch_dimmer_increment_percent' => $this->integer()->null(),
            'metadata' => $this->text()->null(),
            'hg_hub_action_map_id' => $this->integer()->null(),
            'hg_glozone_id' => $this->integer()->null(),
        ]);
        
        $this->execute("INSERT INTO hg_device_sensor_new (id, created_at, updated_at, hg_hub_id, ha_device_id, display_name, hg_device_group_id, hg_product_sensor_id, hg_device_sensor_placement_id, switch_dimmer_increment_percent, metadata, hg_hub_action_map_id, hg_glozone_id) 
                       SELECT id, created_at, updated_at, hg_hub_id, ha_device_id, display_name, hg_device_group_id, hg_product_sensor_id, hg_device_sensor_placement_id, switch_dimmer_increment_percent, metadata, hg_hub_action_map_id, hg_glozone_id 
                       FROM hg_device_sensor");
        
        $this->dropTable('hg_device_sensor');
        $this->renameTable('hg_device_sensor_new', 'hg_device_sensor');

        // For hg_hub_action_trigger
        $this->createTable('hg_hub_action_trigger_new', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'name' => $this->string(255)->null(),
            'display_name' => $this->string(255)->null(),
            'source_name' => $this->string(255)->null(),
            'event_name' => $this->string(255)->null(),
            'event_data' => $this->text()->null(),
            'last_triggered_at' => $this->integer()->null(),
            'last_checked_at' => $this->integer()->null(),
            'hg_hub_action_template_id' => $this->integer()->null(),
            'hg_hub_id' => $this->integer()->null(),
            'ha_device_id' => $this->string(255)->null(),
            'hg_device_sensor_id' => $this->integer()->null(),
            'hg_glozone_start_time_block_id' => $this->integer()->null(),
            'hg_glozone_end_time_block_id' => $this->integer()->null(),
            'hg_status_id' => $this->integer()->null(),
            'rank' => $this->integer()->null(),
            'metadata' => $this->text()->null(),
        ]);
        
        $this->execute("INSERT INTO hg_hub_action_trigger_new (id, created_at, updated_at, name, display_name, source_name, event_name, event_data, last_triggered_at, last_checked_at, hg_hub_action_template_id, hg_hub_id, ha_device_id, hg_device_sensor_id, hg_glozone_start_time_block_id, hg_glozone_end_time_block_id, hg_status_id, rank, metadata) 
                       SELECT id, created_at, updated_at, name, display_name, source_name, event_name, event_data, last_triggered_at, last_checked_at, hg_hub_action_template_id, hg_hub_id, ha_device_id, hg_device_sensor_id, hg_glozone_start_time_block_id, hg_glozone_end_time_block_id, hg_status_id, rank, metadata 
                       FROM hg_hub_action_trigger");
        
        $this->dropTable('hg_hub_action_trigger');
        $this->renameTable('hg_hub_action_trigger_new', 'hg_hub_action_trigger');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // SQLite doesn't support ADD COLUMN with constraints easily, so recreate tables with hue_id columns
        echo "Rolling back requires manual database restoration from backup.\n";
        echo "This migration cannot be automatically reversed due to SQLite limitations.\n";
        return false;
    }
}