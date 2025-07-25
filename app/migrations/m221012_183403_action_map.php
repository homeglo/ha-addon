<?php

use yii\db\Migration;

/**
 * Class m221012_183403_action_map
 */
class m221012_183403_action_map extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("alter table hg_hub_action_trigger
    add hue_id int null;
");
        $this->execute("alter table hg_product_sensor
    add action_map_type varchar(255) null;");

        $this->execute("create table hg_hub_action_map
(
    id                         integer primary key autoincrement,
    created_at                 int          null,
    updated_at                 int          null,
    name                       varchar(255) null,
    display_name               varchar(255) null,
    map_image_url              varchar(255) null,
    base_hg_hub_action_map_id  int null,
    hg_product_sensor_map_type varchar(255) null,
    hg_status_id               int null,
    metadata                   text         null,
    constraint hg_hub_action_map_hg_hub_action_map_id_fk
        foreign key (base_hg_hub_action_map_id) references hg_hub_action_map (id)
            on update set null on delete set null,
    constraint hg_hub_action_map_hg_status_id_fk
        foreign key (hg_status_id) references hg_status (id)
);

");

        $this->execute("alter table hg_hub_action_template
                            add hg_hub_action_map_id int null;
                        
                        ");

        $this->execute("alter table hg_device_sensor
                    add hg_hub_action_map_id int null;
                
                ");

        $this->execute("INSERT INTO hg_hub_action_map (id, created_at, updated_at, name, display_name, map_image_url, base_hg_hub_action_map_id, hg_product_sensor_map_type, hg_status_id, metadata) VALUES (1, null, null, 'wework_map_1', 'WeWork Map 1', null, null, 'hue_dimmer_switch_4', null, null);");


        $this->execute("update hg_hub_action_template set hg_hub_action_map_id = 1 where hg_hub_action_map_id is null;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221012_183403_action_map cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221012_183403_action_map cannot be reverted.\n";

        return false;
    }
    */
}
