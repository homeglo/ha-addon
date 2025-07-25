<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgHubActionMap;

/**
 * HgHubActionMapSearch represents the model behind the search form of `app\models\HgHubActionMap`.
 */
class HgHubActionMapSearch extends HgHubActionMap
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'base_hg_hub_action_map_id', 'hg_status_id'], 'integer'],
            [['name', 'display_name', 'map_image_url', 'hg_product_sensor_map_type', 'preserve_hue_buttons', 'metadata'], 'safe'],
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
        $query = HgHubActionMap::find();

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
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'map_image_url', $this->map_image_url])
            ->andFilterWhere(['like', 'hg_product_sensor_map_type', $this->hg_product_sensor_map_type])
            ->andFilterWhere(['like', 'preserve_hue_buttons', $this->preserve_hue_buttons])
            ->andFilterWhere(['like', 'metadata', $this->metadata])
            ->andWhere(['IS','base_hg_hub_action_map_id',null]);

        return $dataProvider;
    }
}
