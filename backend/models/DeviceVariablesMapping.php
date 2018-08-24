<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_device_variables_mapping".
 *
 * @property int $id
 * @property int $service_model_template_id
 * @property int $device_id
 * @property int $resource_manager_id
 * @property string $value
 * @property string $created_date
 * @property int $created_by
 * @property string $updated_date
 * @property int $updated_by
 */
class DeviceVariablesMapping extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_device_variables_mapping';
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
            [['service_model_template_id', 'device_id', 'resource_manager_id', 'value', 'created_by', 'updated_by'], 'required'],
            [['service_model_template_id', 'device_id', 'resource_manager_id', 'created_by', 'updated_by'], 'integer'],
            [['created_date', 'updated_date'], 'safe'],
            [['value'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'service_model_template_id' => 'Service Model Template ID',
            'device_id' => 'Device ID',
            'resource_manager_id' => 'Resource Manager ID',
            'value' => 'Value',
            'created_date' => 'Created Date',
            'created_by' => 'Created By',
            'updated_date' => 'Updated Date',
            'updated_by' => 'Updated By',
        ];
    }
}
