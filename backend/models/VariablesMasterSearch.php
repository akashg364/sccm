<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\VariablesMaster;

/**
 * VariablesMasterSearch represents the model behind the search form of `backend\models\VariablesMaster`.
 */
class VariablesMasterSearch extends VariablesMaster
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'data_type_id', 'created_by', 'updated_by'], 'integer'],
            [['type', 'value_type', 'variable_name', 'value1_label', 'value2_label', 'active_status', 'created_date', 'updated_date'], 'safe'],
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
        $query = VariablesMaster::find();

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
            'data_type_id' => $this->data_type_id,
            'created_by' => $this->created_by,
            'created_date' => $this->created_date,
            'updated_by' => $this->updated_by,
            'updated_date' => $this->updated_date,
        ]);

        $query->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'value_type', $this->value_type])
            ->andFilterWhere(['like', 'variable_name', $this->variable_name])
            ->andFilterWhere(['like', 'value1_label', $this->value1_label])
            ->andFilterWhere(['like', 'value2_label', $this->value2_label])
            ->andFilterWhere(['like', 'active_status', $this->active_status]);

        return $dataProvider;
    }
}
