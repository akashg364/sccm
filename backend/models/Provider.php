<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_providers".
 * @property int $id Primary Key for the table
 * @property varchar $company_name
 * @property text $description
 * @property string $email_id 
 * @property string $mobile_number 
 * @property text $address   
 * @property string $city
 * @property string $state
 * @property string $country
 * @property tinyint $active_status
 * @property tinyint $approve_status
 * @property datetime $created_date
 * @property datetime $updated_date
 * @property int $created_by
 */
class Provider extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_providers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email_id','mobile_number','company_name'], 'required'],    
            [['description','email_id','address','city','state','country','mobile_number','company_name'], 'string'],            
            [['email_id'],'email'],
            [['company_name'], 'unique'],
            [['active_status'], 'safe'],[['approve_status'], 'safe'],
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [    
            'id'   => 'ID',            
            'company_name'  =>  'Provider Name',
            'description'   =>  'Description',
//            'role_id'   =>  'Role',
            'email_id'  =>  'Email ID',
            'mobile_number' =>  'Contact Number',
            'address'   =>  'Address',
            'city'      =>  'City',
            'state'     =>  'State',
            'country'   =>  'Country',
            'active_status' =>  'Active Status',  
            'approve_status' => 'Approve Status'
        ];
    }
    
    /**
     * Gets all providers of logged in admin
     *
     * @param integer $userId The userId of logged in admin
     * @return array
     */
    public function getAllProvidersOfAdmin($userId) {
        $providers = self::find()->where(['added_by' => $userId, 'approve_status' => 0])->all();

        return $providers;
    }

}
