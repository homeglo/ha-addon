<?php

use yii\db\Migration;

/**
 * Class m221007_150813_transition_execute
 */
class m221007_150813_transition_execute extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("create table hg_glozone_smart_transition_execute
(
    id                             integer primary key autoincrement,
    created_at                     int          null,
    updated_at                     int          null,
    hg_glozone_smart_transition_id int null,
    time_block_today_time          int          null,
    hg_status_id                   int          null,
    metadata                       text         null,
    constraint hg_glozone_smart_transition_execute_smart_transition_null_fk
        foreign key (hg_glozone_smart_transition_id) references hg_glozone_smart_transition (id)
            on update cascade on delete cascade
);

");

        $this->execute("
INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (550, 'success', 'Success', 'smart_transition_execute', null);
INSERT INTO hg_status (id, name, display_name, category_name, `rank`) VALUES (560, 'fail', 'Fail', 'smart_transition_execute', null);
");

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221007_150813_transition_execute cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221007_150813_transition_execute cannot be reverted.\n";

        return false;
    }
    */
}
