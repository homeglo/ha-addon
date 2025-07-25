<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgProductSensor;

/**
 * HgProductSensorSearch represents the model behind the search form of `app\models\HgProductSensor`.
 */
class HgProductSensorSearch extends HgProductSensor
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'rank', 'button_count'], 'integer'],
            [['display_name', 'manufacturer_name', 'product_name', 'type_name', 'archetype', 'model_id', 'description', 'action_map_type'], 'safe'],
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
        $query = HgProductSensor::find();

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
            'rank' => $this->rank,
            'button_count' => $this->button_count,
        ]);

        $query->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'manufacturer_name', $this->manufacturer_name])
            ->andFilterWhere(['like', 'product_name', $this->product_name])
            ->andFilterWhere(['like', 'type_name', $this->type_name])
            ->andFilterWhere(['like', 'archetype', $this->archetype])
            ->andFilterWhere(['like', 'model_id', $this->model_id])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'action_map_type', $this->action_map_type]);

        return $dataProvider;
    }
}
