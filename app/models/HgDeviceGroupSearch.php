<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgDeviceGroup;

/**
 * HgDeviceGroupSearch represents the model behind the search form of `app\models\HgDeviceGroup`.
 */
class HgDeviceGroupSearch extends HgDeviceGroup
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'hg_hub_id', 'hg_device_group_type_id', 'hg_glozone_id', 'is_room'], 'integer'],
            [['display_name', 'metadata', 'ha_device_id'], 'safe'],
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
    public function search($params, $hg_hub_ids=[])
    {
        $query = HgDeviceGroup::find();

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
            'hg_device_group_type_id' => $this->hg_device_group_type_id,
            'hg_glozone_id' => $this->hg_glozone_id,
            'is_room' => $this->is_room,
            'ha_device_id' => $this->ha_device_id,
        ]);

        $query->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'metadata', $this->metadata]);

        return $dataProvider;
    }
}
