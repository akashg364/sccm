<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\AuthAssignment;
use yii\helpers\ArrayHelper;
/**
 * PrivilegeSearch represents the model behind the search form of `app\models\Privilege`.
 */
class AuthAssignmentSearch extends AuthAssignment
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_name'], 'safe'],           
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
        $query = AuthAssignment::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
			//'pageSize' => 2,
			'pageSize'=> isset(Yii::$app->params['defaultPageSize']) ? Yii::$app->params['defaultPageSize'] : 5,
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
            'item_name' => $this->item_name,
        ]);      	
        return $dataProvider;
    }
    
     public function getUserRoles(){        
        $query =  ArrayHelper::map(AuthAssignment::findBySql('select distinct(item_name) from auth_assignment')
                ->all(),'item_name');        
          //$dataProvider = new ActiveDataProvider([
            //'query' => $query,            
       // ]);
        
          return $query;
        
            }
}
