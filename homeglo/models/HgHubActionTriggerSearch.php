<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgHubActionTrigger;

/**
 * HgHubActionTriggerSearch represents the model behind the search form of `app\models\HgHubActionTrigger`.
 */
class HgHubActionTriggerSearch extends HgHubActionTrigger
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'hg_hub_id', 'hg_device_sensor_id', 'hg_glozone_start_time_block_id', 'hg_glozone_end_time_block_id', 'hg_hub_action_template_id', 'hg_status_id', 'rank'], 'integer'],
            [['name', 'display_name', 'source_name', 'event_name', 'event_data', 'metadata'], 'safe'],
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
        $query = HgHubActionTrigger::find();

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
            'hg_hub_id' => $this->hg_hub_id,
            'hg_device_sensor_id' => $this->hg_device_sensor_id,
            'hg_glozone_start_time_block_id' => $this->hg_glozone_start_time_block_id,
            'hg_glozone_end_time_block_id' => $this->hg_glozone_end_time_block_id,
            'hg_hub_action_template_id' => $this->hg_hub_action_template_id,
            'hg_status_id' => $this->hg_status_id,
            'rank' => $this->rank,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'source_name', $this->source_name])
            ->andFilterWhere(['like', 'event_name', $this->event_name])
            ->andFilterWhere(['like', 'event_data', $this->event_data])
            ->andFilterWhere(['like', 'metadata', $this->metadata]);

        return $dataProvider;
    }
}
