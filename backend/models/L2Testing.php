<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "l2_testing".
 *
 * @property int $id
 * @property int $service_id
 * @property int $sub_service_id
 * @property int $network_engineer
 * @property string $bangalore_lab_status
 * @property string $bangalore_datetime
 * @property string $reliance_lab_status
 * @property string $reliance_datetime
 * @property int $status
 */
class L2Testing extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'l2_testing';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['service_id', 'sub_service_id', 'network_engineer', 'bangalore_datetime', 'reliance_datetime', 'status'], 'required'],
            [['service_id', 'sub_service_id', 'network_engineer', 'status'], 'integer'],
            [['bangalore_lab_status', 'reliance_lab_status'], 'string'],
            [['bangalore_datetime', 'reliance_datetime'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'service_id' => 'Service ID',
            'sub_service_id' => 'Sub Service ID',
            'network_engineer' => 'Network Engineer',
            'bangalore_lab_status' => 'Bangalore Lab Status',
            'bangalore_datetime' => 'Bangalore Datetime',
            'reliance_lab_status' => 'Reliance Lab Status',
            'reliance_datetime' => 'Reliance Datetime',
            'status' => 'Status',
        ];
    }
    
    /*Relation with Service*/
    public function getService() {
        return $this->hasOne(Services::className(), ["id" => "service_id"]);
    }
    
    /*Relation with SubService*/
    public function getSubService() {
        return $this->hasOne(SubServices::className(), ["id" => "sub_service_id"]);
    }

    /*Relation with Service Model*/
    public function getNetworkEngineer() {
        return $this->hasOne(NetworkEngineers::className(), ["id" => "network_engineer"]);
    }
}
