<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_service_mapping".
 *
 * @property int $id
 * @property int $service_id
 * @property int $sub_service_id
 * @property int $topology_id
 * @property int $created_by
 * @property string $created_date
 * @property int $updated_by
 * @property string $updated_date
 * @property int $active_status
 */
class ServiceMapping extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_service_mapping';
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
            [['service_id', 'sub_service_id', 'topology_id','active_status'], 'required'],
            ['service_id', 'unique', 'targetAttribute' => ['service_id','sub_service_id', 'topology_id']],
            [['service_id', 'sub_service_id', 'topology_id', 'created_by', 'updated_by'], 'integer'],
            [['created_date', 'updated_date'], 'safe'],
            [['active_status'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'service_id' => 'Service',
            'sub_service_id' => 'Sub Service',
            'topology_id' => 'Topology',
            'created_by' => 'Created By',
            'created_date' => 'Created On',
            'updated_by' => 'Updated By',
            'updated_date' => 'Updated On',
            'active_status' => 'Active Status',
            'topology.name'=>'Topology name',
            'subService.name'=>'Sub Service Name'
        ];
    }

    //Relation 
    public function getService(){
        return $this->hasOne(Services::className(),["id"=>"service_id"]);
    }

    //Relation 
    public function getSubService(){
        return $this->hasOne(SubServices::className(),["id"=>"sub_service_id"]);
    }

    //Relation 
    // public function getSubServices(){
    //     return $this->hasMany(SubServices::className(),["id"=>"sub_service_id"]);
    // }

    //Relation 
    public function getTopology(){
        return $this->hasOne(Topology::className(),["id"=>"topology_id"]);
    }

    //Relation 
    // public function getTopologies(){
    //     return $this->hasMany(Topology::className(),["id"=>"topology_id"]);
    // }
}
