<?php

namespace backend\models;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tbl_sub_service_filters".
 *
 * @property int $id
 * @property string $type
 * @property string $param
 * @property int $value
 */
class SubServiceFilters extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
	
   public static function tableName()
    {
        return 'tbl_sub_service_filters';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'param', 'value'], 'required'],
            [['value'], 'integer'],
            [['type', 'param'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'param' => 'Param',
            'value' => 'Value',
        ];
    }
	
	/*
	Author : Jitendra Gamit
	Date : 10thAugust, 2018
	Purpose : Get service filter
	***/
	public static function getSubServiceFilter() {
        
		$sql = "SELECT type FROM tbl_sub_service_filters GROUP by type";			
		$results = \Yii::$app->db->createCommand($sql)->queryAll();
	    
		$array_data = array();
		
		foreach ($results as $key => $value) {
			$type = $value['type'];
		    $data = self::find()->where(["type"=>$type])->asArray()->All(); 
			$data1[$type] = ArrayHelper::map($data,"param","param");                
		}
		
		return $data1;
	} // getSubServiceFilter()
	
}
