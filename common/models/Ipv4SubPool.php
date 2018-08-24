<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tbl_ipv4_sub_pool".
 *
 * @property string $id
 * @property int $ipv4_pool_id
 * @property string $ip_pool
 * @property int $ip_count
 * @property string $created_date
 * @property string $modified_date
 */
class Ipv4SubPool extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_ipv4_sub_pool';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ipv4_pool_id', 'created_date'], 'required'],
            [['ipv4_pool_id', 'ip_count'], 'integer'],
            [['created_date', 'modified_date'], 'safe'],
            [['ip_pool'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ipv4_pool_id' => 'Ipv4 Pool ID',
            'ip_pool' => 'Ip Pool',
            'ip_count' => 'Ip Count',
            'created_date' => 'Created Date',
            'modified_date' => 'Modified Date',
        ];
    }

    public function getPoolAssignments(){
        return $this->hasMany(Ipv4PoolAssignment::className(),["sub_ip_pool_id"=>"id"]);
    }
}
