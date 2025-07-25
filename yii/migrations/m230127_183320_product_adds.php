<?php

use yii\db\Migration;

/**
 * Class m230127_183320_product_adds
 */
class m230127_183320_product_adds extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
INSERT INTO hg_product_light (id, display_name, manufacturer_name, productid, product_name, archetype, model_id, maxlumen, description, `rank`, version, price, `range`, capability_json) VALUES (NULL, 'Hue ambiance 800', 'hue', 'Philips-LTA002-2-A19CTv3', 'Hue Ambiance lamp', 'floodbulb', 'LTA002', 800, null, null, null, null, null, null);
");

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('hg_product_light',['productid'=>'Philips-LTA002-2-A19CTv3']);

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230127_183320_product_adds cannot be reverted.\n";

        return false;
    }
    */
}
