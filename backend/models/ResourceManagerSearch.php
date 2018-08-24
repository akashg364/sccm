<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ResourceManager;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\User;
use backend\models\Customer;

/**
 * ResourceManagerSearch represents the model behind the search form of `backend\models\ResourceManager`.
 */
class ResourceManagerSearch extends ResourceManager
{
    public $company_name;
    public $data_type;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'data_type_id', 'created_by', 'updated_by'], 'integer'],
            [['type', 'value_type', 'variable_name', 'variable_value', 'created_date', 'updated_date','company_name','data_type'], 'safe'],
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
    public function search($params) {
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        if (strstr($user->user_type, 'super') || empty($user['user_type'])) {
            $query = ResourceManager::find(); 
            $query->joinWith(["customer", "dataType"]);
        } else {
            $query = ResourceManager::find()->select('tbl_customers.*,tbl_resource_manager.*,tbl_data_type.*')
                    ->leftJoin('tbl_customers', '`tbl_resource_manager`.`customer_id` = `tbl_customers`.`id`')
                    ->leftJoin('tbl_data_type', '`tbl_data_type`.`id` = `tbl_resource_manager`.`data_type_id`')
                    ->leftJoin('user', '`user`.`reference_id` = `tbl_customers`.`provider_id`')
                    ->where(['`tbl_customers`.`provider_id`' => $user['reference_id']]);
        }

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
            'data_type_id' => $this->data_type_id,
            'created_by' => $this->created_by,
            'created_date' => $this->created_date,
            'updated_by' => $this->updated_by,
            'updated_date' => $this->updated_date,
        ]);
        $query->andFilterWhere(['like','company_name',$this->company_name]);
         $query->andFilterWhere(['like','data_type',$this->data_type]);
        $query->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'value_type', $this->value_type])
            ->andFilterWhere(['like', 'variable_name', $this->variable_name])
            ->andFilterWhere(['like', 'variable_value', $this->variable_value]);
        return $dataProvider;
    }

    public function getStatusLink($data) {
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        $provider = Customer::find()->where(['id' => $data->customer_id])->one();
        if ($data->approve_status == 0) {
            if ((trim($user['user_type']) == 'admin' || empty($user['user_type'])) || $user['reference_id'] == $provider['provider_id']) {
                return Html::a('<i class="fa fa-check"></i>', Url::toRoute(['/resource-manager/updatestatus', 'id' => $data->id, 'status' => 1]), ['data' => [
                                'confirm' => 'Are you sure you want to Accept this item?',
                                'method' => 'post',
                            ], 'class' => 'btn btn-success']) . ' ' . Html::a('<i class="fa fa-times"></i>', Url::toRoute(['/resource-manager/updatestatus', 'id' => $data->id, 'status' => 2]), ['data' => [
                                'confirm' => 'Are you sure you want to Reject this item?',
                                'method' => 'post',
                            ], 'class' => 'btn btn-danger']);
            } else {
                return '<strong>No Action Taken</strong>';
            }
        } else {
            if ($data->approve_status == 1) {
                return '<strong>Accepted</strong>';
            } else if ($data->approve_status == 2) {
                return '<strong>Rejected</strong>';
            }
        }
    }

    public function getActiveInactiveLink($data) {
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        $provider = Customer::find()->where(['id' => $data->customer_id])->one();
        if ($data->active_status == 0) {
            if ((trim($user['user_type']) == 'admin' || empty($user['user_type'])) || $user['reference_id'] == $provider['provider_id']) {
                return Html::a(Html::encode("Active"), Url::toRoute(['/resource-manager/updateactiveinactive', 'id' => $data->id, 'status' => 1]), ['data' => [
                                'confirm' => 'Are you sure you want to Active this item?',
                                'method' => 'post',
                            ], 'class' => 'btn btn-success']);
            } else {
                return '<strong>No Action Taken</strong>';
            }
        } else {
            if ((trim($user['user_type']) == 'admin' || empty($user['user_type'])) || $user['reference_id'] == $provider['provider_id']) {
                return Html::a(Html::encode("Inactive"), Url::toRoute(['/resource-manager/updateactiveinactive', 'id' => $data->id, 'status' => 0]), ['data' => [
                                'confirm' => 'Are you sure you want to Inactive this item?',
                                'method' => 'post',
                            ], 'class' => 'btn btn-danger']);
            } else {
                return '<strong>No Action Taken</strong>';
            }
        }
    }

}