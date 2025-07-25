<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgGlozoneTimeBlock;

/**
 * HgGlozoneTimeBlockSearch represents the model behind the search form of `app\models\HgGlozoneTimeBlock`.
 */
class HgGlozoneTimeBlockSearch extends HgGlozoneTimeBlock
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'hg_glozone_id', 'default_hg_glo_id', 'hg_status_id', 'base_hg_glozone_time_block_id'], 'integer'],
            [['name', 'display_name', 'time_start_default_midnightmins', 'time_end_default_midnightmins', 'time_start_sun_midnightmins', 'time_end_sun_midnightmins', 'time_start_mon_midnightmins', 'time_end_mon_midnightmins', 'time_start_tue_midnightmins', 'time_end_tue_midnightmins', 'time_start_wed_midnightmins', 'time_end_wed_midnightmins', 'time_start_thu_midnightmins', 'time_end_thu_midnightmins', 'time_start_fri_midnightmins', 'time_end_fri_midnightmins', 'time_start_sat_midnightmins', 'time_end_sat_midnightmins', 'timezone', 'metadata'], 'safe'],
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
        $query = HgGlozoneTimeBlock::find();
        $query->with(['hgGlozone','defaultHgGlo','hgStatus']);

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
            'hg_glozone_id' => $this->hg_glozone_id,
            'default_hg_glo_id' => $this->default_hg_glo_id,
            'hg_status_id' => $this->hg_status_id,
            'base_hg_glozone_time_block_id' => $this->base_hg_glozone_time_block_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'time_start_default_midnightmins', $this->time_start_default_midnightmins])
            ->andFilterWhere(['like', 'time_start_sun_midnightmins', $this->time_start_sun_midnightmins])
            ->andFilterWhere(['like', 'time_end_sun_midnightmins', $this->time_end_sun_midnightmins])
            ->andFilterWhere(['like', 'time_start_mon_midnightmins', $this->time_start_mon_midnightmins])
            ->andFilterWhere(['like', 'time_end_mon_midnightmins', $this->time_end_mon_midnightmins])
            ->andFilterWhere(['like', 'time_start_tue_midnightmins', $this->time_start_tue_midnightmins])
            ->andFilterWhere(['like', 'time_end_tue_midnightmins', $this->time_end_tue_midnightmins])
            ->andFilterWhere(['like', 'time_start_wed_midnightmins', $this->time_start_wed_midnightmins])
            ->andFilterWhere(['like', 'time_end_wed_midnightmins', $this->time_end_wed_midnightmins])
            ->andFilterWhere(['like', 'time_start_thu_midnightmins', $this->time_start_thu_midnightmins])
            ->andFilterWhere(['like', 'time_end_thu_midnightmins', $this->time_end_thu_midnightmins])
            ->andFilterWhere(['like', 'time_start_fri_midnightmins', $this->time_start_fri_midnightmins])
            ->andFilterWhere(['like', 'time_end_fri_midnightmins', $this->time_end_fri_midnightmins])
            ->andFilterWhere(['like', 'time_start_sat_midnightmins', $this->time_start_sat_midnightmins])
            ->andFilterWhere(['like', 'time_end_sat_midnightmins', $this->time_end_sat_midnightmins])
            ->andFilterWhere(['like', 'timezone', $this->timezone])
            ->andFilterWhere(['like', 'metadata', $this->metadata]);

        return $dataProvider;
    }
}
