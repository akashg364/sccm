<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_privilege".
 * @property int $id Primary Key for the table
 * @property string $name
 * @property datetime $created_date
 * @property int $created_by
 * @property int $updated_by
 * @property datetime $last_updated_date Description   
 * @property int $active_status
 */
class Privilege extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_privileges';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [    
            'id'   => 'ID', 
            'name' => 'Name',           
        ];
    }
}
