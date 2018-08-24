<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tbl_customers".
 * @property int $id Primary Key for the table
 * @property int $customer_id for customer
 * @property varchar $company_name
 * @property string $first_name 
 * @property string $last_name 
 * @property text $description
 * @property int $provider_id
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
class Customer extends \yii\db\ActiveRecord
{
    public $providerName;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_customers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['provider_id','email_id','mobile_number','company_name','customer_id'], 'required'],    
            [['description','email_id','address','city','state','country','mobile_number','company_name'], 'string'],
            [['customer_id'],'integer'],
            [['email_id'],'email'],
            [['company_name'], 'unique'],
            [['customer_id'], 'unique'],
            [['active_status'], 'safe'],
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [    
            'id'   => 'ID',
            'customer_id'   =>  'Customer ID',
            'company_name'  =>  'Customer Name',
            'description'   =>  'Description',
            'provider_id'   =>  'Provider ID',
            'role_id'       =>  'Role ID',
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
     * Gets all customers of logged in provider
     *
     * @param integer $userId The userId of logged in provider
     * @return array
     */
    public function getAllCustomersOfProvider($userId) {
        $customers = self::find()->where(['provider_id' => $userId, 'approve_status' => 0])->all();

        return $customers;
    }

    public static function getCustomerName($id) {
        if (($customer = Customer::findOne($id)) !== null) {
            return $customer['company_name'];
        }
    }

    /* get all customers */
    public function getCustomers() {
        $customers = self::find()->All(); //in Yii2
        return $customers;
    }

    public static function getCustomerList() {
        $data = self::find()->asArray()->all();
        return ArrayHelper::map($data, "id", "company_name");
    }
    
     public static function getCustomerListByCustomerID($customerID) {
        $customers = self::find()->where(['customer_id'=>$customerID])->asArray()->all();
        return $customers;
    }
}
