<?php

use yii\db\Migration;

/**
 * Class m221128_213734_glo_room_table
 */
class m221128_213734_glo_room_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("create table hg_glo_device_group
                        (
                            id                 integer primary key autoincrement,
                            created_at         int          null,
                            updated_at         int          null,
                            hg_glozone_id      int null,
                            hg_glo_id          int null,
                            hg_device_group_id int null,
                            hg_hub_id          int null,
                            hub_display_name   varchar(255) null,
                            hue_scene_id       varchar(255) null,
                            metadata           text         null,
                            constraint hg_glo_device_group_hg_device_group_null_fk
                                foreign key (hg_device_group_id) references hg_device_group (id)
                                    on update cascade on delete cascade,
                            constraint hg_glo_device_group_hg_glo_null_fk
                                foreign key (hg_glo_id) references hg_glo (id)
                                    on update cascade on delete cascade,
                            constraint hg_glo_device_group_hg_glozone_id_fk
                                foreign key (hg_glozone_id) references hg_glozone (id)
                                    on update cascade on delete cascade,
                            constraint hg_glo_device_group_hg_hub__fk
                                foreign key (hg_hub_id) references hg_hub (id)
                                    on update cascade on delete cascade
                        );

");

        $this->execute("create table hg_device_sensor_device_group_multiroom
                        (
                            id                  integer primary key autoincrement,
                            created_at          int          null,
                            updated_at          int          null,
                            hg_device_sensor_id int null,
                            hg_device_group_id  int null,
                            metadata            text         null,
                            constraint hg_device_sensor_device_group_multiroom_1
                                foreign key (hg_device_sensor_id) references hg_device_sensor (id)
                                    on update cascade on delete cascade,
                            constraint hg_device_sensor_device_group_multiroom_2
                                foreign key (hg_device_group_id) references hg_device_group (id)
                                    on update cascade on delete cascade
                        );

");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221128_213734_glo_room_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221128_213734_glo_room_table cannot be reverted.\n";

        return false;
    }
    */
}
