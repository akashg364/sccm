<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Ipv6Pool;

/**
 * Ipv6PoolSearch represents the model behind the search form of `backend\models\Ipv6Pool`.
 */
class Ipv6PoolSearch extends Ipv6Pool
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_by', 'updated_by'], 'integer'],
            [['pool', 'subnet', 'created_date', 'updated_date', 'is_full', 'is_active'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
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
        $query = Ipv6Pool::find();

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
            'created_by'   => $this->created_by,
            'created_date' => $this->created_date,
            'updated_by'   => $this->updated_by,
            'updated_date' => $this->updated_date,
        ]);

        $query->andFilterWhere(['like', 'pool', $this->pool])
              ->andFilterWhere(['like', 'subnet', $this->subnet])
              ->andFilterWhere(['like', 'is_full', $this->is_full])
              ->andFilterWhere(['like', 'is_active', $this->is_active]);

        return $dataProvider;
    }
}
