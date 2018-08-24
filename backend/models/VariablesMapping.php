<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_variables_mapping".
 *
 * @property int $id
 * @property int $variable_id
 * @property int $customer_id
 */
class VariablesMapping extends \yii\db\ActiveRecord
{

    public $value1;
    public $value2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_variables_mapping';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['variable_id', 'customer_id'], 'required'],
            [['variable_id', 'customer_id'], 'integer'],
            [["variable_id"],'unique','targetAttribute'=>["variable_id",'customer_id'],"message"=>"Variable already mapped to this customer"],
            [['value1','value2'],'safe'],
            ['value2', 'compare', 'compareAttribute' => 'value1', 'operator' => '>', 'type' => 'number','when'=>function($model){
                return !empty($model->value2);
            },'whenClient' => "function (attribute, value) { 
                if($('#variablesmapping-value2').val()!=''){
                    console.log($('#variablesmapping-value2').val(),$('#variablesmapping-value1').val());
                    console.log($('#variablesmapping-value2').val() > $('#variablesmapping-value1').val());
                    return !($('#variablesmapping-value2').val() > $('#variablesmapping-value1').val());
                }
                return true;
            }"],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'variable_id' => 'Variable',
            'customer_id' => 'Customer',
        ];
    }

    public function getVariableValues(){
        return $this->hasMany(VariablesValues::className(),["variable_mapping_id"=>"id"]);
    }

    public function getVariableMaster(){
        return $this->hasOne(VariablesMaster::className(),["id"=>"variable_id"]);
    }

    public function getCustomer(){
        return $this->hasOne(Customer::className(),["id"=>"customer_id"]);
    }


}
