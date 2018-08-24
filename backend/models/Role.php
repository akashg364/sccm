<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_roles".
 * @property int $id Primary Key for the table
 * @property string $role
 * @property datetime $created_date
 * @property int $created_by
 * @property int $updated_by
 * @property datetime $last_updated_date Description   
 * @property int $active_status
 */
class Role extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_roles';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['role'], 'required'],
            [['role'], 'string', 'max' => 250],
            [['role'], 'unique'],
            [['privileges'],'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [    
            'id'   => 'ID', 
            'role' => 'Role',           
            'privilege' => 'Privileges'
        ];
    }
}
