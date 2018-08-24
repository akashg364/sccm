<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Query;
use app\models\Subservices;
use app\models\Parameters;
use backend\models\ServiceInstanceDevices;
use yii\web\NotFoundHttpException;

/**
 * @SWG\Definition(
 *   definition="ServicesListResponse",
 *   type="object",
 *   required={"status_code","sub_status_code","message","data"},
 *   @SWG\Property(property="status_code", type="integer",example="2009"),
 *   @SWG\Property(property="sub_status_code", type="integer",example="2009"),
 *   @SWG\Property(property="message", type="string",example="Service Data retrieval successfully"),
 *   @SWG\Property(property="data",type="array",@SWG\Items(@SWG\Property(property="services", type="array",@SWG\Items(@SWG\Property(property="service_id",type="integer",example="1"),@SWG\Property(property="service_name",type="string",example="Service Name"),@SWG\Property(property="sub_services",type="array",@SWG\Items(@SWG\Property(property="sub_service_id", type="integer", format="float", example="1.13"),@SWG\Property(property="sub_service_name",type="string",example="Sub Service Name")))))))
 * )
 *
 * @SWG\Definition(
 *   definition="CreateServices",
 *   type="object",
 *   required={"name","alias","description"},
 *   @SWG\Property(property="name", type="string"),
 *   @SWG\Property(property="alias", type="string"),
 *   @SWG\Property(property="description", type="string")
 * )
 *
 * @SWG\Definition(
 *   definition="UpdateServices",
 *   type="object",
 *   required={"name","alias","description"},
 *   allOf={
 *       @SWG\Schema(ref="#/definitions/CreateServices"),
 *   }
 * )
 *
 * @SWG\Definition(
 *   definition="Services",
 *   type="object",
 *   required={"name","alias","description"},
 *   allOf={
 *       @SWG\Schema(ref="#/definitions/CreateServices"),
 *       @SWG\Schema(
 *           required={"id"},
 *           @SWG\Property(property="id", format="int64", type="integer")
 *       )
 *   }
 * )
 * 
 * @SWG\Definition(
 *   definition="ServiceById",
 *   type="object",
 *   required={"status_code","sub_status_code","message","data"},
 *   @SWG\Property(property="status_code", type="integer",example="2009"),
 *   @SWG\Property(property="sub_status_code", type="integer",example="2009"),
 *   @SWG\Property(property="message", type="string",example="Service Data retrieval successfully"),
 *   @SWG\Property(property="data",@SWG\Property(property="subservice", @SWG\Property(property="sub_service_id",type="integer",example="2.37"),@SWG\Property(property="service_name",type="string",example="Service Sub Flavor name"),@SWG\Property(property="system_define",type="array",@SWG\Items(@SWG\Property(property="hostname", type="integer"),@SWG\Property(property="vpn-id",type="string"),@SWG\Property(property="vrf-name",type="string"))),@SWG\Property(property="user_define",type="array",@SWG\Items(@SWG\Property(property="remote-as", type="integer"),@SWG\Property(property="bit-value",type="string"),@SWG\Property(property="block-port-no",type="string")))))
 * )
 * 
 * @SWG\Definition(
 *   definition="CustomerServices",
 *   type="object",
 *   required={"status_code","sub_status_code","message","data"},
 *   @SWG\Property(property="status_code", type="integer",example="2009"),
 *   @SWG\Property(property="sub_status_code", type="integer",example="2010"),
 *   @SWG\Property(property="message", type="string",example="Service Data retrieval successfully"),
 *   @SWG\Property(property="data",@SWG\Property(property="RJIL", @SWG\Property(property="customer_id",type="integer",example="1"),@SWG\Property(property="customer_name",type="string",example="RJIL"),@SWG\Property(property="services",type="array",@SWG\Items(@SWG\Property(property="service_id", type="integer"),@SWG\Property(property="service_name",type="string"),@SWG\Property(property="service_instance_id",type="integer"),@SWG\Property(property="provisioning_datetime",type="datetime"),@SWG\Property(property="service_type",type="string"),@SWG\Property(property="type_of_change",type="string"),@SWG\Property(property="total_time_of_provision",type="string"),@SWG\Property(property="provisioning_status",type="string"),@SWG\Property(property="hostnames",type="array",@SWG\Items(@SWG\Property(property="hostname",type="string"))),@SWG\Property(property="interface",type="string"),@SWG\Property(property="vrf",type="string"),@SWG\Property(property="detailed_configuration",type="string")))))
 * )
 * 
 * @SWG\Definition(
 *   definition="ServiceDelete",
 *   type="object",
 *   required={"status_code","sub_status_code","message","data"},
 *   @SWG\Property(property="status_code", type="integer",example="200"),
 *   @SWG\Property(property="sub_status_code", type="integer",example="2000"),
 *   @SWG\Property(property="message", type="string",example="Service Deleted Successfully"),
 *   @SWG\Property(property="data",@SWG\Property(property="service_order_id", type="integer"))
 * )
 */

