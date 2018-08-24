<?php
/***
Base Model Class
**/
namespace common\models;

use Yii;

Class BaseModel extends \yii\db\ActiveRecord {

	// Relation
	public function getCreatedBy(){
		return $this->hasOne(User::className(),["id"=>"created_by"]);
	} 

	// Relation
	public function getUpdatedBy(){
		return $this->hasOne(User::className(),["id"=>"updated_by"]);
	} 


	public function getCreatedByUser(){
		 $model = $this->hasOne(User::className(),["id"=>"created_by"])->one();
		 return $model?$model->username:"<Not Set>";
	}	

	public function getUpdatedByUser(){
		 $model = $this->hasOne(User::className(),["id"=>"updated_by"])->one();
		 return $model?$model->username:"<Not Set>";
	}

	public function setCreatedByUpdatedByUser(){
		$this->created_by = $this->getCreatedByUser();
        $this->updated_by = $this->getUpdatedByUser();
	}

	//@Desc : sort by id desc if table has id column 
	protected function sortByIdDesc(){
		if($this->hasAttribute('id')){
			\Yii::$container->set('yii\data\Sort',['defaultOrder' => ['id'=>SORT_DESC]]);
		}else{
			\Yii::$container->set('yii\data\Sort',[]);
		}
	}
}	