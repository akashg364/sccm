<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_service_model_variables".
 *
 * @property int $id
 * @property int $service_model_id
 * @property int $resource_manager_id
 */
class ServiceModelVariables extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_service_model_variables';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_model_id', 'resource_manager_id'], 'required'],
            [['service_model_id', 'resource_manager_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'service_model_id' => 'Service Model ID',
            'resource_manager_id' => 'Resource Manager ID',
        ];
    }


    /*Relation*/
    // public function getVariable(){
    //     return $this->hasOne(ResourceManager::className(),['id'=>'resource_manager_id']); 
    // }
    public function getVariable(){
        return $this->hasOne(VariablesMaster::className(),['id'=>'resource_manager_id']); 
    }

    public function batchInsert($rows) {
        $attributes = array_keys($rows[0]);   
        $res= Yii::$app->db->createCommand()
                ->batchInsert(ServiceModelVariables::tableName(), $attributes, $rows)
                ->execute();
        return $res;
    }
}
