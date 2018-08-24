<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

class Device extends \common\models\Device
{
   
   public static $deviceTypes = [
                "CSS",
                "ECR",
                "AG1",
                "AG2",
                "AG3" 
    ];

   // public function getDeviceTypes() {
   //      return [
   //          "ESR" => "CSS",
   //          "ECR" => "ECR",
   //          "PAR" => "AG1",
   //          "AAR" => "AG2",
   //          "CCR" => "AG3",
   //      ];
   //  }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['hostname', 'sapid', 'loopback','router_type'], 'required'],
             [['created_date', 'updated_date','location'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['hostname', 'sapid', 'loopback'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'hostname' => 'Hostname',
            'sapid' => 'Sapid',
            'loopback' => 'Loopback',
            'created_date' => 'Created Date',
            'updated_date' => 'Updated Date',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }
    
    public static function getDeviceList() {
        $data = self::find()
                ->where(["is_active"=>'1'])
                ->asArray()->all();
        return ArrayHelper::map($data, "id", "hostname");     
    }
    
    public static function getDevice($id) {
        if (($device = Device::findOne($id)) !== null) {
            return $device['hostname'];
        }
    }
	
	public static function getDeviceByHostname($hostname){
		return self::find()->where(["hostname"=>$hostname])->asArray()->one();
	}
	
	public static function getDevicesByHostnames($hostname){
		return self::find()->where(["hostname"=>$hostname])->asArray()->all();
	}
}
