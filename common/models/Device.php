<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tbl_device".
 *
 * @property int $id
 * @property string $hostname
 * @property string $sapid
 * @property string $loopback
 * @property string $created_date
 * @property string $updated_date
 * @property int $created_by
 * @property int $updated_by
 * @property string $neid
 */
class Device extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_device';
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
    
    public static function getDeviceList(){
        $data = self::find()
                ->where(["is_active"=>'1'])
                ->asArray()->all();
        return ArrayHelper::map($data, "id", "hostname");     
    }
}