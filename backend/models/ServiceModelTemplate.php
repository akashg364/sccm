<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_service_model_template".
 *
 * @property int $id
 * @property int $device_role_id
 * @property int $template_version
 * @property string $cli_nip
 * @property string $nso_payload
 * @property int $created_by
 * @property string $created_on
 * @property int $updated_by
 * @property string $updated_on
 */
class ServiceModelTemplate extends \yii\db\ActiveRecord {
    
    public $resource_manager_id;
    public $next_device_role_id;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'tbl_service_model_template';
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
    public function rules() {
        return [
            [['device_role_id','service_model_id','template_version', 'cli_nip', 'nso_payload','resource_manager_id'], 'required'],
            [['device_role_id', 'template_version', 'created_by', 'updated_by'], 'integer'],
            [['cli_nip', 'nso_payload'], 'string'],
            [['created_on', 'updated_on','reference_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'device_role_id' => 'Device Role ID',
            'service_model_id' => 'Service Model ID',
            'template_version' => 'Template Version',
            'cli_nip' => 'Cli Nip',
            'nso_payload' => 'Nso Payload',
            'created_by' => 'Created By',
            'created_on' => 'Created On',
            'updated_by' => 'Updated By',
            'updated_on' => 'Updated On',
        ];
    }

    public function batchInsert($rows) {
        $attributes = array_keys($rows[0]);   
        $res= Yii::$app->db->createCommand()
                ->batchInsert(ServiceModelTemplate::tableName(), $attributes, $rows)
                ->execute();
        return $res;
    }
    
    public static function getTemplatePayload($id) {
        if (($template = ServiceModelTemplate::findOne($id)) !== null) {
            return $template['nso_payload'];
        }
    }

    //Relation
    public function getDeviceRole(){
        return $this->hasOne(DeviceRole::className(),['id'=>'device_role_id']);
    }

    // Related Device Role
    public function getRelatedDeviceRole(){
        return $this->hasOne(DeviceRole::className(),['id'=>'reference_id']);
    }

     //Relation
    public function getServiceModel(){
        return $this->hasOne(ServiceModel::className(),['id'=>'service_model_id']);
    }

    //Relation
    public function getServiceModelTemplateVariables(){
        return $this->hasMany(ServiceModelTemplateVariables::className(),['template_id'=>'id']);
    }


    public static function getTemplateDeviceRoleList($service_model_id,$skip_device_role_id=null){
        $serviceModelTemplates = ServiceModelTemplate::find()
        ->joinWith(['deviceRole'])
        ->where(["service_model_id"=>$service_model_id])
        ->all();

        $deviceRolesArray = [];
        foreach ($serviceModelTemplates as $key => $template) {
            $deviceRole = $template->deviceRole;
            if(!empty($skip_device_role_id) && $template->device_role_id == $skip_device_role_id){
                continue;
            }
            $deviceRolesArray[$deviceRole->id] = $deviceRole->role_name; 
        }
        return $deviceRolesArray;
    }
}
