<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgProductLight;

/**
 * HgProductLightSearch represents the model behind the search form of `app\models\HgProductLight`.
 */
class HgProductLightSearch extends HgProductLight
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'maxlumen', 'rank', 'version'], 'integer'],
            [['display_name', 'manufacturer_name', 'productid', 'product_name', 'archetype', 'model_id', 'description', 'range', 'capability_json'], 'safe'],
            [['price'], 'number'],
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
        $query = HgProductLight::find();

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
            'maxlumen' => $this->maxlumen,
            'rank' => $this->rank,
            'version' => $this->version,
            'price' => $this->price,
        ]);

        $query->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'manufacturer_name', $this->manufacturer_name])
            ->andFilterWhere(['like', 'productid', $this->productid])
            ->andFilterWhere(['like', 'product_name', $this->product_name])
            ->andFilterWhere(['like', 'archetype', $this->archetype])
            ->andFilterWhere(['like', 'model_id', $this->model_id])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'range', $this->range])
            ->andFilterWhere(['like', 'capability_json', $this->capability_json]);

        return $dataProvider;
    }
}
