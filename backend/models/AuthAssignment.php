<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "auth_assignment".
 * @property int $user_id
 * @property string $item_name
 */
class AuthAssignment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_assignment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [          
            [['item_name'], 'string'],           
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [    
            'user_id'   => 'User ID', 
            'item_name' => 'Item Name',           
        ];
    }
}
