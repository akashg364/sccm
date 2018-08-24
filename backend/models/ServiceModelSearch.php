<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ServiceModel;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\User;

/**
 * ServiceModelSearch represents the model behind the search form of `backend\models\ServiceModel`.
 */
class ServiceModelSearch extends ServiceModel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'service_id', 'sub_service_id', 'topology_id', 'created_by', 'updated_by'], 'integer'],
            [['name', 'description', 'created_on', 'updated_on','service_name','sub_service_name','topology_name'], 'safe'],
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
        $query = ServiceModel::find();
        $query->alias('sm');
        $query->joinWith([
            'service'=>function($q){return $q->alias('s');},
            'subService'=>function($q){return $q->alias('ss');},
            'topology'=>function($q){return $q->alias('t');},
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
            'sm.id' => $this->id,
            'service_id' => $this->service_id,
            'sub_service_id' => $this->sub_service_id,
            'topology_id' => $this->topology_id,
            'created_by' => $this->created_by,
            'created_on' => $this->created_on,
            'updated_by' => $this->updated_by,
            'updated_on' => $this->updated_on,
            'is_deleted'=>0,
        ]);

        $query->andFilterWhere(['LIKE','s.name',$this->service_name]);
        $query->andFilterWhere(['LIKE','ss.name',$this->sub_service_name]);
        $query->andFilterWhere(['LIKE','t.topology',$this->topology_name]);

        $query->andFilterWhere(['like', 'sm.name', $this->name])
            ->andFilterWhere(['like', 'sm.description', $this->description]);

          //  qpe( $query);
        return $dataProvider;
    }
    
    public function getDeleteActionLink($data){
       $user = User::find()->where(['id' => Yii::$app->user->id])->one();
       if($data->is_deleted === 2){
           if ((trim($user['user_type']) == 'admin' || empty($user['user_type']))){
               return '<strong>Deleted</strong>'.' ( '.Html::a(Html::encode("Restore"), Url::toRoute(['/service-model/updatestatus', 'id' => $data->id, 'status' => 0]), ['data' => [
                                'confirm' => 'Are you sure you want to Restore this Deletation?',
                                'method' => 'post',
                            ]]).' )';
           }else{
               return '<strong>Deleted</strong>';
           }
           
       }else if($data->is_deleted === 1){
           if ((trim($user['user_type']) == 'admin' || empty($user['user_type']))){
               return Html::a(Html::encode("Accept"), Url::toRoute(['/service-model/updatestatus', 'id' => $data->id, 'status' => 2]), ['data' => [
                                'confirm' => 'Are you sure you want to Accept this Deletation?',
                                'method' => 'post',
                            ],'class' => 'btn btn-danger','title'=>'Want to accept the deletation?']).' '.Html::a(Html::encode("Reject"), Url::toRoute(['/service-model/updatestatus', 'id' => $data->id, 'status' => 0]), ['data' => [
                                'confirm' => 'Are you sure you want to Reject this Deletation?',
                                'method' => 'post',
                            ], 'class' => 'btn btn-success','title'=>'Want to reject the deletation?']);
           }else{
               return '<strong>Waiting For Deletation</strong>';
           }
       }else{
           return '';
       }
        
    }
}
