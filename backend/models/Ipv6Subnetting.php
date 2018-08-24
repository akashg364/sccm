<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_ipv6_subnetting".
 *
 * @property int $id
 * @property string $useable_ips
 * @property string $total_ips
 * @property string $subnet
 * @property int $is_active
 */
class Ipv6Subnetting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_ipv6_subnetting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['useable_ips', 'total_ips'], 'required'],
            [['useable_ips', 'total_ips'], 'string', 'max' => 100],
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
            'useable_ips' => 'Useable Ips',
            'total_ips' => 'Total Ips',
            'subnet' => 'Subnet',
            'is_active' => 'Is Active',
        ];
    }
}
