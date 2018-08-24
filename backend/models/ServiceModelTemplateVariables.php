<?php

namespace backend\models;

use Yii;
use yii\db\Query;
/**
 * This is the model class for table "tbl_service_model_template_variables".
 *
 * @property int $id
 * @property int $template_id
 * @property int $resource_manager_id
 */
class ServiceModelTemplateVariables extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_service_model_template_variables';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'template_id', 'resource_manager_id'], 'required'],
            [['id', 'template_id', 'resource_manager_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'template_id' => 'Template ID',
            'resource_manager_id' => 'Resource Manager ID',
        ];
    }


    public function getResourceManager(){
        return $this->hasOne(VariablesMaster::className(),["id"=>"resource_manager_id"]);
    }
	
	public function getVariable(){
        return $this->hasOne(VariablesMaster::className(),["id"=>"resource_manager_id"]);
    }
    
    public static function getIpAddressMappedVariables($service_template_id){
        $query  =new Query;
        $query->select("`tbl_variables_master`.`variable_name` as 'id',`tbl_variables_master`.`variable_name` as 'variable_name',`tbl_variables_master`.`variable_name` as 'variable_name'")
                ->from('tbl_service_model_template_variables')
                ->join('inner join', 'tbl_variables_master', 'tbl_service_model_template_variables.resource_manager_id=tbl_variables_master.id')
                ->join('inner join', 'tbl_data_type', 'tbl_variables_master.data_type_id=tbl_data_type.id');
        $query->where(['tbl_variables_master.id' =>  $service_template_id,'tbl_data_type.data_type' =>  ['Ipv6','Ipv4']]);
        $data   =   $query->all();
        return $data;                
    }
}
