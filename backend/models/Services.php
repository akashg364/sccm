<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tbl_services".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $created_date
 * @property string $updated_date
 */
class Services extends \common\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'tbl_services';
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
            [['created_date', 'updated_date','ref_id'], 'safe'],
            [['name', 'description','active_status'], 'required'],
            [['name'], 'string', 'max' => 50],
            [['name'], 'unique'],
            [['description'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Service Name',
            'description' => 'Description',
            'created_date' => 'Created Date',
            'updated_date' => 'Updated Date',
        ];
    }

    /* get all services */

    public function getServicesAll() {
        $services = self::find()->All(); //in Yii2
        return $services;
    }

    public static function getServiceList() {
        $data = self::find()->asArray()->all();
        return ArrayHelper::map($data, "id", "name");
    }
    
    public static function getServiceName($id) {
        if (($service = Services::findOne($id)) !== null) {
            return $service['name'];
        }
    }

    // Relation
    public function getCreatedBy(){
        return $this->hasOne(User::className(),["id"=>"created_by"]);
    } 

    // Relation
    public function getUpdatedBy(){
        return $this->hasOne(User::className(),["id"=>"modified_by"]);
    } 
	
	#
	public static function getServiceNameByServiceOrder ($service_order_id) {
		
		$sql = "select s.name from tbl_service_instance si 
				left join tbl_service_model sm on sm.id = si.service_model_id 
				left join tbl_services s on s.id = sm.service_id 
				where si.service_order_id = '$service_order_id'";
		$results = \Yii::$app->db->createCommand($sql)->queryOne();
		return $results['name'];
	} // 
}