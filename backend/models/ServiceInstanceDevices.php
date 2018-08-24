<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_service_instance_devices".
 *
 * @property int $id
 * @property string $service_instance_id
 * @property int $template_id
 * @property int $device_id
 * @property int $role_id
 * @property string $user_defined_data
 * @property string $system_defined_data
 * @property string $nso_payload
 * @property int $created_by
 * @property string $created_date
 * @property int $updated_by
 * @property string $updated_date
 * @property int $is_active
 */
class ServiceInstanceDevices extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_service_instance_devices';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
          //[['service_instance_id', 'template_id', 'device_id', 'role_id', 'created_by', 'updated_by'], 'required'],
            [['service_instance_id', 'device_id', 'role_id'], 'required'],
            [['template_id', 'device_id', 'role_id', 'created_by', 'updated_by'], 'integer'],
            [['user_defined_data', 'system_defined_data', 'nso_payload'], 'string'],
            [['created_date', 'updated_date'], 'safe'],
          //[['service_instance_id'], 'string', 'max' => 255],
            [['is_active'], 'string', 'max' => 1],
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
            'id'                  => 'ID',
            'service_instance_id' => 'Instance',
            'template_id'         => 'Template',
            'device_id'           => 'Device',
            'role_id'             => 'Role',
            'user_defined_data'   => 'User Defined Data',
            'system_defined_data' => 'System Defined Data',
            'nso_payload'         => 'Nso Payload',
            'created_by'          => 'Created By',
            'created_date'        => 'Created Date',
            'updated_by'          => 'Updated By',
            'updated_date'        => 'Updated Date',
            'is_active'           => 'Is Active',
        ];
    }
    
    /*Relation with Customer*/
    public function getDeviceRole() {
        return $this->hasOne(DeviceRole::className(),["id"=>"role_id"]);
    }
    
    /*Relation with Service Model*/
    public function getDevice() {
        return $this->hasOne(Device::className(),["id"=>"device_id"]);
    }
    
    /*Relation with Service Model*/
    public function getServiceInstance() {
        return $this->hasOne(ServiceInstance::className(),["id"=>"service_instance_id"]);
    }
      
    public static function getServiceModelByTemplate($id = 1) {
        $serviceTemplate = ServiceModelTemplate::find()->where(['id' => $id])->One();
        $serviceModel = ServiceModel::find()->where(['id' => $serviceTemplate['service_model_id']])->One();
        return $serviceModel['name'];
    }
	
    public function batchInsert($rows) {
        $attributes = array_keys($rows[0]);   
        $res= Yii::$app->db->createCommand()
                ->batchInsert(self::tableName(), $attributes, $rows)
                ->execute();
        return $res;
    }
}