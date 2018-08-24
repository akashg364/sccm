<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ApiLog;

/**
 * ApiLogSearch represents the model behind the search form of `app\models\ApiLog`.
 */
class ApiLogSearch extends ApiLog
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_by'], 'integer'],
            [['api_url', 'request_method', 'request', 'response', 'log_details', 'log_file_path', 'service_template_id', 'created_date'], 'safe'],
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
        $query = ApiLog::find();

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
            'created_date' => $this->created_date,
            'created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['like', 'api_url', $this->api_url])
            ->andFilterWhere(['like', 'request_method', $this->request_method])
            ->andFilterWhere(['like', 'request', $this->request])
            ->andFilterWhere(['like', 'response', $this->response])
            ->andFilterWhere(['like', 'log_details', $this->log_details])
            ->andFilterWhere(['like', 'log_file_path', $this->log_file_path])
            ->andFilterWhere(['like', 'service_template_id', $this->service_template_id]);

        return $dataProvider;
    }
}
