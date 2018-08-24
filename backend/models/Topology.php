<?php

namespace backend\models;

use Yii;
use backend\models\SubServices;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tbl_topology".
 *
 * @property int $id
 * @property int $sid
 * @property int $ssid
 * @property string $topology
 * @property string $description
 * @property string $created_date
 * @property string $modified
 */
class Topology extends \common\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'tbl_topology';
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
            [['created_date', 'updated_date'], 'safe'],
            [['name'], 'unique'],
            [['name', 'description','active_status'], 'required'],
            [['name'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Topology name',
            'description' => 'Description',
            'created_date' => 'Created date',
            'modified' => 'Modified',
        ];
    }

    /* get sub service name */

    public function getSubservice() {
        return $this->hasOne(SubServices::classname(), ['id' => 'ssid']);
    }

    /* get sub service name */

    public function getservice() {
        return $this->hasOne(Services::classname(), ['id' => 'sid']);
    }

    public static function getTopologyList() {
        $data = ArrayHelper::map(self::find()->asArray()->all(),"id","name");
      return $data;
    }
	
    public function getTopologyAll() {
        $services = self::find()->where('id IN (4,5)')->All(); //in Yii2
        return $services;
    }

}
