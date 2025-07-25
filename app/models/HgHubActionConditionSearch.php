<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgHubActionCondition;

/**
 * HgHubActionConditionSearch represents the model behind the search form of `app\models\HgHubActionCondition`.
 */
class HgHubActionConditionSearch extends HgHubActionCondition
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'hg_hub_action_trigger_id', 'hg_status_id'], 'integer'],
            [['name', 'display_name', 'property', 'operator', 'value', 'metadata'], 'safe'],
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
        $query = HgHubActionCondition::find();

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
            'hg_status_id' => $this->hg_status_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'property', $this->property])
            ->andFilterWhere(['like', 'operator', $this->operator])
            ->andFilterWhere(['like', 'value', $this->value])
            ->andFilterWhere(['like', 'metadata', $this->metadata]);

        return $dataProvider;
    }
}
