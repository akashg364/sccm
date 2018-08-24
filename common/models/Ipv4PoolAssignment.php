<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tbl_ipv4_pool_assignment".
 *
 * @property string $id
 * @property int $sub_ip_pool_id
 * @property string $subnet
 * @property string $start_ip
 * @property string $end_ip
 * @property string $device_id
 * @property int $ip_count
 * @property string $created_date
 * @property string $modified_date
 */
class Ipv4PoolAssignment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_ipv4_pool_assignment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sub_ip_pool_id','device_id','start_ip','end_ip','subnet','usable_ips'], 'required'],
            [['sub_ip_pool_id', 'ip_count','usable_ips'], 'integer'],
            [['created_date', 'modified_date','ip_count'], 'safe'],
            [['subnet'], 'string', 'max' => 100],
            [['start_ip', 'end_ip'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sub_ip_pool_id' => 'Sub Ip Pool ID',
            'subnet' => 'Subnet',
            'start_ip' => 'Start Ip',
            'end_ip' => 'End Ip',
            'device_id' => 'Device',
            'ip_count' => 'Ip Count',
            'created_date' => 'Created Date',
            'modified_date' => 'Modified Date',
            'usable_ips'=>"Usable Ips",
        ];
    }
}
