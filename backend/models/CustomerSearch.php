<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Customer;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\User;

/**
 * CustomerSearch represents the model behind the search form of `app\models\Customer`.
 */
class CustomerSearch extends Customer {

    public $providerName;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['provider_id','customer_id', 'company_name', 'email_id', 'description', 'email_id', 'mobile_number', 'address', 'city', 'state', 'country'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
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
        if (trim($user['user_type']) == 'superadmin' || empty($user['user_type']) || trim($user['user_type']) == 'superuser') {
//            $query = Customer::find()->select('tbl_customers.*,tbl_providers.company_name as providerName')
//                            ->leftJoin('user', '`user`.`reference_id` = `tbl_customers`.`provider_id`')->leftJoin('tbl_providers', '`tbl_customers`.`provider_id` = `tbl_providers`.`id`');
            $query = Customer::find();
        } else {
            $query = Customer::find()->select('tbl_customers.*')
                            ->leftJoin('user', '`user`.`reference_id` = `tbl_customers`.`provider_id`')->where(['`tbl_customers`.`provider_id`' => $user['reference_id']]);
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                //'pageSize' => 2,
                'pageSize' => isset(Yii::$app->params['defaultPageSize']) ? Yii::$app->params['defaultPageSize'] : 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'tbl_customers.company_name' => $this->company_name,
        ]);
        $query->andFilterWhere(['like', 'tbl_customers.active_status', $this->active_status]);
        
