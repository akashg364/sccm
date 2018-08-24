<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\VariablesMapping;

/**
 * VariablesMappingSearch represents the model behind the search form of `backend\models\VariablesMapping`.
 */
class VariablesMappingSearch extends VariablesMapping
{   

    public $variable_name;
    public $company_name;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['variable_name','company_name'],'safe'],
            [['id', 'variable_id', 'customer_id'], 'integer'],
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
        $query = VariablesMapping::find();
        $query->joinWith(["variableMaster","customer"]);
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
            'variable_id' => $this->variable_id,
            'customer_id' => $this->customer_id,
        ]);

        $query->andFilterWhere(["LIKE","variable_name",$this->variable_name]);
        $query->andFilterWhere(["LIKE","company_name",$this->company_name]);

        return $dataProvider;
    }
}
