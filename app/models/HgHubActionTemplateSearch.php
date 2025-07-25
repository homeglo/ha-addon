<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgHubActionTemplate;

/**
 * HgHubActionTemplateSearch represents the model behind the search form of `app\models\HgHubActionTemplate`.
 */
class HgHubActionTemplateSearch extends HgHubActionTemplate
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'hg_hub_id', 'hg_version_id', 'hg_status_id', 'multi_room','hg_hub_action_map_id'], 'integer'],
            [['hg_product_sensor_type_name', 'name', 'display_name', 'platform', 'metadata'], 'safe'],
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
    public function search($params,$hg_hub_ids=[])
    {
        $query = HgHubActionTemplate::find();

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
            'hg_version_id' => $this->hg_version_id,
            'hg_status_id' => $this->hg_status_id,
            'multi_room' => $this->multi_room,
            'hg_hub_action_map_id'=>$this->hg_hub_action_map_id
        ]);

        $query->andFilterWhere(['like', 'hg_product_sensor_type_name', $this->hg_product_sensor_type_name])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'platform', $this->platform])
            ->andFilterWhere(['like', 'metadata', $this->metadata]);

        return $dataProvider;
    }
}
