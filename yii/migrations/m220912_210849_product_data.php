<?php

use yii\db\Migration;

/**
 * Class m220912_210849_product_data
 */
class m220912_210849_product_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("INSERT INTO hg_version (id, version, display_name) VALUES (1, '1.0', 'Beta');
INSERT INTO hg_version (id, version, display_name) VALUES (2, '1.1', 'Beta-1.1');");

        $this->execute("INSERT INTO hg_home (id, updated_at, created_at, name, display_name, hg_version_id) VALUES (1, null, null, 'default1.0', 'Default1.0', 1);
");
        $this->execute("INSERT INTO hg_glozone (id, created_at, updated_at, hg_home_id, name, display_name, bed_time_weekday_midnightmins, wake_time_weekday_midnightmins, bed_time_weekend_midnightmins, wake_time_weekend_midnightmins, metadata) VALUES (1, null, null, 1, null, 'Default Glozone', '1380', '360', '360', '1380', null);

");
        $this->execute("INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (100, 'active', 'Active', 'glo', null);
INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (110, 'inactive', 'Inactive', 'glo', null);
INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (300, 'homeglo_template', 'HomeGlo Template', 'action_template', null);
INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (305, 'homeglo_template', 'HomeGlo Template Inactive', 'action_template', null);
INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (310, 'client_template', 'Client Template', 'action_template', null);
INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (400, 'active_template', 'Active Time Block Template', 'time_block', null);
INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (410, 'inactive_template', 'Inactive Time Block Template', 'time_block', null);
INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (420, 'active', 'Active Time Block', 'time_block', null);
INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (430, 'inactive', 'Inactive Time Block', 'time_block', null);
INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (500, 'smartTransition_active', 'Smart Transition Active', 'smart_transition', null);
INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (510, 'smartTransition_inactive', 'Smart Transition Inactive', 'smart_transition', null);


");

        $this->execute("
      INSERT INTO hg_product_light (id, display_name, manufacturer_name, productid, product_name, archetype, model_id, maxlumen, description, `rank`, version, price, `range`, capability_json) VALUES (1, 'Hue Sultan Bulb 800', 'hue', 'Philips-LCA003-1-A19ECLv6', 'Hue color lamp', 'sultanbulb', 'LCA003', 800, null, null, null, null, null, null);
INSERT INTO hg_product_light (id, display_name, manufacturer_name, productid, product_name, archetype, model_id, maxlumen, description, `rank`, version, price, `range`, capability_json) VALUES (2, 'Hue Lightstrip Plus v4', 'hue', 'Philips-LCL001-1-LedStripsv4', 'Hue lightstrip plus', 'huelightstrip', 'LCL001', 1600, null, null, null, null, null, null);
INSERT INTO hg_product_light (id, display_name, manufacturer_name, productid, product_name, archetype, model_id, maxlumen, description, `rank`, version, price, `range`, capability_json) VALUES (3, 'Hue Lightstrip Plus v3', 'hue', 'Philips-LST002-1-LedStripsv3', 'Hue lightstrip plus', 'huelightstrip', 'LST002', 1600, null, null, null, null, null, null);
INSERT INTO hg_product_light (id, display_name, manufacturer_name, productid, product_name, archetype, model_id, maxlumen, description, `rank`, version, price, `range`, capability_json) VALUES (4, 'Hue Sultan Bulb 1100', 'hue', 'Philips-LCA007-1-A19HECLv1', 'Hue color lamp', 'sultanbulb', 'LCA007', 1100, null, null, null, null, null, null);
INSERT INTO hg_product_light (id, display_name, manufacturer_name, productid, product_name, archetype, model_id, maxlumen, description, `rank`, version, price, `range`, capability_json) VALUES (5, 'Hue Sultan Bulb 1680', 'hue', 'Philips-LCA009-1-A21ECLv1', 'Hue color lamp', 'sultanbulb', 'LCA009', 1680, null, null, null, null, null, null);
INSERT INTO hg_product_light (id, display_name, manufacturer_name, productid, product_name, archetype, model_id, maxlumen, description, `rank`, version, price, `range`, capability_json) VALUES (6, 'Hue Signe Gradient Lamp', 'hue', '4422-9482-0441_HG01_PSU13', 'Signe gradient table', 'huesigne', '915005987401', 700, null, null, null, null, null, null);
INSERT INTO hg_product_light (id, display_name, manufacturer_name, productid, product_name, archetype, model_id, maxlumen, description, `rank`, version, price, `range`, capability_json) VALUES (7, 'Hue Sultan Bulb 800', 'hue', 'Philips-LCA005-1-A19ECLv7', 'Hue color lamp', 'sultanbulb', 'LCA005', 800, null, null, null, null, null, null);
INSERT INTO hg_product_light (id, display_name, manufacturer_name, productid, product_name, archetype, model_id, maxlumen, description, `rank`, version, price, `range`, capability_json) VALUES (8, 'Hue color downlight', 'hue', 'Philips-LCB001-1-BR30ECLv4', 'Hue color lamp', 'floodbulb', 'LCB001', 650, null, null, null, null, null, null);
");
        $this->execute("
      INSERT INTO hg_product_sensor (id, display_name, manufacturer_name, product_name, type_name, archetype, model_id, description, `rank`, button_count) VALUES (10, 'Hue Dimmer Switch v1', 'hue', 'Hue dimmer switch', 'hue_dimmer_switch', 'ZLLSwitch', 'RWL020', null, null, 4);
INSERT INTO hg_product_sensor (id, display_name, manufacturer_name, product_name, type_name, archetype, model_id, description, `rank`, button_count) VALUES (11, 'Hue Dimmer Switch v2', 'hue', 'Hue dimmer switch', 'hue_dimmer_switch', 'ZLLSwitch', 'RWL022', null, null, 4);
INSERT INTO hg_product_sensor (id, display_name, manufacturer_name, product_name, type_name, archetype, model_id, description, `rank`, button_count) VALUES (12, 'Hue Smart Button', 'hue', 'Hue Smart button', null, 'ZLLSwitch', 'ROM001', null, null, 1);
INSERT INTO hg_product_sensor (id, display_name, manufacturer_name, product_name, type_name, archetype, model_id, description, `rank`, button_count) VALUES (13, 'Hue Tap Dial Switch', 'hue', 'Hue tap dial switch', null, 'ZLLSwitch', 'RDM002', null, null, 4);
INSERT INTO hg_product_sensor (id, display_name, manufacturer_name, product_name, type_name, archetype, model_id, description, `rank`, button_count) VALUES (14, 'Lutron Aurora', 'lutron', 'Lutron Aurora', null, 'ZLLSwitch', 'Z3-1BRL', null, null, null);
INSERT INTO hg_product_sensor (id, display_name, manufacturer_name, product_name, type_name, archetype, model_id, description, `rank`, button_count) VALUES (15, 'Hue Motion Sensor', 'hue', 'Hue motion sensor', null, 'ZLLPresence', 'SML003', null, null, null);
INSERT INTO hg_product_sensor (id, display_name, manufacturer_name, product_name, type_name, archetype, model_id, description, `rank`, button_count) VALUES (16, 'Hue Ambient Light Sensor', 'hue', 'Hue ambient light sensor', null, 'ZLLLightLevel', 'SML003', null, null, null);
INSERT INTO hg_product_sensor (id, display_name, manufacturer_name, product_name, type_name, archetype, model_id, description, `rank`, button_count) VALUES (17, 'Hue Temperature Sensor', 'hue', 'Hue temperature sensor', null, 'ZLLTemperature', 'SML003', null, null, null);
INSERT INTO hg_product_sensor (id, display_name, manufacturer_name, product_name, type_name, archetype, model_id, description, `rank`, button_count) VALUES (18, 'Hue Ambient Light Sensor', 'hue', 'Hue ambient light sensor', null, 'ZLLLightLevel', 'SML001', null, null, null);
INSERT INTO hg_product_sensor (id, display_name, manufacturer_name, product_name, type_name, archetype, model_id, description, `rank`, button_count) VALUES (19, 'Hue Temperature Sensor', 'hue', 'Hue temperature sensor', null, 'ZLLTemperature', 'SML001', null, null, null);
INSERT INTO hg_product_sensor (id, display_name, manufacturer_name, product_name, type_name, archetype, model_id, description, `rank`, button_count) VALUES (20, 'Hue Motion Sensor', 'hue', 'Hue motion sensor', null, 'ZLLPresence', 'SML001', null, null, null);
INSERT INTO hg_product_sensor (id, display_name, manufacturer_name, product_name, type_name, archetype, model_id, description, `rank`, button_count) VALUES (21, 'Hue Motion Sensor', 'hue', 'Hue motion sensor', null, 'ZLLPresence', 'SML002', null, null, null);
INSERT INTO hg_product_sensor (id, display_name, manufacturer_name, product_name, type_name, archetype, model_id, description, `rank`, button_count) VALUES (22, 'Hue Temperature Sensor', 'hue', 'Hue temperature sensor', null, 'ZLLTemperature', 'SML002', null, null, null);
INSERT INTO hg_product_sensor (id, display_name, manufacturer_name, product_name, type_name, archetype, model_id, description, `rank`, button_count) VALUES (23, 'Hue Ambient Light Sensor', 'hue', 'Hue ambient light sensor', null, 'ZLLLightLevel', 'SML002', null, null, null);
        ");
        $this->execute("INSERT INTO hg_device_group_type (id, name, display_name, hue_class_name, `rank`, metadata) VALUES (200, 'bath', 'Bath', 'Toilet', null, null);
INSERT INTO hg_device_group_type (id, name, display_name, hue_class_name, `rank`, metadata) VALUES (205, 'bed', 'Bed', 'Bedroom', null, null);
INSERT INTO hg_device_group_type (id, name, display_name, hue_class_name, `rank`, metadata) VALUES (210, 'closet', 'Closet', 'Closet', null, null);
INSERT INTO hg_device_group_type (id, name, display_name, hue_class_name, `rank`, metadata) VALUES (215, 'common', 'Common', 'Lounge', null, null);
INSERT INTO hg_device_group_type (id, name, display_name, hue_class_name, `rank`, metadata) VALUES (220, 'porch', 'Porch', 'Porch', null, null);
INSERT INTO hg_device_group_type (id, name, display_name, hue_class_name, `rank`, metadata) VALUES (225, 'other', 'Other', 'Other', null, null);

");
        $this->execute("INSERT INTO hg_device_sensor_placement (id, name, display_name, `rank`) VALUES (300, 'normal', 'Normal', null);
INSERT INTO hg_device_sensor_placement (id, name, display_name, `rank`) VALUES (305, 'couch', 'Couch', null);
INSERT INTO hg_device_sensor_placement (id, name, display_name, `rank`) VALUES (310, 'bedside', 'Bedside', null);
INSERT INTO hg_device_sensor_placement (id, name, display_name, `rank`) VALUES (315, 'custom_1', 'Custom 1', null);
INSERT INTO hg_device_sensor_placement (id, name, display_name, `rank`) VALUES (316, 'custom_2', 'Custom 2', null);
INSERT INTO hg_device_sensor_placement (id, name, display_name, `rank`) VALUES (317, 'custom_3', 'Custom 3', null);

");
        $this->execute("INSERT INTO hg_device_light_fixture (id, name, hue_archetype_name, display_name, `rank`, targeting, nl_sensitivity, brightness_mask_percent) VALUES (500, 'overhead', 'sultanbulb', 'Overhead', null, null, null, null);
INSERT INTO hg_device_light_fixture (id, name, hue_archetype_name, display_name, `rank`, targeting, nl_sensitivity, brightness_mask_percent) VALUES (505, 'lamp', null, 'Lamp', null, null, null, null);
INSERT INTO hg_device_light_fixture (id, name, hue_archetype_name, display_name, `rank`, targeting, nl_sensitivity, brightness_mask_percent) VALUES (510, 'strip_ambient', null, 'Strip Ambient', null, 'ambient', null, 10);
INSERT INTO hg_device_light_fixture (id, name, hue_archetype_name, display_name, `rank`, targeting, nl_sensitivity, brightness_mask_percent) VALUES (515, 'strip_direct', null, 'Strip Direct', null, null, null, null);
INSERT INTO hg_device_light_fixture (id, name, hue_archetype_name, display_name, `rank`, targeting, nl_sensitivity, brightness_mask_percent) VALUES (520, 'strip_accent', null, 'Strip Accent', null, null, null, null);
");
    }



    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220912_210849_product_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220912_210849_product_data cannot be reverted.\n";

        return false;
    }
    */
}
