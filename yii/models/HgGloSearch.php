<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgGlo;

/**
 * HgGloSearch represents the model behind the search form of `app\models\HgGlo`.
 */
class HgGloSearch extends HgGlo
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'base_hg_glo_id', 'hg_status_id', 'hg_glozone_id', 'hg_hub_id', 'hg_version_id', 'rank', 'brightness'], 'integer'],
            [['name', 'hub_name', 'display_name', 'metadata'], 'safe'],
            [['hue_x', 'hue_y'], 'number'],
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
        $query = HgGlo::find();

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
            'hg_status_id' => $this->hg_status_id,
            'hg_glozone_id' => $this->hg_glozone_id,
            'hg_version_id' => $this->hg_version_id,
            'rank' => $this->rank,
            'hue_x' => $this->hue_x,
            'hue_y' => $this->hue_y,
            'brightness' => $this->brightness,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'hub_name', $this->hub_name])
            ->andFilterWhere(['like', 'display_name', $this->display_name])
            // ->andFilterWhere(['like', 'hue_ids', $this->hue_ids]) // REMOVED: No longer filtering by Hue IDs
            ->andFilterWhere(['like', 'metadata', $this->metadata]);

        if (!$this->hg_glozone_id) {
            $query->where('0=1');
        }

        return $dataProvider;
    }
}
