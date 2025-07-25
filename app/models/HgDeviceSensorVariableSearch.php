<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\HgDeviceSensorVariable;

/**
 * HgDeviceSensorVariableSearch represents the model behind the search form of `app\models\HgDeviceSensorVariable`.
 */
class HgDeviceSensorVariableSearch extends HgDeviceSensorVariable
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'hg_device_sensor_id', 'variable_name', 'value', 'hg_status_id', 'json_data'], 'integer'],
            [['display_name', 'description'], 'safe'],
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
        $query = HgDeviceSensorVariable::find();

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
            'hg_device_sensor_id' => $this->hg_device_sensor_id,
            'variable_name' => $this->variable_name,
            'value' => $this->value,
            'hg_status_id' => $this->hg_status_id,
            'json_data' => $this->json_data,
        ]);

        $query->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
