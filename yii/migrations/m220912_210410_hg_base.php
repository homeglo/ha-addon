<?php

use yii\db\Migration;

/**
 * Class m220912_210410_hg_base
 */
class m220912_210410_hg_base extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("create table hg_device_group_type
(
    id             integer primary key autoincrement,
    name           varchar(255) null,
    display_name   varchar(255) null,
    hue_class_name varchar(255) null,
    `rank`         int          null,
    metadata       text         null
);

create table hg_device_light_fixture
(
    id                      integer primary key autoincrement,
    name                    varchar(255) null,
    hue_archetype_name      varchar(255)              null,
    display_name            varchar(255) null,
    `rank`                  int                       null,
    targeting               varchar(255)              null,
    nl_sensitivity          int                       null,
    brightness_mask_percent integer                null
);

create table hg_device_sensor_placement
(
    id           integer primary key autoincrement,
    name         varchar(255) null,
    display_name varchar(255) null,
    `rank`       int          null
);

create table hg_product_light
(
    id                integer primary key autoincrement,
    display_name      varchar(255) null,
    manufacturer_name varchar(255) null,
    productid         varchar(255) null,
    product_name      varchar(255) null,
    archetype         varchar(255) null,
    model_id          varchar(255) null,
    maxlumen          int          null,
    description       text         null,
    `rank`            int          null,
    version           int          null,
    price             real null,
    `range`           varchar(255) null,
    capability_json   text         null
);

create table hg_product_sensor
(
    id                integer primary key autoincrement,
    display_name      varchar(255) null,
    manufacturer_name varchar(255) null,
    product_name      varchar(255) null,
    type_name         varchar(255) null,
    archetype         varchar(255) null,
    model_id          varchar(255) null,
    description       text         null,
    `rank`            int          null,
    button_count      int          null
);

create table hg_status
(
    id            integer primary key autoincrement,
    name          varchar(255) null,
    display_name  varchar(255) null,
    category_name varchar(255) null,
    `rank`        int          null
);

create table hg_version
(
    id           integer primary key autoincrement,
    version      varchar(255) null,
    display_name varchar(255) null
);

create table hg_home
(
    id            integer primary key autoincrement,
    updated_at    int            null,
    created_at    int            null,
    name          varchar(255)   null,
    display_name  varchar(255)   null,
    lat           real null,
    lng           real null,
    hg_version_id int   null,
    constraint hg_home_ibfk_1
        foreign key (hg_version_id) references hg_version (id)
);

create table hg_glozone
(
    id                             integer primary key autoincrement,
    created_at                     int          null,
    updated_at                     int          null,
    hg_home_id                     int null,
    name                           varchar(255) null,
    display_name                   varchar(255) null,
    bed_time_weekday_midnightmins  varchar(255) null,
    wake_time_weekday_midnightmins varchar(255) null,
    bed_time_weekend_midnightmins  varchar(255) null,
    wake_time_weekend_midnightmins varchar(255) null,
    metadata                       text         null,
    constraint hg_glozone_ibfk_1
        foreign key (hg_home_id) references hg_home (id)
            on update cascade on delete cascade
);

create index idx_glozone_home_id
    on hg_glozone (hg_home_id);

create index hg_version_id
    on hg_home (hg_version_id);

create table hg_hub
(
    id               integer primary key autoincrement,
    created_at       int                     null,
    updated_at       int                     null,
    hg_home_id       int            null,
    hg_status_id     int            null,
    display_name     varchar(255) default '' not null,
    access_token     varchar(255) default '' not null,
    bearer_token     varchar(255)            null,
    refresh_token    varchar(255)            null,
    token_expires_at int                     null,
    hue_email        varchar(255)            null,
    hue_random       varchar(255)            null,
    notes            text                    null,
    metadata         text                    null,
    constraint hg_hub_ibfk_1
        foreign key (hg_home_id) references hg_home (id)
            on update cascade on delete cascade,
    constraint hg_hub_ibfk_3
        foreign key (hg_status_id) references hg_status (id)
);

create table hg_device_group
(
    id                      integer primary key autoincrement,
    created_at              int                       null,
    updated_at              int                       null,
    hg_hub_id               int              null,
    hg_device_group_type_id int              null,
    hg_glozone_id           int              null,
    room_invoke_order       int                       null,
    is_room                 integer                   null,
    hue_id                  int                       null,
    display_name            varchar(255) null,
    metadata                text                      null,
    constraint hg_device_group_ibfk_1
        foreign key (hg_hub_id) references hg_hub (id)
            on update cascade on delete cascade,
    constraint hg_device_light_group_hg_glozone_null_fk
        foreign key (hg_glozone_id) references hg_glozone (id)
            on delete cascade,
    constraint hg_device_light_group_hg_group_type_id_fk
        foreign key (hg_device_group_type_id) references hg_device_group_type (id)
);

create index idx_device_group_hg_hub_id
    on hg_device_group (hg_hub_id);

create table hg_device_light
(
    id                         integer primary key autoincrement,
    created_at                 int          null,
    updated_at                 int          null,
    hg_hub_id                  int null,
    hue_id                     int          null,
    display_name               varchar(255) null,
    primary_hg_device_group_id int null,
    hg_product_light_id        int null,
    hg_device_light_fixture    int null,
    metadata                   text         null,
    constraint hg_device_light_ibfk_1
        foreign key (hg_hub_id) references hg_hub (id)
            on update cascade on delete cascade,
    constraint hg_device_light_ibfk_2
        foreign key (primary_hg_device_group_id) references hg_device_group (id)
            on update set null on delete set null,
    constraint hg_device_light_ibfk_3
        foreign key (hg_product_light_id) references hg_product_light (id),
    constraint hg_device_light_ibfk_4
        foreign key (hg_device_light_fixture) references hg_device_light_fixture (id)
);

create index idx_hg_device_light_fixture
    on hg_device_light (hg_device_light_fixture);

create index idx_device_light_hg_hub_id
    on hg_device_light (hg_hub_id);

create index hg_product_light_id
    on hg_device_light (hg_product_light_id);

create index idx_device_light_room_id
    on hg_device_light (primary_hg_device_group_id);

create table hg_device_sensor
(
    id                              integer primary key autoincrement,
    created_at                      int          null,
    updated_at                      int          null,
    hg_hub_id                       int null,
    hue_id                          int          null,
    hue_sensor_variable_id          int          null,
    display_name                    varchar(255) null,
    hg_device_group_id              int null,
    hg_product_sensor_id            int null,
    hg_device_sensor_placement_id   int null,
    switch_dimmer_increment_percent int          null,
    metadata                        text         null,
    constraint hg_device_sensor_ibfk_1
        foreign key (hg_hub_id) references hg_hub (id)
            on update cascade on delete cascade,
    constraint hg_device_sensor_ibfk_2
        foreign key (hg_device_group_id) references hg_device_group (id)
            on update set null on delete set null,
    constraint hg_device_sensor_ibfk_4
        foreign key (hg_product_sensor_id) references hg_product_sensor (id),
    constraint hg_device_sensor_ibfk_5
        foreign key (hg_device_sensor_placement_id) references hg_device_sensor_placement (id)
);

create index hg_device_sensor_placement_id
    on hg_device_sensor (hg_device_sensor_placement_id);

create index idx_device_sensor_hg_hub_id
    on hg_device_sensor (hg_hub_id);

create index idx_hg_product_sensor
    on hg_device_sensor (hg_product_sensor_id);

create index idx_device_sensor_room_id
    on hg_device_sensor (hg_device_group_id);

create table hg_glo
(
    id             integer primary key autoincrement,
    created_at     int           null,
    updated_at     int           null,
    base_hg_glo_id int  null,
    name           varchar(255)  null,
    hub_name       varchar(255)  null,
    display_name   varchar(255)  null,
    hg_status_id   int  null,
    hg_glozone_id  int  null,
    hg_hub_id      int  null,
    hg_version_id  int  null,
    hue_ids        text          null,
    `rank`         int           null,
    hue_x          real null,
    hue_y          real null,
    brightness     int           null,
    metadata       text          null,
    constraint hg_glo_hg_glo_id_fk
        foreign key (base_hg_glo_id) references hg_glo (id),
    constraint hg_glo_ibfk_1
        foreign key (hg_status_id) references hg_status (id),
    constraint hg_glo_ibfk_2
        foreign key (hg_glozone_id) references hg_glozone (id)
            on update cascade on delete cascade,
    constraint hg_glo_ibfk_3
        foreign key (hg_hub_id) references hg_hub (id)
            on update cascade on delete cascade,
    constraint hg_glo_ibfk_4
        foreign key (hg_version_id) references hg_version (id)
);

create index hg_glozone_id
    on hg_glo (hg_glozone_id);

create index idx_glo_hg_hub_id
    on hg_glo (hg_hub_id);

create index idx_glo_status_id
    on hg_glo (hg_status_id);

create table hg_glo_device_light
(
    id                 integer primary key autoincrement,
    created_at         int               null,
    updated_at         int               null,
    hg_glo_id          int      null,
    hg_device_light_id int      null,
    hg_device_group_id int      null,
    hg_hub_id          int      null,
    hue_scene_id       varchar(255)      null,
    `on`               integer           null,
    hue_x              real     null,
    hue_y              real     null,
    bri_absolute       int               null,
    metadata           text null,
    constraint foreign_key_name
        foreign key (hg_hub_id) references hg_hub (id)
            on update cascade on delete cascade,
    constraint hg_glo_device_light_hg_device_group_id_fk
        foreign key (hg_device_group_id) references hg_device_group (id)
            on update cascade on delete cascade,
    constraint hg_glo_device_light_ibfk_1
        foreign key (hg_glo_id) references hg_glo (id)
            on update cascade on delete cascade,
    constraint hg_glo_device_light_ibfk_2
        foreign key (hg_device_light_id) references hg_device_light (id)
            on update cascade on delete cascade
);

create table hg_device_group_light
(
    id                 integer primary key autoincrement,
    hg_device_group_id int      null,
    hg_device_light_id int      null,
    metadata           text null,
    constraint hg_device_group_light_ibfk_1
        foreign key (hg_device_group_id) references hg_device_group (id)
            on update cascade on delete cascade,
    constraint hg_device_group_light_ibfk_2
        foreign key (hg_device_light_id) references hg_glo_device_light (id)
            on update cascade on delete cascade
);

create index hg_device_light_group_id
    on hg_device_group_light (hg_device_group_id);

create index idx_device_group_light_device_light_id
    on hg_device_group_light (hg_device_light_id);

create index idx_glo_device_light_device_light_id
    on hg_glo_device_light (hg_device_light_id);

create index idx_glo_device_light_glo_id
    on hg_glo_device_light (hg_glo_id);

create table hg_glozone_time_block
(
    id                              integer primary key autoincrement,
    name                            varchar(255) null,
    display_name                    varchar(255) null,
    hg_glozone_id                   int null,
    default_hg_glo_id               int null,
    hg_status_id                    int null,
    base_hg_glozone_time_block_id   int null,
    time_start_default_midnightmins varchar(255) null,
    time_end_default_midnightmins   varchar(255) null,
    smartOn_switch_behavior         varchar(255) null,
    smartOn_motion_behavior         varchar(255) null,
    smartTransition_behavior        varchar(255) null,
    smartTransition_duration_ms     int          null,
    time_start_sun_midnightmins     varchar(255) null,
    time_end_sun_midnightmins       varchar(255) null,
    time_start_mon_midnightmins     varchar(255) null,
    time_end_mon_midnightmins       varchar(255) null,
    time_start_tue_midnightmins     varchar(255) null,
    time_end_tue_midnightmins       varchar(255) null,
    time_start_wed_midnightmins     varchar(255) null,
    time_end_wed_midnightmins       varchar(255) null,
    time_start_thu_midnightmins     varchar(255) null,
    time_end_thu_midnightmins       varchar(255) null,
    time_start_fri_midnightmins     varchar(255) null,
    time_end_fri_midnightmins       varchar(255) null,
    time_start_sat_midnightmins     varchar(255) null,
    time_end_sat_midnightmins       varchar(255) null,
    timezone                        varchar(255) null,
    metadata                        text         null,
    constraint hg_glozone_time_block_hg_glo_id_fk
        foreign key (default_hg_glo_id) references hg_glo (id)
            on delete set null,
    constraint hg_glozone_time_block_hg_glozone_null_fk
        foreign key (hg_glozone_id) references hg_glozone (id)
            on delete cascade,
    constraint hg_glozone_time_block_hg_glozone_time_block_id_fk
        foreign key (base_hg_glozone_time_block_id) references hg_glozone_time_block (id),
    constraint hg_glozone_time_block_hg_status_id_fk
        foreign key (hg_status_id) references hg_status (id)
);

create table hg_glozone_smart_transition
(
    id                       integer primary key autoincrement,
    created_at               int          null,
    updated_at               int          null,
    hg_glozone_time_block_id int null,
    hg_device_group_id       int null,
    hg_status_id             int null,
    `rank`                   int          null,
    behavior_name            varchar(255) null,
    last_trigger_at          int          null,
    last_trigger_status      varchar(255) null,
    metadata                 text         null,
    constraint hg_glozone_smartTransition_hg_status_id_fk
        foreign key (hg_status_id) references hg_status (id),
    constraint hg_smartTransition_hg_device_group_null_fk
        foreign key (hg_device_group_id) references hg_device_group (id)
            on update cascade on delete cascade,
    constraint hg_smartTransition_hg_glozone_time_block_null_fk
        foreign key (hg_glozone_time_block_id) references hg_glozone_time_block (id)
            on update cascade on delete cascade
);

create index hg_glozone_time_block_hg_glo_null_fk
    on hg_glozone_time_block (hg_glozone_id);

create index idx_hub_home_id
    on hg_hub (hg_home_id);

create index idx_hub_status_id
    on hg_hub (hg_status_id);

create table hg_hub_action_template
(
    id                          integer primary key autoincrement,
    created_at                  int          null,
    updated_at                  int          null,
    hg_hub_id                   int null,
    hg_version_id               int null,
    hg_status_id                int null,
    hg_product_sensor_type_name varchar(255) null,
    name                        varchar(255) null,
    display_name                varchar(255) null,
    platform                    varchar(255) null,
    multi_room                  integer   null,
    metadata                    text         null,
    constraint hg_hub_action_template_hg_hub_null_fk
        foreign key (hg_hub_id) references hg_hub (id)
            on delete cascade,
    constraint hg_hub_action_template_hg_status_id_fk
        foreign key (hg_status_id) references hg_status (id),
    constraint hg_hub_action_template_hg_version_id_fk
        foreign key (hg_version_id) references hg_version (id)
);

create table hg_hub_action_trigger
(
    id                             integer primary key autoincrement,
    created_at                     int          null,
    updated_at                     int          null,
    name                           varchar(255) null,
    display_name                   varchar(255) null,
    source_name                    varchar(255) null,
    event_name                     varchar(255) null,
    event_data                     text         null,
    last_triggered_at              int          null,
    last_checked_at                int          null,
    hg_hub_action_template_id      int null,
    hg_hub_id                      int null,
    hg_device_sensor_id            int null,
    hg_glozone_start_time_block_id int null,
    hg_glozone_end_time_block_id   int null,
    hg_status_id                   int null,
    `rank`                         int          null,
    metadata                       text         null,
    constraint hg_hub_action_rule_hg_hub_action_template_null_fk
        foreign key (hg_hub_action_template_id) references hg_hub_action_template (id)
            on delete cascade,
    constraint hg_hub_action_trigger_hg_device_sensor_null_fk
        foreign key (hg_device_sensor_id) references hg_device_sensor (id)
            on delete cascade,
    constraint hg_hub_action_trigger_hg_glozone_time_block_id_fk
        foreign key (hg_glozone_end_time_block_id) references hg_glozone_time_block (id),
    constraint hg_hub_action_trigger_hg_glozone_time_block_null_fk
        foreign key (hg_glozone_start_time_block_id) references hg_glozone_time_block (id),
    constraint hg_hub_action_trigger_ibfk_1
        foreign key (hg_hub_id) references hg_hub (id)
            on update cascade on delete cascade,
    constraint hg_hub_action_trigger_ibfk_3
        foreign key (hg_status_id) references hg_status (id)
);

create table hg_hub_action_condition
(
    id                       integer primary key autoincrement,
    created_at               int          null,
    updated_at               int          null,
    hg_hub_action_trigger_id int null,
    hg_status_id             int null,
    name                     varchar(255) null,
    display_name             varchar(255) null,
    property                 varchar(255) null,
    operator                 varchar(255) null,
    value                    varchar(255) null,
    metadata                 text         null,
    constraint hg_hub_action_condition_hg_status_id_fk
        foreign key (hg_status_id) references hg_status (id),
    constraint hg_hub_action_condition_ibfk_1
        foreign key (hg_hub_action_trigger_id) references hg_hub_action_trigger (id)
            on update cascade on delete cascade
);

create index idx_hub_action_condition_trigger_id
    on hg_hub_action_condition (hg_hub_action_trigger_id);

create table hg_hub_action_item
(
    id                               integer primary key autoincrement,
    created_at                       int           null,
    updated_at                       int           null,
    hg_hub_action_trigger_id         int  null,
    entity                           varchar(255)  null,
    operation_name                   varchar(255)  null,
    operation_value_json             text          null,
    operate_hg_device_light_group_id int  null,
    hg_glo_id                        int  null,
    display_name                     varchar(255)  null,
    override_hue_x                   real null,
    override_hue_y                   real null,
    override_bri_absolute            int           null,
    override_bri_increment_percent   int           null,
    override_transition_duration_ms  int           null,
    override_transition_at_time      int           null,
    metadata                         text          null,
    constraint hg_hub_action_item_ibfk_1
        foreign key (hg_hub_action_trigger_id) references hg_hub_action_trigger (id)
            on update cascade on delete cascade,
    constraint hg_hub_action_item_ibfk_3
        foreign key (hg_glo_id) references hg_glo (id)
            on update cascade on delete cascade,
    constraint hg_hub_action_item_ibfk_5
        foreign key (operate_hg_device_light_group_id) references hg_device_group (id)
            on update set null on delete set null
);

create index idx_hub_action_item_glo_id
    on hg_hub_action_item (hg_glo_id);

create index idx_hub_action_item_trigger_id
    on hg_hub_action_item (hg_hub_action_trigger_id);

create index operate_hg_device_light_group_id
    on hg_hub_action_item (operate_hg_device_light_group_id);

create index idx_hub_action_trigger_hg_hub_id
    on hg_hub_action_trigger (hg_hub_id);

create index idx_hub_action_trigger_status_id
    on hg_hub_action_trigger (hg_status_id);

create index hg_type_id
    on hg_hub_action_trigger (hg_hub_action_template_id);


");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220912_210410_hg_base cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220912_210410_hg_base cannot be reverted.\n";

        return false;
    }
    */
}
