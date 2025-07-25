<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgDeviceSensor;

/**
 * HgDeviceSensorSearch represents the model behind the search form of `app\models\HgDeviceSensor`.
 */
class HgDeviceSensorSearch extends HgDeviceSensor
{
    public $hg_glozone_id;
    public $product_type_name=[];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[ 'created_at', 'updated_at', 'hg_hub_id', 'hg_device_group_id', 'hg_product_sensor_id', 'hg_device_sensor_placement_id', 'switch_dimmer_increment_percent'], 'integer'],
            [['display_name', 'metadata'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = HgDeviceSensor::find()->joinWith(['hgProductSensor']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'hg_device_sensor.id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'ha_device_id' => $this->ha_device_id,
            'hg_product_sensor_id' => $this->hg_product_sensor_id,
            'hg_device_sensor_placement_id' => $this->hg_device_sensor_placement_id,
            'switch_dimmer_increment_percent' => $this->switch_dimmer_increment_percent,
        ]);

        if ($this->hg_glozone_id) {
            $query->andWhere(['hg_glozone_id'=>$this->hg_glozone_id]);
            $query->orWhere(['IS','hg_glozone_id',NULL]);
        }


        if (!empty($this->product_type_name))
            $query->andWhere(['type_name'=>$this->product_type_name]);

        $query->andFilterWhere(['like', 'hg_device_sensor.display_name', $this->display_name])
            ->andFilterWhere(['like', 'metadata', $this->metadata]);

        $query->orderBy('hg_device_group_id DESC');

        return $dataProvider;
    }
}