/**
 * @SWG\Definition(
 *   definition="DeleteFullService",
 *   type="object",
 *   required={"service_order_id"},
 *   allOf={
 *       @SWG\Schema(
 *           required={"service_order_id"},
 *           @SWG\Property(property="service_order_id", type="string")
 *       )
 *   }
 * )
 */

//class Services extends ActiveRecord {
class Services extends \backend\models\Services {

    public static function tableName() {
        return '{{%tbl_services}}';
    }

    public function rules() {
        return [
            [['name', 'alias', 'description'], 'required'],
            ['name', 'string', 'max' => 255],
            ['alias', 'string'],
            ['description', 'string'],
        ];
    }

    public function getAllServices($id = "") {
        $query = new Query;
        // compose the query
//                $query->select("`tbl_services`.`name` as 'service_name', `tbl_sub_services`.`name` as 'sub_service_name' ,"
//                        . "`tbl_services`.`id` as 'service_id' ,`tbl_sub_services`.`id` as 'sub_service_id'")
        $query->select("`tbl_services`.`name` as 'service_name', `tbl_sub_services`.`name` as 'sub_service_name' ,"
                        . "`tbl_services`.`id` as 'service_id' ,`tbl_sub_services`.`ref_id` as 'sub_service_id'")
                ->from('tbl_services')
                ->join('inner join', 'tbl_service_mapping', 'tbl_services.id=tbl_service_mapping.service_id')
                ->join('inner join', 'tbl_sub_services', 'tbl_service_mapping.sub_service_id=tbl_sub_services.id');
        //                ->where(['tbl_services.active_status'=>1,'tbl_sub_services.active_status'=>1]); 
        if (!empty($id)) {
            $query->where("tbl_services.id=$id");
        }
        $query->orderBy('tbl_services.id');
        $query->orderBy('tbl_sub_services.id');
        // build and execute the query
        $rows = $query->all();
        $data = array();
//        $i=1;
        foreach ($rows as $row) {
//            if(!in_array($row['service_name'], array_keys($data))){
//                    $i=1;
//                }
//                $subservice_id  =   $row['service_id'].".".$i;
            $data[$row['service_name']]['service_id'] = $row['service_id'];
            $data[$row['service_name']]['service_name'] = $row['service_name'];
//                $data[$row['service_name']]['sub_services'][] = array('sub_service_id'  => $row['sub_service_id'],
//                                                                  'sub_service_name'    =>$row['sub_service_name']);   
//                $data[$row['service_name']]['sub_services'][] = array('sub_service_id'  => $subservice_id,
            $data[$row['service_name']]['sub_services'][] = array('sub_service_id' => $row['sub_service_id'],
                'sub_service_name' => $row['sub_service_name']);
//                $i++;
        }
        if (!empty($data)) {
            return $data;
        } else {
            throw new NotFoundHttpException('Service Resource not found');
        }
    }

    public function getAllServicesByCustomerId($customerId) {
        $query = new Query;
//         $query->select("`tbl_services`.`name` as 'service_name', `tbl_sub_services`.`name` as 'sub_service_name' ,"
//                        . "`tbl_services`.`id` as 'service_id' ,`tbl_sub_services`.`id` as 'sub_service_id'")
        $query->select("distinct `tbl_services`.`name` as 'service_name', `tbl_sub_services`.`name` as 'sub_service_name' ,"
                        . "`tbl_services`.`id` as 'service_id' ,`tbl_sub_services`.`ref_id` as 'sub_service_id'")
                ->from('tbl_services')
                ->join('join', 'tbl_service_model', 'tbl_services.id=tbl_service_model.service_id')
                ->join('join', 'tbl_sub_services', 'tbl_service_model.sub_service_id=tbl_sub_services.id')
                ->join('join', 'tbl_service_instance', 'tbl_service_instance.service_model_id=tbl_service_model.id')
                ->where(['tbl_service_instance.customer_id' => $customerId]);
        $rows = $query->all();
        $data = array();
//        $i=1;
        foreach ($rows as $row) {
//                if(!in_array($row['service_name'], array_keys($data))){
//                    $i=1;
//                }
//                $subservice_id  =   $row['service_id'].".".$i;
            $data[$row['service_name']]['service_id'] = $row['service_id'];
            $data[$row['service_name']]['service_name'] = $row['service_name'];
//                $data[$row['service_name']]['sub_services'][] = array('sub_service_id'  => $row['sub_service_id'],
//                                                                  'sub_service_name'    =>$row['sub_service_name']);
            $data[$row['service_name']]['sub_services'][] = array('sub_service_id' => $row['sub_service_id'],
                'sub_service_name' => $row['sub_service_name']);
//                $i++;
        }
        if (!empty($data)) {
            return $data;
        } else {
            throw new NotFoundHttpException('Service Resource for the Customer not found');
        }
    }

