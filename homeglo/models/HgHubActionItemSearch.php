<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgHubActionItem;

/**
 * HgHubActionItemSearch represents the model behind the search form of `app\models\HgHubActionItem`.
 */
class HgHubActionItemSearch extends HgHubActionItem
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'hg_hub_action_trigger_id', 'operate_hg_device_light_group_id', 'hg_glo_id', 'override_bri_absolute', 'override_bri_increment_percent', 'override_transition_duration_ms', 'override_transition_at_time'], 'integer'],
            [['entity', 'operation_name', 'operation_value_json', 'display_name', 'metadata'], 'safe'],
            [['override_hue_x', 'override_hue_y'], 'number'],
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
        $query = HgHubActionItem::find();

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
            'hg_hub_action_trigger_id' => $this->hg_hub_action_trigger_id,
            'operate_hg_device_light_group_id' => $this->operate_hg_device_light_group_id,
            'hg_glo_id' => $this->hg_glo_id,
            'override_hue_x' => $this->override_hue_x,
            'override_hue_y' => $this->override_hue_y,
            'override_bri_absolute' => $this->override_bri_absolute,
            'override_bri_increment_percent' => $this->override_bri_increment_percent,
            'override_transition_duration_ms' => $this->override_transition_duration_ms,
            'override_transition_at_time' => $this->override_transition_at_time,
        ]);

        $query->andFilterWhere(['like', 'entity', $this->entity])
            ->andFilterWhere(['like', 'operation_name', $this->operation_name])
            ->andFilterWhere(['like', 'operation_value_json', $this->operation_value_json])
            ->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'metadata', $this->metadata]);

        return $dataProvider;
    }
}
