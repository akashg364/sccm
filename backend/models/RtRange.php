<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_rt_range".
 *
 * @property int $id
 * @property int $customer_id
 * @property string $topology
 * @property int $start_value
 * @property int $end_value
 * @property int $last_used
 * @property int $is_active
 */
class RtRange extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_rt_range';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'topology', 'start_value', 'end_value', 'last_used', 'is_active'], 'required'],
            [['id', 'customer_id', 'start_value', 'end_value', 'last_used'], 'integer'],
            [['topology'], 'string'],
            [['is_active'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'topology' => 'Topology',
            'start_value' => 'Start Value',
            'end_value' => 'End Value',
            'last_used' => 'Last Used',
            'is_active' => 'Is Active',
        ];
    }
    
    public static function getRtRange($id) {
        if (($model = RtRange::findOne($id)) !== null) {
            return $model;
        }
    }
}