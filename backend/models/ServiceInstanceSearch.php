<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ServiceInstance;

/**
 * ServiceInstanceSearch represents the model behind the search form of `backend\models\ServiceInstance`.
 */
class ServiceInstanceSearch extends ServiceInstance
{
    public $company_name;
    public $service_model;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'service_model_id', 'endpoints', 'created_by', 'updated_by'], 'integer'],
            [['service_order_id', 'scheduled_status', 'scheduled_date', 'is_active', 'created_on', 'updated_on','company_name','service_model','user_defined_data', 'system_defined_data', 'device_list'], 'safe'],
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
        \Yii::$container->set('yii\data\Sort',['defaultOrder' => ['id'=>SORT_DESC]]);    
        $query = ServiceInstance::find();

        $query->joinWith(["customer", "serviceModel"]);
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
            'id'               => $this->id,
            'service_order_id' => $this->service_order_id,
            'customer_id'      => $this->customer_id,
            'service_model_id' => $this->service_model_id,
            'scheduled_date'   => $this->scheduled_date,
        ]);

        $query->andFilterWhere(['like', 'company_name',$this->company_name]);
        $query->andFilterWhere(['like', 'service_model',$this->service_model]);
        $query->andFilterWhere(['like', 'device_list',$this->device_list]);
        $query->andFilterWhere(['like', 'scheduled_status', $this->scheduled_status])
              ->andFilterWhere(['like', 'is_active', $this->is_active]);

        return $dataProvider;
    }
}
