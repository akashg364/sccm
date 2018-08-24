<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_ipv4_pool".
 *
 * @property int $id
 * @property string $pool
 * @property int $created_by
 * @property string $created_date
 * @property int $updated_by
 * @property string $updated_date
 * @property int $is_full
 * @property int $is_active
 */
class Ipv4Pool extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_ipv4_pool';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pool', 'created_by', 'created_date', 'updated_by', 'updated_date'], 'required'],
            [['created_by', 'updated_by'], 'integer'],
            [['created_date', 'updated_date'], 'safe'],
            [['pool'], 'string', 'max' => 45],
            [['is_full', 'is_active'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pool' => 'Ipv4 Pool',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
            'updated_by' => 'Updated By',
            'updated_date' => 'Updated Date',
            'is_full' => 'Is Full',
            'is_active' => 'Is Active',
        ];
    }

    public static function isPoolFull($id){
        
        if ($model = Ipv4Pool::findOne($id)) {
            return $model->is_full?null:$model;        
        }
        return null;
    }

    public function getSubPools(){
        return $this->hasMany(Ipv4SubPool::className(),["ipv4_pool_id"=>"id"]);
    }
}
