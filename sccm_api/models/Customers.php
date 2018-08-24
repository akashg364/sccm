<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Query;
use app\models\Subservices;
use app\models\Services;

/**
 * @SWG\Definition(
 *   definition="Customers",
 *   type="object",
 *   required={"customer_id","customer_name"},
 *   @SWG\Property(property="customer_name", type="string"),
 *   @SWG\Property(property="customer_id", type="string"),
 * )
 * 
 * @SWG\Definition(
 *   definition="CustomersCreateResponse",
 *   type="object",
 *   required={"customer_id"},
 *   @SWG\Property(property="customer_id", type="string"),
 * )
 * 
 * @SWG\Definition(
 *   definition="CustomersDelete",
 *   type="object",
 *   required={"status_code","sub_status_code","message","data"},
 *   @SWG\Property(property="status_code", type="integer",example="1003"),
 *   @SWG\Property(property="sub_status_code", type="integer",example="3001"),
 *   @SWG\Property(property="message", type="string",example="Customer Deleted Successfully"),
 *   @SWG\Property(property="data",@SWG\Property(property="id", type="integer"),@SWG\Property(property="customer_id", type="string"),@SWG\Property(property="first_name", type="string"),@SWG\Property(property="last_name", type="string"),@SWG\Property(property="company_name", type="string"),@SWG\Property(property="description", type="string"),@SWG\Property(property="role_id", type="interger"),@SWG\Property(property="provider_id", type="integer"),@SWG\Property(property="email_id", type="string"),@SWG\Property(property="mobile_number", type="integer"),@SWG\Property(property="address", type="string"),@SWG\Property(property="city", type="string"),@SWG\Property(property="state", type="string"),@SWG\Property(property="country", type="string"),@SWG\Property(property="active_status", type="string"),@SWG\Property(property="approve_status", type="string"),@SWG\Property(property="created_date", type="date"),@SWG\Property(property="updated_date", type="date"),@SWG\Property(property="created_by", type="integer"),@SWG\Property(property="status_action_taken_by", type="string"),@SWG\Property(property="acceptance_action_taken_by", type="string"))
 * )
 * 
 * @SWG\Definition(
 *   definition="CustomerData",
 *   type="object",
 *   required={"status_code","sub_status_code","message","data"},
 *   @SWG\Property(property="status_code", type="integer",example="1004"),
 *   @SWG\Property(property="sub_status_code", type="integer",example="4001"),
 *   @SWG\Property(property="message", type="string",example="Customer Data retrieval successfully"),
 *   @SWG\Property(property="data",@SWG\Property(property="customer_id", type="string", example="123"),@SWG\Property(property="customer_name", type="string"),@SWG\Property(property="services",type="array",@SWG\Items(@SWG\Property(property="service_id", type="integer", example="1"),@SWG\Property(property="service_name",type="string"),@SWG\Property(property="sub_services",type="array",@SWG\Items(@SWG\Property(property="sub_service_id", type="integer"),@SWG\Property(property="sub_service_name",type="string"))))))
 * )
 * 
 * @SWG\Definition(
 *   definition="CustomersUpdate",
 *   type="object",
 *   required={"status_code","sub_status_code","message","data"},
 *   @SWG\Property(property="status_code", type="integer",example="1002"),
 *   @SWG\Property(property="sub_status_code", type="integer",example="2001"),
 *   @SWG\Property(property="message", type="string",example="Customer Updated Successfully"),
 *   @SWG\Property(property="data",@SWG\Property(property="customer_id", type="string"))
 * )
 */

class Customers extends ActiveRecord {

    public static function tableName() {
        return '{{%tbl_customers}}';
    }

    public function rules() {
        return [
            [['company_name','customer_id'], 'required'],
            ['customer_id', 'string', 'max' => 255],   
            ['company_name', 'string', 'max' => 255],
            [['company_name'], 'unique'],
            [['customer_id'], 'unique'],
        ];
    }
    
    public function getAllCustomers($id = "") {
        
    }
    
}
