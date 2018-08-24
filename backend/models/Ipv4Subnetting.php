<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_ipv4_subnetting".
 *
 * @property int $id
 * @property string $class
 * @property string $useable_ips
 * @property string $total_ips
 * @property string $subnet_mask
 * @property string $wildcard_mask
 * @property string $subnet
 * @property int $is_active
 */
class Ipv4Subnetting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_ipv4_subnetting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['useable_ips', 'total_ips', 'subnet_mask', 'wildcard_mask'], 'required'],
            [['class'], 'string', 'max' => 5],
            [['useable_ips', 'total_ips'], 'string', 'max' => 100],
            [['subnet_mask', 'wildcard_mask'], 'string', 'max' => 20],
            [['subnet'], 'string', 'max' => 10],
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
            'class' => 'Class',
            'useable_ips' => 'Useable Ips',
            'total_ips' => 'Total Ips',
            'subnet_mask' => 'Subnet Mask',
            'wildcard_mask' => 'Wildcard Mask',
            'subnet' => 'Subnet',
            'is_active' => 'Is Active',
        ];
    }
}
