<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_ipv6_pool".
 *
 * @property int $id
 * @property string $pool
 * @property string $subnet
 * @property int $created_by
 * @property string $created_date
 * @property int $updated_by
 * @property string $updated_date
 * @property int $is_full
 * @property int $is_active
 */
class Ipv6Pool extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_ipv6_pool';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pool', 'subnet', 'created_by', 'created_date', 'updated_by'], 'required'],
            [['created_by', 'updated_by'], 'integer'],
            [['created_date', 'updated_date'], 'safe'],
            [['pool'], 'string', 'max' => 45],
            [['is_full', 'is_active'], 'string', 'max' => 1],
            [['pool'], 'unique', 'targetAttribute' => ['pool'], 'message' => 'Pool Already Exists!!!'],
            ['pool', 'validIpv6'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pool' => 'Ipv6 Pool',
            'subnet' => 'Subnet',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
            'updated_by' => 'Updated By',
            'updated_date' => 'Updated Date',
            'is_full' => 'Is Full',
            'is_active' => 'Is Active',
        ];
    }
    
    public function validIpv6($attribute, $params)
    {
        if (!filter_var($this->pool, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->addError($attribute, 'Invalid IPv6 address!!!');
        }
    }
    
    public static function get($id)
    {
        if (($model = Ipv6Pool::findOne($id)) !== null) {
            if($model->is_full == 1)
            {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }
    
    public static function isPoolFull($id)
    {
        if (($model = Ipv6Pool::findOne($id)) !== null) {
            if($model->is_full == 1)
            {
                return null;
            } else {
                return $model;
            }
        }
        return null;
    }
}