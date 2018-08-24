<?php
namespace api\models;

use yii\db\ActiveRecord;

class Subservicefilters extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%tbl_sub_service_filters}}';
    }

    public function rules()
    {
        return [
            [['type', 'param','value'], 'required'],
            ['type', 'string', 'max' => 255],
            ['param', 'string']
        ];
    }
	
	public static function getFilterParam($type,$value){
		$model = self::find()
		->where(["type"=>$type,"value"=>$value])
		->limit(1)->one();
		return $model?$model->param:'';
	}
}