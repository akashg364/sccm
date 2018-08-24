<?php

namespace backend\models;

use Yii;
use backend\models\Services;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tbl_sub_services".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $created_date
 * @property string $updated_date
 */
class SubServices extends \common\models\BaseModel {
    public $components;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'tbl_sub_services';
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
            [['created_date', 'updated_date','component_blocks'], 'safe'],
            [['name', 'description','active_status','is_managed','home_type','routing_protocol','terminated_at','topology_type','eds','ref_id'], 'required'],
            [['name'], 'unique', 'targetAttribute' => ['name']],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'created_date' => 'Created Date',
            'updated_date' => 'Updated Date',   
            'ref_id'    =>  'Sub Survice Id',
            'is_managed'    =>  'Is Managed'
        ];
    }

    /* get all services */

    public function getSubservicesAll() {
        $services = self::find()->All(); //in Yii2
        return $services;
    }

    /* get service name */

    public function getService() {
        return $this->hasOne(Services::classname(), ['id' => 'sid']);
    }
    
    public static function getSubServiceName($id) {
        if (($subService = SubServices::findOne($id)) !== null) {
            return $subService['name'];
        }
    }

    public static function getSubserviceList() {
        $data = self::find()->asArray()->all();
        $data = ArrayHelper::map($data,"id","name");                
        return $data;
    }
    
    public static function getSubServiceById($id){
        $data = self::find()->where(["id"=>$id])->limit(1)->asArray()->one();
        return $data;
    }
        
    public static function getComponentList($id) {
        $assign_components = [];
        $assign_components_str = '';
        $assign_components = ServiceComponentReference::getComponents($id);
        return $assign_components;
        if(!empty($assign_components)) {
            $assign_elements =  ArrayHelper::map($assign_components, "id", "name");

            $assign_components_str = implode(',<br/> ', $assign_elements);
            return $assign_components_str;
        }    
    }

    /*Relation : SubService => Component Blocks*/
    public function getCombonentBlocks(){
        return $this->hasMany(ServiceComponentReference::className(),["sub_service_id"=>"id"]);
    }
}
