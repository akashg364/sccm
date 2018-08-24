<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tbl_service_model".
 *
 * @property int $id
 * @property string $name
 * @property int $service_id
 * @property int $sub_service_id
 * @property int $topology_id
 * @property string $description
 * @property int $created_by
 * @property string $created_on
 * @property int $updated_by
 * @property string $updated_on
 */
class ServiceModel extends \yii\db\ActiveRecord
{
    public $user_variables;
    public $system_variables;
    public $device_role_id;
    public $service_name;
    public $sub_service_name;
    public $topology_name;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_service_model';
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
    public function rules()
    {
        return [
            [['name', 'service_id', 'sub_service_id', 'topology_id', 'description','system_variables','user_variables','payload_key'], 'required'],
            [['service_id', 'sub_service_id', 'topology_id', 'created_by', 'updated_by'], 'integer'],
            [['description'], 'string'],
            [['created_on', 'updated_on','device_role_id','user_variables','system_variables'], 'safe'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
			'payload_key'=>"Payload Key",
            'name' => 'Service Name',
            'service_id' => 'Service',
            'sub_service_id' => 'Sub Service',
            'topology_id' => 'Topology',
            'description' => 'Description',
            'created_by' => 'Created By',
            'created_on' => 'Created On',
            'updated_by' => 'Updated By',
            'updated_on' => 'Updated On',
        ];
    }
	
	 //Relation
    public function getServiceModelOneTemplate(){
        return $this->hasOne(ServiceModelTemplate::className(),['service_model_id'=>'id']);
    }
	
    //Relation
    public function getServiceModelTemplate(){
        return $this->hasMany(ServiceModelTemplate::className(),['service_model_id'=>'id']);
    }
     //Relation
    public function getServiceModelVariables(){
        return $this->hasMany(ServiceModelVariables::className(),['service_model_id'=>'id']);
    }
     //Relation
    public function getService(){
         return $this->hasOne(Services::className(),['id'=>'service_id']);
    }
     //Relation
    public function getSubService(){
         return $this->hasOne(SubServices::className(),['id'=>'sub_service_id']);
    }
     //Relation
    public function getTopology(){
         return $this->hasOne(Topology::className(),['id'=>'topology_id']);
    }
    
    public static function truncate($str, $len) {
        $tail = max(0, $len - 10);
        $trunk = substr($str, 0, $tail);
        $trunk .= strrev(preg_replace('~^..+?[\s,:]\b|^...~', '...', strrev(substr($str, $tail, $len - $tail))));
        return $trunk;
    }

    /**
     * Gets all services of logged in user
     *
     * @param integer $userId The userId of logged in user
     * @return array
     */
    public function getAllServicesByUser($userId) {
        $services = self::find()->where(['created_by' => $userId])->all();

        return $services;
    }

    /* get all services */
    public function getServiceModelAll() {
        $serviceModel = self::find()->where(["is_deleted"=>0])->All(); //in Yii2
        return $serviceModel;
    }

    public static function getServiceModelList() {
        $data = self::find()->asArray()->all();
        return ArrayHelper::map($data, "id", "name");
    }
    
    public static function getServiceModel($id) {
        if (($serviceModel = ServiceModel::findOne($id)) !== null) {
            return $serviceModel;
        }
    }
}
