<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ServiceMapping;

/**
 * ServiceMappingSearch represents the model behind the search form of `backend\models\ServiceMapping`.
 */
class ServiceMappingSearch extends ServiceMapping
{   

    public $service_name;
    public $sub_service_name;
    public $topology_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'service_id', 'sub_service_id', 'topology_id', 'created_by', 'updated_by'], 'integer'],
            [['created_date', 'updated_date', 'active_status','service_name','sub_service_name','topology_name'], 'safe'],
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
        $query = ServiceMapping::find();
        $query->joinWith([
            "service"=>function($q){return $q->alias("s");},
            "subService"=>function($q){return $q->alias("ss");},
            "topology"=>function($q){return $q->alias("t");},
        ]);
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
            'topology_id' => $this->topology_id,
            'created_by' => $this->created_by,
            'created_date' => $this->created_date,
            'updated_by' => $this->updated_by,
            'updated_date' => $this->updated_date,
        ]);

        $query->andFilterWhere(["LIKE","s.name",$this->service_name]);
        $query->andFilterWhere(["LIKE","ss.name",$this->sub_service_name]);
        $query->andFilterWhere(["LIKE","t.name",$this->topology_name]);

        $query->andFilterWhere(['like', 'active_status', $this->active_status]);

        return $dataProvider;
    }
}
