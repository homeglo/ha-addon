<?php

namespace app\models;

use app\components\HelperComponent;
use Yii;

/**
 * This is the model class for table "hg_product_light".
 *
 * @property int $id
 * @property string|null $display_name
 * @property string|null $manufacturer_name
 * @property string|null $productid
 * @property string|null $product_name
 * @property string|null $archetype
 * @property string|null $model_id
 * @property int|null $maxlumen
 * @property string|null $description
 * @property int|null $rank
 * @property int|null $version
 * @property float|null $price
 * @property string|null $range
 * @property string|null $capability_json
 *
 * @property HgDeviceLight[] $hgDeviceLights
 */
class HgProductLight extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_product_light';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['maxlumen', 'rank', 'version'], 'integer'],
            [['description', 'capability_json'], 'string'],
            [['price'], 'number'],
            [['display_name', 'manufacturer_name', 'productid', 'product_name', 'archetype', 'model_id', 'range'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'display_name' => 'Product Name',
            'manufacturer_name' => 'Manufacturer Name',
            'productid' => 'Productid',
            'product_name' => 'Product Name',
            'archetype' => 'Archetype',
            'model_id' => 'Model ID',
            'maxlumen' => 'Maxlumen',
            'description' => 'Description',
            'rank' => 'Rank',
            'version' => 'Version',
            'price' => 'Price',
            'range' => 'Range',
            'capability_json' => 'Capability Json',
        ];
    }

    /**
     * Gets query for [[HgDeviceLights]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceLights()
    {
        return $this->hasMany(HgDeviceLight::className(), ['hg_product_light_id' => 'id']);
    }

    /**
     * take light data from hub and figure out if it exists in product table. if not add it
     * @param array $data
     */
    public static function triageHueLight(array $data)
    {
        //check for modelid in table
        /*if (isset($data['modelid'])) {
            $hgProductLight = ;
            if ($hgProductLight)
                return $hgProductLight;
        } else {*/ //this is a product we haven't seen before
            $hgProductLight = HgProductLight::find()->where(['model_id'=>$data['modelid']])->one() ?? new HgProductLight();
            $hgProductLight->display_name = $data['productname'].' '.@$data['capabilities']['control']['maxlumen'];
            $hgProductLight->manufacturer_name = $data['manufacturername'];
            $hgProductLight->productid = $data['productid'];
            $hgProductLight->product_name = $data['productname'];
            $hgProductLight->archetype = $data['config']['archetype'];
            $hgProductLight->model_id = $data['modelid'];
            $hgProductLight->maxlumen = $data['capabilities']['control']['maxlumen'];
            $hgProductLight->capability_json = json_encode($data['capabilities']);
            if (!$hgProductLight->save()) {
                Yii::error(HelperComponent::getFirstErrorFromFailedValidation($hgProductLight));
            }
        //}

        return $hgProductLight;
    }
}
