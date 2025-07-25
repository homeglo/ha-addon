<?php

use yii\db\Migration;

/**
 * Class m230220_163339_sensor_glozone
 */
class m230220_163339_sensor_glozone extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            alter table hg_device_sensor
                add hg_glozone_id int null;
        ");

        foreach (\app\models\HgDeviceSensor::find()->all() as $hgDeviceSensor) {
            $hgDeviceSensor->hg_glozone_id = $hgDeviceSensor->hgDeviceGroup->hg_glozone_id;
            $hgDeviceSensor->save();
        }


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230220_163339_sensor_glozone cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230220_163339_sensor_glozone cannot be reverted.\n";

        return false;
    }
    */
}
