<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\L2Testing;

/**
 * L2TestingSearch represents the model behind the search form of `backend\models\L2Testing`.
 */
class L2TestingSearch extends L2Testing
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'service_id', 'sub_service_id', 'network_engineer', 'status'], 'integer'],
            [['bangalore_lab_status', 'bangalore_datetime', 'reliance_lab_status', 'reliance_datetime'], 'safe'],
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
        $query = L2Testing::find();

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
            'service_id' => $this->service_id,
            'sub_service_id' => $this->sub_service_id,
            'network_engineer' => $this->network_engineer,
            'bangalore_datetime' => $this->bangalore_datetime,
            'reliance_datetime' => $this->reliance_datetime,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'bangalore_lab_status', $this->bangalore_lab_status])
            ->andFilterWhere(['like', 'reliance_lab_status', $this->reliance_lab_status]);

        return $dataProvider;
    }
}
