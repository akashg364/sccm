<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

class DataType extends \yii\db\ActiveRecord
{
    
      /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_data_type';
    }

    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\BlameableBehavior::className(),
                'updatedByAttribute' => 'modified_by',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['data_type'], 'required'],
            ['data_type','unique','filter'=>["is_active"=>1]],
            [['id', 'created_by', 'modified_by', 'is_active'], 'integer'],
            [['created_date', 'modified_date'], 'safe'],
            [['data_type'], 'string', 'max' => 30],
        ];
    }

    public static function getDataTypeDropdownList(){
        $data = self::find()
        ->where(["is_active"=>1])
        ->orderBy(["id"=>SORT_DESC])
        ->asArray()
        ->all();
        return array_map("ucfirst",ArrayHelper::map($data,"id","data_type"));
    }
    
}
