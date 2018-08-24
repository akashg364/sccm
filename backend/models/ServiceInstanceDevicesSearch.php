<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ServiceInstanceDevices;

/**
 * ServiceInstanceDevicesSearch represents the model behind the search form of `backend\models\ServiceInstanceDevices`.
 */
class ServiceInstanceDevicesSearch extends ServiceInstanceDevices
{
    public $template;
    public $service_instance;
    public $device;
    public $role_name;
    public $service_name;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'template_id', 'device_id', 'role_id', 'created_by', 'updated_by'], 'integer'],
            [['template', 'service_name','service_instance', 'device', 'role_name', 'service_instance_id', 'user_defined_data', 'system_defined_data', 'nso_payload', 'created_date', 'updated_date', 'is_active'], 'safe'],
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
        $query = ServiceInstanceDevices::find();
        $query->joinWith(["deviceRole", "serviceInstance", "device"]);

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
            'id'           => $this->id,
            'template_id'  => $this->template_id,
            'device_id'    => $this->device_id,
            'role_id'      => $this->role_id,
            'created_by'   => $this->created_by,
            'created_date' => $this->created_date,
            'updated_by'   => $this->updated_by,
            'updated_date' => $this->updated_date,
        ]);
        
        $query->andFilterWhere(['like', 'service_instance', $this->service_instance]);
        $query->andFilterWhere(['like', 'device', $this->device]);
        $query->andFilterWhere(['like', 'role_name', $this->role_name]);
        $query->andFilterWhere(['like', 'service_instance_id', $this->service_instance_id])
              ->andFilterWhere(['like', 'user_defined_data', $this->user_defined_data])
              ->andFilterWhere(['like', 'system_defined_data', $this->system_defined_data])
              ->andFilterWhere(['like', 'nso_payload', $this->nso_payload])
              ->andFilterWhere(['like', 'is_active', $this->is_active]);

        return $dataProvider;
    }
}