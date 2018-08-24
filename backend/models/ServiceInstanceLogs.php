<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_service_instance_logs".
 *
 * @property int $id
 * @property string $service_order_id
 * @property int $customer_id
 * @property int $service_model_id
 * @property int $endpoints
 * @property string $user_defined_data
 * @property string $final_nso_payload
 * @property string $scheduled_status
 * @property string $scheduled_date
 * @property int $created_by
 * @property string $created_on
 * @property int $updated_by
 * @property string $updated_on
 * @property int $is_active
 * @property string $service_instance_status
 * @property string $type_of_change
 * @property string $inclusion_hostname
 * @property string $topology
 * @property string $hub_hostname
 * @property string $uniqueId
 */
class ServiceInstanceLogs extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_service_instance_logs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_order_id', 'customer_id', 'service_model_id', 'endpoints', 'topology','id'], 'required'],
            [['customer_id', 'service_model_id', 'endpoints', 'created_by', 'updated_by'], 'integer'],
            [['user_defined_data', 'final_nso_payload', 'scheduled_status', 'service_instance_status', 'type_of_change', 'topology'], 'string'],
            [['scheduled_date', 'created_on', 'updated_on'], 'safe'],
            [['service_order_id'], 'string', 'max' => 250],
            [['inclusion_hostname', 'hub_hostname', 'uniqueId'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'service_order_id' => 'Service Order ID',
            'customer_id' => 'Customer ID',
            'service_model_id' => 'Service Model ID',
            'endpoints' => 'Endpoints',
            'user_defined_data' => 'User Defined Data',
            'final_nso_payload' => 'Final Nso Payload',
            'scheduled_status' => 'Scheduled Status',
            'scheduled_date' => 'Scheduled Date',
            'created_by' => 'Created By',
            'created_on' => 'Created On',
            'updated_by' => 'Updated By',
            'updated_on' => 'Updated On',
            'is_active' => 'Is Active',
            'service_instance_status' => 'Service Instance Status',
            'type_of_change' => 'Type Of Change',
            'inclusion_hostname' => 'Inclusion Hostname',
            'topology' => 'Topology',
            'hub_hostname' => 'Hub Hostname',
            'uniqueId' => 'Unique ID',
        ];
    }
}
