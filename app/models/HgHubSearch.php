<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgHub;

/**
 * HgHubSearch represents the model behind the search form of `app\models\HgHub`.
 */
class HgHubSearch extends HgHub
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'hg_home_id', 'hg_status_id', 'token_expires_at'], 'integer'],
            [['display_name', 'access_token', 'bearer_token', 'refresh_token', 'hue_email', 'hue_random', 'notes', 'metadata'], 'safe'],
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
        $query = HgHub::find();

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
            'hg_home_id' => $this->hg_home_id,
            'hg_status_id' => $this->hg_status_id,
            'token_expires_at' => $this->token_expires_at,
        ]);

        $query->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'access_token', $this->access_token])
            ->andFilterWhere(['like', 'bearer_token', $this->bearer_token])
            ->andFilterWhere(['like', 'refresh_token', $this->refresh_token])
            ->andFilterWhere(['like', 'hue_email', $this->hue_email])
            ->andFilterWhere(['like', 'hue_random', $this->hue_random])
            ->andFilterWhere(['like', 'notes', $this->notes])
            ->andFilterWhere(['like', 'metadata', $this->metadata]);

        return $dataProvider;
    }
}
