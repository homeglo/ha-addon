<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgGlozoneSmartTransition;

/**
 * HgGlozoneSmartTransitionSearch represents the model behind the search form of `app\models\HgGlozoneSmartTransition`.
 */
class HgGlozoneSmartTransitionSearch extends HgGlozoneSmartTransition
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'hg_glozone_time_block_id', 'hg_device_group_id', 'hg_status_id', 'rank', 'last_trigger_at'], 'integer'],
            [['behavior_name', 'last_trigger_status', 'metadata'], 'safe'],
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
        $query = HgGlozoneSmartTransition::find();

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
            'hg_glozone_time_block_id' => $this->hg_glozone_time_block_id,
            'hg_device_group_id' => $this->hg_device_group_id,
            'hg_status_id' => $this->hg_status_id,
            'rank' => $this->rank,
            'last_trigger_at' => $this->last_trigger_at,
        ]);

        $query->andFilterWhere(['like', 'behavior_name', $this->behavior_name])
            ->andFilterWhere(['like', 'last_trigger_status', $this->last_trigger_status])
            ->andFilterWhere(['like', 'metadata', $this->metadata]);

        return $dataProvider;
    }
}
