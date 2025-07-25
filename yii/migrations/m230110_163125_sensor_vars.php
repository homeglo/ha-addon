<?php

use yii\db\Migration;

/**
 * Class m230110_163125_sensor_vars
 */
class m230110_163125_sensor_vars extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //create table
        $this->execute("
            create table hg_device_sensor_variable
(
    id                  integer primary key autoincrement,
    created_at          int          null,
    updated_at          int          null,
    display_name        varchar(255) null,
    hg_device_sensor_id int null,
    variable_name       varchar(255)          null,
    value               varchar(255)          null,
    hg_status_id        int null,
    description         text         null,
    json_data           text          null,
    sensor_type_name    varchar(255) null,
    override_hg_product_sensor_id int null,
    acceptable_values   text null,
    constraint hg_device_sensor_variable_hg_device_sensor_null_fk
        foreign key (hg_device_sensor_id) references hg_device_sensor (id)
            on update cascade on delete cascade,
    constraint hg_device_sensor_variable_hg_status_id_fk
        foreign key (hg_status_id) references hg_status (id),
    constraint hg_device_sensor_variable_hg_product_sensor_id_fk
        foreign key (override_hg_product_sensor_id) references hg_product_sensor (id)
            on update cascade on delete cascade
);

        ");


        //add new statuses
        $this->execute("
        INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (600, 'default_sensor_variable', 'Default Sensor Variable', 'sensor_variable', null);
        INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (605, 'user_sensor_variable', 'User Sensor Variable', 'sensor_variable', null);
        ");

        $this->execute("
            UPDATE hg_product_sensor SET type_name = 'hue_ambient_sensor' where ID in (16,18,23);
            UPDATE hg_product_sensor SET type_name = 'hue_temperature_sensor' where ID in (17,19,23);
        ");

        $this->execute("
        INSERT INTO hg_device_sensor_variable (id, created_at, updated_at, display_name, hg_device_sensor_id, override_hg_product_sensor_id, variable_name, value, sensor_type_name, hg_status_id, description, json_data, acceptable_values) VALUES (1, 1673375600, 1673384733, 'Motion Warning Timer', null, null, 'motion_warning_timer', 'PT00:05:00', 'hue_motion_sensor', 600, null, null, null);
        INSERT INTO hg_device_sensor_variable (id, created_at, updated_at, display_name, hg_device_sensor_id, override_hg_product_sensor_id, variable_name, value, sensor_type_name, hg_status_id, description, json_data, acceptable_values) VALUES (2, 1673384956, 1673384956, 'Motion Finalize Timer', null, null, 'motion_finalize_timer', 'PT00:00:30', 'hue_motion_sensor', 600, null, null, null);
        INSERT INTO hg_device_sensor_variable (id, created_at, updated_at, display_name, hg_device_sensor_id, override_hg_product_sensor_id, variable_name, value, sensor_type_name, hg_status_id, description, json_data, acceptable_values) VALUES (4, 1673388564, 1673388564, 'Motion Default Sensitivity', null, null, 'motion_default_sensitivity', '3', 'hue_motion_sensor', 600, null, null, null);
        INSERT INTO hg_device_sensor_variable (id, created_at, updated_at, display_name, hg_device_sensor_id, override_hg_product_sensor_id, variable_name, value, sensor_type_name, hg_status_id, description, json_data, acceptable_values) VALUES (5, 1673388591, 1673388591, 'Motion Default Sensitivity', null, 20, 'motion_default_sensitivity', '1', 'hue_motion_sensor', 600, null, null, null);
        INSERT INTO hg_device_sensor_variable (id, created_at, updated_at, display_name, hg_device_sensor_id, override_hg_product_sensor_id, variable_name, value, sensor_type_name, hg_status_id, description, json_data, acceptable_values) VALUES (6, 1673388656, 1673388656, 'Motion Default Sensitivity', null, 21, 'motion_default_sensitivity', '1', 'hue_motion_sensor', 600, null, null, null);
        INSERT INTO hg_device_sensor_variable (id, created_at, updated_at, display_name, hg_device_sensor_id, override_hg_product_sensor_id, variable_name, value, sensor_type_name, hg_status_id, description, json_data, acceptable_values) VALUES (7, 1673388869, 1673388869, 'Ambient Default Darkness Threshold', null, null, 'ambient_default_darkness_threshold', '65000', 'hue_ambient_sensor', 600, null, null, null);

        ");

        // SQLite doesn't support MODIFY column syntax
        // SQLite is flexible with data types, so existing integer columns can store varchar values
        echo "Column type modifications are not needed in SQLite due to its flexible typing system.\n";

        \app\models\HgDeviceSensorVariable::syncAllSensorsAndVariables();

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('hg_device_sensor_variable');
        $this->delete('hg_status',['id'=>[600,605]]);

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230110_163125_sensor_vars cannot be reverted.\n";

        return false;
    }
    */
}