    /**
     * 
     * @param type $sid
     * @return type integer
     * @throws NotFoundHttpException
     */
    public static function getServiceModelBySid($sid = "", $ssid = "") {
        $query = new Query;
        $query->SELECT('rm.variable_name,rm.type,dr.role_name,ss.name,ss.ref_id')
                ->from('tbl_service_model')
                ->join("INNER JOIN", "tbl_service_model_template as smt ON (tbl_service_model.id = smt.service_model_id)")
                ->join("INNER JOIN", "tbl_service_model_template_variables as smtv ON (smtv.template_id=smt.id)")
                ->join("INNER JOIN", "tbl_device_role as dr ON (dr.id=smt.device_role_id)")
                ->join("INNER JOIN", "tbl_variables_master as rm ON (rm.id = smtv.resource_manager_id)")
                ->join("INNER JOIN", "tbl_sub_services as ss ON (ss.id = tbl_service_model.sub_service_id)");
        if (!empty($sid)) {
            $query->where(['tbl_service_model.service_id' => $sid]);
        }
        if (!empty($ssid)) {
            $query->where(['tbl_sub_services.ref_id' => $ssid]);
        }
        $data = $query->all();


        if (!empty($data)) {
            return $data;
        } else {
            throw new NotFoundHttpException('Service Resource not found');
        }
    }

    /**
     * Get Services according to the Customers
     * @return type
     * @throws NotFoundHttpException
     */
    public static function getServicesbyCustomers() {
        $query = new Query;
        $query->SELECT('c.id as customer_id,c.company_name as customer_name,s.id as service_id,s.name as service_type,sb.ref_id as subservice_id,sb.name as service_name,si.service_order_id as service_instance_id,si.scheduled_date as provisioning_datetime,si.service_instance_status as provisioning_status,si.type_of_change as type_of_change,si.created_on as created_on,si.final_nso_payload as detailed_configuration,si.service_model_id,si.id as id')
                ->from('tbl_service_instance as si')
                ->join("INNER JOIN", "tbl_customers as c ON (si.customer_id=c.id)")
                ->join("INNER JOIN", "tbl_service_model  as sm ON (si.service_model_id=sm.id)")
                ->join("INNER JOIN", "tbl_services as s ON (sm.service_id=s.id)")
                ->join("INNER JOIN", "tbl_sub_services  as sb ON (sm.sub_service_id=sb.id)")
                ->distinct();
        $data = $query->all();
//echo $query->createCommand()->getRawSql();exit;
        if (!empty($data)) {
            return $data;
        } else {
            throw new NotFoundHttpException('Service Resource not found');
        }
    }

    /**
     * To get the Variable value using Service Model Id and Variable Name
     * @param type integer $service_model_id
     * @param type string $variableName
     */
    public static function getVariableValue($service_model_id, $variableName) {
        $query = new Query;
        $query->SELECT('variable_name,value')
                ->from('tbl_variables_master')
                ->join("INNER JOIN", "tbl_device_variables_mapping ON (tbl_variables_master.id=tbl_device_variables_mapping.resource_manager_id)")
                ->join("INNER JOIN", "tbl_service_model_template ON (tbl_service_model_template.id=tbl_device_variables_mapping.service_model_template_id)")
                ->where(['tbl_variables_master.variable_name' => $variableName, 'tbl_service_model_template.service_model_id' => $service_model_id]);
        $data = $query->one();

//echo $query->createCommand()->getRawSql();exit;
        if (!empty($data)) {
            return $data['value'];
        }
    }

    public static function getDeviceHostNamesByInstanceID($service_instance_id) {
        $query = new Query;
        $query->SELECT('hostname')
                ->from('tbl_device')
                ->join("INNER JOIN", "tbl_service_instance_devices ON (tbl_service_instance_devices.device_id=tbl_device.id)")
                ->where(['tbl_service_instance_devices.service_instance_id' => $service_instance_id]);
        $data = $query->all();
        if (!empty($data)) {
            return $data;
        }
    }

}