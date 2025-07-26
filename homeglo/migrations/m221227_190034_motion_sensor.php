<?php

use yii\db\Migration;

/**
 * Class m221227_190034_motion_sensor
 */
class m221227_190034_motion_sensor extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            UPDATE hg_product_sensor SET type_name = 'hue_motion_sensor' where ID in (15,20,21);
            UPDATE hg_product_sensor SET action_map_type = 'hue_dimmer_switch_4' where ID in (10,11);
            UPDATE hg_product_sensor SET action_map_type = 'hue_motion_sensor' where ID in (15,20,21);
        ");
        //Hue motion scene id
        $this->execute("alter table hg_device_sensor
    add hue_motion_scene_id varchar(255) null;");

        //create migration for first smartOn motion map
        $str =
            <<<EOD
        INSERT INTO `hg_hub_action_map` (`id`, `created_at`, `updated_at`, `name`, `display_name`, `map_image_url`, `base_hg_hub_action_map_id`, `hg_product_sensor_map_type`, `hg_status_id`, `preserve_hue_buttons`, `metadata`) VALUES (17, 1672168561, 1672168579, 'motion_map_default', 'Motion Map', '', NULL, 'hue_motion_sensor', NULL, CAST('[]' AS JSON), NULL);
        INSERT INTO `hg_hub_action_template` (`id`, `created_at`, `updated_at`, `hg_hub_id`, `hg_version_id`, `hg_hub_action_map_id`, `hg_status_id`, `hg_product_sensor_type_name`, `name`, `display_name`, `platform`, `multi_room`, `metadata`) VALUES (25, 1672168562, 1672168562, NULL, 1, 17, 300, 'hue_motion_sensor', 'smartOn_motion', 'Smart On', 'hue', NULL, NULL);
        INSERT INTO `hg_hub_action_trigger` (`id`, `created_at`, `updated_at`, `name`, `display_name`, `source_name`, `event_name`, `event_data`, `last_triggered_at`, `last_checked_at`, `hg_hub_action_template_id`, `hg_hub_id`, `hue_id`, `hg_device_sensor_id`, `hg_glozone_start_time_block_id`, `hg_glozone_end_time_block_id`, `hg_status_id`, `rank`, `metadata`) VALUES (201, 1672168562, 1672168904, 'smartOn_short_press_top', 'Presence Turn On', 'sensor', 'presence', NULL, NULL, NULL, 25, NULL, NULL, NULL, NULL, NULL, 300, NULL, NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (77, 1672168562, 1672169377, 201, 300, 'rule_detect_presence', 'Rule Detect Presence', '/sensors/((hg_device_sensor.hue_id))/state/presence', 'eq', 'true', NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (78, 1672168562, 1672169101, 201, 300, 'rule_lastupdated', 'Rule Lastupdated', '/sensors/((hg_device_sensor.hue_id))/state/presence', 'dx', NULL, NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (79, 1672168562, 1672168562, 201, 300, 'rule_timeblock', 'Rule Time Block', '/config/localtime', 'in', '((hgGlozoneTimeBlockStartTime))/((hgGlozoneTimeBlockEndTime))', NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (80, 1672168562, 1672168562, 201, 300, 'rule_anyon', 'Rule Any lights on', '/groups/((hg_device_group[0].hue_id))/state/any_on', 'eq', 'false', NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (84, 1672168562, 1672169508, 201, 300, 'rule_presence_dark', 'Rule Presence Dark', '/sensors/((hg_device_sensor.hue_ambient_sensor_id))/state/dark', 'eq', 'true', NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (85, 1672169551, 1672169551, 201, NULL, 'sensor_status', 'Check Sensor Status', '/sensors/((hg_device_sensor.hue_sensor_variable_id))/state/status', 'lt', '1', NULL);
        INSERT INTO `hg_hub_action_item` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `entity`, `operation_name`, `operation_value_json`, `operate_hg_device_light_group_id`, `hg_glo_id`, `display_name`, `override_hue_x`, `override_hue_y`, `override_bri_absolute`, `override_bri_increment_percent`, `override_transition_duration_ms`, `override_transition_at_time`, `metadata`) VALUES (105, 1672168562, 1672169269, 201, '/sensors/((hg_device_sensor.hue_sensor_variable_id))/state', 'set_sensor_state', CAST('{\"status\":1}' AS JSON), NULL, NULL, 'Set Sensor State', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
        INSERT INTO `hg_hub_action_item` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `entity`, `operation_name`, `operation_value_json`, `operate_hg_device_light_group_id`, `hg_glo_id`, `display_name`, `override_hue_x`, `override_hue_y`, `override_bri_absolute`, `override_bri_increment_percent`, `override_transition_duration_ms`, `override_transition_at_time`, `metadata`) VALUES (108, 1672168562, 1672169193, 201, '/groups/((hg_device_group.hue_id))/action', 'turn_on_scene', NULL, NULL, NULL, 'Smart on Scene', NULL, NULL, NULL, NULL, 200, NULL, NULL);
        INSERT INTO `hg_hub_action_template` (`id`, `created_at`, `updated_at`, `hg_hub_id`, `hg_version_id`, `hg_hub_action_map_id`, `hg_status_id`, `hg_product_sensor_type_name`, `name`, `display_name`, `platform`, `multi_room`, `metadata`) VALUES (26, 1672168562, 1672168839, NULL, 1, 17, 300, 'hue_motion_sensor', 'smartOff_finalize', 'Smart Off (Finalize)', 'hue', NULL, NULL);
        INSERT INTO `hg_hub_action_trigger` (`id`, `created_at`, `updated_at`, `name`, `display_name`, `source_name`, `event_name`, `event_data`, `last_triggered_at`, `last_checked_at`, `hg_hub_action_template_id`, `hg_hub_id`, `hue_id`, `hg_device_sensor_id`, `hg_glozone_start_time_block_id`, `hg_glozone_end_time_block_id`, `hg_status_id`, `rank`, `metadata`) VALUES (202, 1672168562, 1672170466, 'smartOff_finalize', 'Smart Off Finalize', 'sensor', 'smart_off', NULL, NULL, NULL, 26, NULL, NULL, NULL, NULL, NULL, 300, NULL, NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (81, 1672168562, 1672170487, 202, 300, 'rule_detect_presence', 'Rule Detect Presence', '/sensors/((hg_device_sensor.hue_id))/state/presence', 'eq', 'false', NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (82, 1672168562, 1672173833, 202, 300, 'rule_last_changed', 'Rule Last Changed', '/sensors/((hg_device_sensor.hue_sensor_variable_id))/state/status', 'ddx', 'PT00:00:10', NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (83, 1672168562, 1672170564, 202, 300, 'rule_sensor_status', 'Rule Sensor Status', '/sensors/((hg_device_sensor.hue_sensor_variable_id))/state/status', 'gt', '1', NULL);
        INSERT INTO `hg_hub_action_item` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `entity`, `operation_name`, `operation_value_json`, `operate_hg_device_light_group_id`, `hg_glo_id`, `display_name`, `override_hue_x`, `override_hue_y`, `override_bri_absolute`, `override_bri_increment_percent`, `override_transition_duration_ms`, `override_transition_at_time`, `metadata`) VALUES (106, 1672168562, 1672168562, 202, '/groups/((hg_device_group.hue_id))/action', 'turn_off_room', NULL, NULL, NULL, 'Turn Off Room', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
        INSERT INTO `hg_hub_action_item` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `entity`, `operation_name`, `operation_value_json`, `operate_hg_device_light_group_id`, `hg_glo_id`, `display_name`, `override_hue_x`, `override_hue_y`, `override_bri_absolute`, `override_bri_increment_percent`, `override_transition_duration_ms`, `override_transition_at_time`, `metadata`) VALUES (107, 1672168562, 1672168562, 202, '/sensors/((hg_device_sensor.hue_sensor_variable_id))/state', 'set_sensor_state', CAST('{\"status\":0}' AS JSON), NULL, NULL, 'Set Sensor State', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
        INSERT INTO `hg_hub_action_template` (`id`, `created_at`, `updated_at`, `hg_hub_id`, `hg_version_id`, `hg_hub_action_map_id`, `hg_status_id`, `hg_product_sensor_type_name`, `name`, `display_name`, `platform`, `multi_room`, `metadata`) VALUES (27, 1672249706, 1672249941, NULL, 1, 17, 300, 'hue_motion_sensor', 'smart_dim_restore', 'Smart Dim (Restore)', 'hue', NULL, NULL);
        INSERT INTO `hg_hub_action_trigger` (`id`, `created_at`, `updated_at`, `name`, `display_name`, `source_name`, `event_name`, `event_data`, `last_triggered_at`, `last_checked_at`, `hg_hub_action_template_id`, `hg_hub_id`, `hue_id`, `hg_device_sensor_id`, `hg_glozone_start_time_block_id`, `hg_glozone_end_time_block_id`, `hg_status_id`, `rank`, `metadata`) VALUES (203, 1672171406, 1672171466, 'smart_dim_restore', 'Smart Dim Restore', 'sensor', 'smart_dim_restore', NULL, NULL, NULL, 27, NULL, NULL, NULL, NULL, NULL, 300, NULL, NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (87, 1672171406, 1672171476, 203, 300, 'rule_detect_presence', 'Rule Detect Presence', '/sensors/((hg_device_sensor.hue_id))/state/presence', 'eq', 'true', NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (88, 1672171406, 1672171498, 203, 300, 'rule_last_changed', 'Rule Last Changed', '/sensors/((hg_device_sensor.hue_id))/state/presence', 'dx', NULL, NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (89, 1672171406, 1672171406, 203, 300, 'rule_sensor_status', 'Rule Sensor Status', '/sensors/((hg_device_sensor.hue_sensor_variable_id))/state/status', 'gt', '1', NULL);
        INSERT INTO `hg_hub_action_item` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `entity`, `operation_name`, `operation_value_json`, `operate_hg_device_light_group_id`, `hg_glo_id`, `display_name`, `override_hue_x`, `override_hue_y`, `override_bri_absolute`, `override_bri_increment_percent`, `override_transition_duration_ms`, `override_transition_at_time`, `metadata`) VALUES (102, 1672171406, 1672251396, 203, '/groups/0/action', 'turn_on_temp_motion_scene', NULL, NULL, NULL, 'Adjust Brightness', NULL, NULL, NULL, NULL, 200, NULL, NULL);
        INSERT INTO `hg_hub_action_item` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `entity`, `operation_name`, `operation_value_json`, `operate_hg_device_light_group_id`, `hg_glo_id`, `display_name`, `override_hue_x`, `override_hue_y`, `override_bri_absolute`, `override_bri_increment_percent`, `override_transition_duration_ms`, `override_transition_at_time`, `metadata`) VALUES (110, 1672171406, 1672247349, 203, '/sensors/((hg_device_sensor.hue_sensor_variable_id))/state', 'set_sensor_state', CAST('{\"status\":1}' AS JSON), NULL, NULL, 'Set Sensor State', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
        INSERT INTO `hg_hub_action_template` (`id`, `created_at`, `updated_at`, `hg_hub_id`, `hg_version_id`, `hg_hub_action_map_id`, `hg_status_id`, `hg_product_sensor_type_name`, `name`, `display_name`, `platform`, `multi_room`, `metadata`) VALUES (28, 1672171630, 1672171644, NULL, 1, 17, 300, 'hue_motion_sensor', 'arm', 'Arm', 'hue', NULL, NULL);
        INSERT INTO `hg_hub_action_trigger` (`id`, `created_at`, `updated_at`, `name`, `display_name`, `source_name`, `event_name`, `event_data`, `last_triggered_at`, `last_checked_at`, `hg_hub_action_template_id`, `hg_hub_id`, `hue_id`, `hg_device_sensor_id`, `hg_glozone_start_time_block_id`, `hg_glozone_end_time_block_id`, `hg_status_id`, `rank`, `metadata`) VALUES (204, 1672171630, 1672171654, 'smart_motion_arm', 'Arm', 'sensor', 'arm', NULL, NULL, NULL, 28, NULL, NULL, NULL, NULL, NULL, 300, NULL, NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (90, 1672171630, 1672171862, 204, 300, 'rule_anyon', 'Rule Any On', '/groups/((hg_device_group[0].hue_id))/state/any_on', 'eq', 'false', NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (91, 1672171630, 1672171723, 204, 300, 'rule_detect_presence', 'Rule Detect Presence', '/sensors/((hg_device_sensor.hue_id))/state/presence', 'eq', 'false', NULL);
        INSERT INTO `hg_hub_action_item` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `entity`, `operation_name`, `operation_value_json`, `operate_hg_device_light_group_id`, `hg_glo_id`, `display_name`, `override_hue_x`, `override_hue_y`, `override_bri_absolute`, `override_bri_increment_percent`, `override_transition_duration_ms`, `override_transition_at_time`, `metadata`) VALUES (101, 1672171630, 1672171630, 204, '/sensors/((hg_device_sensor.hue_sensor_variable_id))/state', 'set_sensor_state', CAST('{\"status\":0}' AS JSON), NULL, NULL, 'Set Sensor State', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
        INSERT INTO `hg_hub_action_template` (`id`, `created_at`, `updated_at`, `hg_hub_id`, `hg_version_id`, `hg_hub_action_map_id`, `hg_status_id`, `hg_product_sensor_type_name`, `name`, `display_name`, `platform`, `multi_room`, `metadata`) VALUES (29, 1672249901, 1672249913, NULL, 1, 17, 300, 'hue_motion_sensor', 'smart_dim_halfway', 'Smart Dim (Halfway)', 'hue', NULL, NULL);
        INSERT INTO `hg_hub_action_trigger` (`id`, `created_at`, `updated_at`, `name`, `display_name`, `source_name`, `event_name`, `event_data`, `last_triggered_at`, `last_checked_at`, `hg_hub_action_template_id`, `hg_hub_id`, `hue_id`, `hg_device_sensor_id`, `hg_glozone_start_time_block_id`, `hg_glozone_end_time_block_id`, `hg_status_id`, `rank`, `metadata`) VALUES (200, 1672168562, 1672246516, 'short_press_dimmer', 'Presence Dim (Halfway)', 'sensor', 'half_time_passed', NULL, NULL, NULL, 29, NULL, NULL, NULL, NULL, NULL, 300, NULL, NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (75, 1672168562, 1672170121, 200, 300, 'rule_presence', 'Rule Presence Detected', '/sensors/((hg_device_sensor.hue_id))/state/presence', 'eq', 'false', NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (76, 1672168562, 1672170154, 200, 300, 'rule_presence_timeout', 'Rule Presence Time', '/sensors/((hg_device_sensor.hue_id))/state/presence', 'ddx', 'PT00:00:10', NULL);
        INSERT INTO `hg_hub_action_condition` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `hg_status_id`, `name`, `display_name`, `property`, `operator`, `value`, `metadata`) VALUES (86, 1672170191, 1672170191, 200, NULL, 'rule_check_sensor_state', 'Rule Check State', '/sensors/((hg_device_sensor.hue_sensor_variable_id))/state/status', 'eq', '1', NULL);
        INSERT INTO `hg_hub_action_item` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `entity`, `operation_name`, `operation_value_json`, `operate_hg_device_light_group_id`, `hg_glo_id`, `display_name`, `override_hue_x`, `override_hue_y`, `override_bri_absolute`, `override_bri_increment_percent`, `override_transition_duration_ms`, `override_transition_at_time`, `metadata`) VALUES (103, 1672170346, 1672251515, 200, '/scenes/((hg_device_sensor.hue_motion_scene_id))', 'storelightstate', NULL, NULL, NULL, 'Storelightstate', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
        INSERT INTO `hg_hub_action_item` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `entity`, `operation_name`, `operation_value_json`, `operate_hg_device_light_group_id`, `hg_glo_id`, `display_name`, `override_hue_x`, `override_hue_y`, `override_bri_absolute`, `override_bri_increment_percent`, `override_transition_duration_ms`, `override_transition_at_time`, `metadata`) VALUES (104, 1672170388, 1672170400, 200, '/sensors/((hg_device_sensor.hue_sensor_variable_id))/state', 'set_sensor_state', CAST('{\"status\":2}' AS JSON), NULL, NULL, 'Set Sensor State', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
        INSERT INTO `hg_hub_action_item` (`id`, `created_at`, `updated_at`, `hg_hub_action_trigger_id`, `entity`, `operation_name`, `operation_value_json`, `operate_hg_device_light_group_id`, `hg_glo_id`, `display_name`, `override_hue_x`, `override_hue_y`, `override_bri_absolute`, `override_bri_increment_percent`, `override_transition_duration_ms`, `override_transition_at_time`, `metadata`) VALUES (109, 1672168562, 1672247455, 200, '/groups/((hg_device_group.hue_id))/action', 'adjust_brightness', NULL, NULL, NULL, 'Dimmer', NULL, NULL, NULL, -50, NULL, NULL, NULL);
        EOD;

        $this->execute($str);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221227_190034_motion_sensor cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221227_190034_motion_sensor cannot be reverted.\n";

        return false;
    }
    */
}
