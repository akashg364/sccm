<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\GenericVariables;

/**
 * GenericVariablesSearch represents the model behind the search form about `app\models\GenericVariables`.
 */
class GenericVariablesSearch extends GenericVariables
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'customer_id'], 'integer'],
            [['variable_name', 'variable_value', 'status', 'created_date'], 'safe'],
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
        $query = GenericVariables::find();

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
            'customer_id' => $this->customer_id,
            'created_date' => $this->created_date,
        ]);

        $query->andFilterWhere(['like', 'variable_name', $this->variable_name])
            ->andFilterWhere(['like', 'variable_value', $this->variable_value])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
