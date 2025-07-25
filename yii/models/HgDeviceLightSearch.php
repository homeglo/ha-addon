<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgDeviceLight;

/**
 * HgDeviceLightSearch represents the model behind the search form of `app\models\HgDeviceLight`.
 */
class HgDeviceLightSearch extends HgDeviceLight
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'hg_hub_id', 'primary_hg_device_group_id', 'hg_product_light_id', 'hg_device_light_fixture'], 'integer'],
            [['serial', 'ha_device_id'], 'string'],
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
    public function search($params, $hg_hub_ids)
    {
        $query = HgDeviceLight::find();

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
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'hg_hub_id' => $this->hg_hub_id,
            'ha_device_id' => $this->ha_device_id,
            'serial'=>$this->serial,
            'primary_hg_device_group_id' => $this->primary_hg_device_group_id,
            'hg_product_light_id' => $this->hg_product_light_id,
            'hg_device_light_fixture' => $this->hg_device_light_fixture,
        ]);

        if (!empty($hg_hub_ids)) {
            $query->andFilterWhere(['IN','hg_hub_id',$hg_hub_ids]);
        }

        $query->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'metadata', $this->metadata]);

        return $dataProvider;
    }
}
