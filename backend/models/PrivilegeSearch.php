<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Privilege;

/**
 * PrivilegeSearch represents the model behind the search form of `app\models\Privilege`.
 */
class PrivilegeSearch extends Privilege
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'safe'],           
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
        $query = Privilege::find();

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
            'name' => $this->name,
        ]);      	
        return $dataProvider;
    }
    
     public function getDropdownPrivileges(){        
        $query =  Privilege::findBySql('select id,name from tbl_privileges')
                ->asArray()->all();        
          $dataProvider = new ActiveDataProvider([
            'query' => $query,            
        ]);
        
          return $dataProvider;
        
            }
}
