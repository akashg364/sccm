<?php

namespace app\models;

use yii\db\ActiveRecord;


class Parameters extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%parameters}}';
    }

    public function rules()
    {
        return [
            [['name', 'prefix','type','sub_service_id','client_id'], 'required'],
            ['name', 'string', 'max' => 255],
            ['prefix', 'string'],
            ['type', 'string'],
            ['sub-service_id', 'int'],
            ['client_id', 'int'],
        ];
    }
}