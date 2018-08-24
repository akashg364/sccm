<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_variables_master".
 *
 * @property int $id
 * @property string $type system/user
 * @property string $value_type range/single
 * @property int $data_type_id
 * @property string $variable_name
 * @property string $value1_label
 * @property string $value2_label
 * @property int $active_status
 * @property int $created_by
 * @property string $created_date
 * @property int $updated_by
 * @property string $updated_date
 */
class VariablesMaster extends \common\models\BaseModel
{
    public static $variableTypes = [
            "user"  =>"User Defined",
            "system"=>'System Defined'
    ];

    public static $valueTypes = [
        "range" =>"Range",
        "single_value"=>"Signle Value",
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_variables_master';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'value_type', 'data_type_id', 'variable_name','description'], 'required'],
            [['value1_label', 'value2_label'],'required','when'=>function($model){
               return $model->value_type=='range'; 
            },'whenClient' => "function (attribute, value) { return $('#variablesmaster-value_type').val()=='range';}"
            ],
            ['variable_name','unique'],
            [['data_type_id', 'created_by', 'updated_by'], 'integer'],
            [['created_date', 'updated_date'], 'safe'],
            [['type'], 'string', 'max' => 6],
            [['value_type'], 'string', 'max' => 20],
            [['variable_name', 'value1_label', 'value2_label'], 'string', 'max' => 50],
            [['active_status'], 'string', 'max' => 1],
        ];
    }


    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\BlameableBehavior::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Variable Type',
            'value_type' => 'Value Type',
            'data_type_id' => 'Data Type',
            'variable_name' => 'Variable Name',
            'value1_label' => 'Value1 Label',
            'value2_label' => 'Value2 Label',
            'active_status' => 'Active Status',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
            'updated_by' => 'Updated By',
            'updated_date' => 'Updated Date',
        ];
    }   

    /*Relation with Data Type*/
    public function getDataType(){
        return $this->hasOne(DataType::className(),["id"=>"data_type_id"]);
    }

    public static function getVariablesList($type=''){
        $ret["user"] = [];
        $ret["system"] = []; 
        $query = self::find()->where(["active_status"=>1]);
                if($type){
                    $query->andWhere(["type"=>$type]);
                }
        $data = $query->asArray()->all();
        foreach ($data as $key => $d) {
            $ret[$d["type"]][$d["id"]] = $d["variable_name"];
        }

        return ($type)?$ret[$type]:$ret;
    }


    public static function getSystemVariablesForForm(){

        $ret = ["list" =>[]];
        $data = self::find()
                ->alias("vm")
                ->joinWith(["dataType"])
                ->select(["vm.id","type","value_type","data_type_id","variable_name","value1_label","value2_label","description","data_type"])
                ->where([
				"active_status"=>1,
				//"type"=>'system'
				])
                ->asArray()->all();
              
        $mapping = [];        
        foreach ($data as $key => $d) {
            $ret["list"][$d["id"]] = $d["variable_name"]."- ".ucfirst($d["type"]);
            $mapping[$d["id"]] = $d;
        }    
        $ret["mapping"] = $mapping;
        return $ret;
    }
}