        return $dataProvider;
    }

    public function getStatusLink($data) {
        if ($data->approve_status == 0) {
            $user = User::find()->where(['id' => Yii::$app->user->id])->one();

            if ((trim($user['user_type']) == 'superadmin' || empty($user['user_type'])) || ($user['reference_id'] == $data->provider_id && $user['user_type'] == 'provideradmin')) {
                return Html::a(Html::encode("Approve"), Url::toRoute(['/customer/updatestatus', 'id' => $data->id, 'status' => 1]), ['data' => [
                                'confirm' => 'Are you sure you want to Approve this item?',
                                'method' => 'post',
                            ], 'class' => 'btn btn-success']) . ' ' . Html::a(Html::encode("Reject"), Url::toRoute(['/customer/updatestatus', 'id' => $data->id, 'status' => 2]), ['data' => [
                                'confirm' => 'Are you sure you want to Reject this item?',
                                'method' => 'post',
                            ], 'class' => 'btn btn-danger']);
            } else {
                return '<strong>Request For Approve</strong>';
            }
        } else {
            $username = '';
            $user = User::find()->where(['id' => Yii::$app->user->id])->one();
            if (!empty($data->acceptance_action_taken_by)) {
                $userData = User::find()->where(['id' => $data->acceptance_action_taken_by])->one();
                $username = ' - ' . $userData['username'];
            }
            if ($data->approve_status == 1) {
//                return '<strong>On Board</strong>' . ' ' . $username;
                return '<strong>On Board</strong>';
            } else if ($data->approve_status == 2) {
                    if ((trim($user['user_type']) == 'superuser' || empty($user['user_type'])) || ($user['reference_id'] == $data->provider_id && $user['user_type'] == 'provideruser')) {
                    return '<strong>Rejected</strong>   '.Html::a(Html::encode("Request for Approval"), Url::toRoute(['/customer/updatestatus', 'id' => $data->id, 'status' => 0]), ['data' => [
                                    'confirm' => 'Are you sure you want to sent request to Approve this item?',
                                    'method' => 'post',
                                ], 'class' => 'btn btn-success']);
                }else{
    //                return '<strong>Rejected</strong>' . ' ' . $username;
                    return '<strong>Rejected</strong>';
                }
            }
        }
    }

    public function getActiveInactiveLink($data) {
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        $username = '';
        if (!empty($data->status_action_taken_by)) {
            $userData = User::find()->where(['id' => $data->status_action_taken_by])->one();
            $username = ' - ' . $userData['username'];
        } else {
            $userData = User::find()->where(['id' => $data->created_by])->one();
            $username = ' - ' . $userData['username'];
        }
        if ($data->active_status == 0 && ($data->approve_status == 0 || $data->approve_status == 2)) {
            return '<strong>NA</strong>';
        } else if ($data->active_status == 1 && $data->approve_status != 0) {
            if ((trim($user['user_type']) == 'superuser' || empty($user['user_type'])) || ($user['reference_id'] == $data->provider_id && $user['user_type'] == 'provideruser')) {
                return '<strong>Active</strong>' . ' (' . Html::a(Html::encode("Request for Inactive"), Url::toRoute(['/customer/updateactiveinactive', 'id' => $data->id, 'status' => 2]), ['data' => [
                                'confirm' => 'Are you sure you want to Inactive this item?',
                                'method' => 'post',
                            ], 'class' => 'btn']) . ' )';
            } else {
//                return '<strong>Active</strong>' . ' ' . $username;
                return '<strong>Active</strong>';
            }
        } else if ($data->active_status == 1 && $data->approve_status ===0) {            
                return '<strong>Request For Active</strong>'.' '.$username;            
        //}else if ($data->active_status == 2 && $data->approve_status !=0) {
                }else if ($data->active_status == 2 && $data->approve_status == 1) {
            if ((trim($user['user_type']) == 'superuser' || empty($user['user_type'])) || ($user['reference_id'] == $data->provider_id && $user['user_type'] == 'provideruser')) {
//                return '<strong>Request for Inactive</strong>' . ' ( ' .Html::a(Html::encode("Active"), Url::toRoute(['/customer/updateactiveinactive', 'id' => $data->id, 'status' => 1]), ['data' => [
//                                'confirm' => 'Are you sure you want to Active this item?',
//                                'method' => 'post',
//                            ], 'class' => 'btn ']).' )';
                return '<strong>Request for Inactive</strong>';
            } else {
//                return '<strong>Inactive</strong>'.' '.$username;
                return '<strong>Active</strong>' . ' (Inactivate - ' .Html::a(Html::encode("Yes "), Url::toRoute(['/customer/updateactiveinactive', 'id' => $data->id, 'status' => 0]), ['data' => [
                                'confirm' => 'Are you sure you want to Inactive Active this item?',
                                'method' => 'post',
                            ], 'class' => ' ']).Html::a(Html::encode(" No"), Url::toRoute(['/customer/updateactiveinactive', 'id' => $data->id, 'status' => 1]), ['data' => [
                                'confirm' => 'Are you sure you want to Inactive Active this item?',
                                'method' => 'post',
                            ], 'class' => '']).' )';
            }
        }else if ($data->active_status == 2 && $data->approve_status ===0) {            
//                return '<strong>Request For Inactive</strong>'.' '.$username;    
            return '<strong>Request For Inactive</strong>';
        }else if ($data->active_status == 0 && $data->approve_status ===1) {   
            if ((trim($user['user_type']) == 'superuser' || empty($user['user_type'])) || ($user['reference_id'] == $data->provider_id && $user['user_type'] == 'provideruser')) {
                return '<strong>Inactive</strong>' . ' (' . Html::a(Html::encode("Request for Active"), Url::toRoute(['/customer/updateactiveinactive', 'id' => $data->id, 'status' => 2]), ['data' => [
                                'confirm' => 'Are you sure you want to Request to active this item?',
                                'method' => 'post',
                            ], 'class' => 'btn']) . ' )';
            } else {
//                return '<strong>Active</strong>' . ' ' . $username;
                return '<strong>Inactive</strong>';
            }
        }
    }

    public function getActiveCustomersByProviderID($provider_id) {
        $model = Customer::find()
                ->where(['active_status' => 1])->asArray()
                ->all();
        return $model;
    }

}
