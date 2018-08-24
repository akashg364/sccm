<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Ipv6PoolAssignment;

/**
 * Ipv6PoolAssignmentSearch represents the model behind the search form of `backend\models\Ipv6PoolAssignment`.
 */
class Ipv6PoolAssignmentSearch extends Ipv6PoolAssignment
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'pool_id', 'service_instance_id', 'usable_ips', 'ip_count', 'device_id'], 'integer'],
            [['subnet', 'network_ip', 'broadcast_ip', 'is_full', 'created_date', 'updated_date', 'is_active'], 'safe'],
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
        $query = Ipv6PoolAssignment::find();

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
            'pool_id' => $this->pool_id,
            'service_instance_id' => $this->service_instance_id,
            'usable_ips' => $this->usable_ips,
            'ip_count' => $this->ip_count,
            'device_id' => $this->device_id,
            'created_date' => $this->created_date,
            'updated_date' => $this->updated_date,
        ]);

        $query->andFilterWhere(['like', 'subnet', $this->subnet])
            ->andFilterWhere(['like', 'network_ip', $this->network_ip])
            ->andFilterWhere(['like', 'broadcast_ip', $this->broadcast_ip])
            ->andFilterWhere(['like', 'is_full', $this->is_full])
            ->andFilterWhere(['like', 'is_active', $this->is_active]);

        return $dataProvider;
    }
}
