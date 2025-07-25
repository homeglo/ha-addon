<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgGlozone;

/**
 * HgGlozoneSearch represents the model behind the search form of `app\models\HgGlozone`.
 */
class HgGlozoneSearch extends HgGlozone
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'hg_home_id'], 'integer'],
            [['name', 'display_name', 'bed_time_weekday_midnightmins', 'wake_time_weekday_midnightmins', 'bed_time_weekend_midnightmins', 'wake_time_weekend_midnightmins', 'metadata'], 'safe'],
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
        $query = HgGlozone::find();

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
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'bed_time_weekday_midnightmins', $this->bed_time_weekday_midnightmins])
            ->andFilterWhere(['like', 'wake_time_weekday_midnightmins', $this->wake_time_weekday_midnightmins])
            ->andFilterWhere(['like', 'bed_time_weekend_midnightmins', $this->bed_time_weekend_midnightmins])
            ->andFilterWhere(['like', 'wake_time_weekend_midnightmins', $this->wake_time_weekend_midnightmins])
            ->andFilterWhere(['like', 'metadata', $this->metadata]);

        return $dataProvider;
    }
}
