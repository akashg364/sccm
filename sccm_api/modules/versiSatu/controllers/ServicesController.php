<?php

namespace app\modules\versiSatu\controllers;

#use backend\models\Services;

use app\components\Controller;
use app\models\Services;
use yii\web\NotFoundHttpException;
use Yii;
use common\components\Nso;
use backend\models\ServiceInstance;

class ServicesController extends Controller {

    public function actionIndex() {
        $data = [];
        try {
            $serviceModel = new Services();
            $services = $serviceModel->getAllServices();
            $serviceData = array();
            foreach ($services as $service) {
                $serviceData[] = $service;
            }
            $data['services'] = $serviceData;
            return $this->apiResponse(200, 2009, 2010, $data, $message = "Service Data retrieval successfully");
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            return $this->apiResponse(200, 300, 3000, $data, $message = "", $error);
        }
    }

    public function actionCreate() {
        $dataRequest['Services'] = Yii::$app->request->getBodyParams();
        $model = new Services();
//        throw new NotFoundHttpException('Resource not found');
        if ($model->load($dataRequest) && $model->save()) {
            return $this->apiCreated($model);
        }

        return $this->apiValidate($model->errors);
    }

    public function actionUpdate($id) {
        $dataRequest['Services'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if ($model->load($dataRequest) && $model->save()) {
            return $this->apiUpdated($model);
        }

        return $this->apiValidate($model->errors);
    }

//    public function actionView($id)
//    {
//        return $this->apiItem($this->findModel($id));
//    }

    public function actionView($id) {
        $service_list_data = array();
        try {
            $service_model = new Services();
            $service_list = $service_model->getServiceModelBySid($id);
            
//            $i=0;
            foreach ($service_list as $key => $value) {
//                $service_list_data['sub_service_id']=$value['id'];
//                $service_list_data['sub_service_id']=$id.".".$i;
                $service_list_data['sub_service_id'] = $value['ref_id'];
                $service_list_data['sub_service_name'] = $value['name'];
                if ($value['type'] == "user") {
                    $service_list_data['user_define'][] = $value['variable_name'];
//                    $service_list_data['user_define'][$value['role_name']][] = $value['variable_name'];
                } else if ($value['type'] == "system") {
                    $service_list_data['system_define'][] =  $value['variable_name'];
//                    $service_list_data['system_define'][$value['role_name']][] =  $value['variable_name'];
                    // $service_list_data['system'][]
                }
//                $i++;
            }
            if (empty($service_list_data)) {
                $message = "Service Data not found for provided ServiceID";
            } else {
                $message = "Service Data retrieval successfully";
            }
            return $this->apiResponse(200, 2009, 9002, $service_list_data, $message);
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            return $this->apiResponse(200, 300, 3000, $service_list_data, $message = "", $error);
        }
    }

    public function actionDelete($id) {
        if ($this->findModel($id)->delete()) {
            return $this->apiDeleted(true);
        }
        return $this->apiDeleted(false);
    }

    public function actionListing($service_id, $subserviceid) {
        $serviceModel = new Services();
        $services = $serviceModel->getAllServicesByCustomerId($id);
    }

    protected function findModel($id) {
        if (($model = Services::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Resource not found');
        }
    }

    /*
      Author : Jitendra Gamit
      Date : 2ndMay, 2018
     * * */

    public function actionDeletefullservice() {
        $str_request = Yii::$app->request->getBodyParams();
        $service_order_id = $str_request['service_order_id'];
        if (trim($str_request['service_order_id']) == '') {
            return $this->apiResponse(200, 10001, 10001, $data = '', $message = '', "Service Order ID missing", $appData);
        }
        $service_order_id = $str_request['service_order_id'];
        $ServiceInstanceModel = ServiceInstance::find()->where(['service_order_id' => $service_order_id])->one(); 
        $serviceInstanceId = $ServiceInstanceModel['id'];
        $serviceInstance = new ServiceInstance();
        $res = $serviceInstance->deleteService($serviceInstanceId);
        if(!$res){               
                return $this->apiResponse(200, 10001, 10001, $data = '', $message = '', $response["errors"], $appData);
            }else{                         
                return $this->apiResponse(200, 10000, 10000, $data = '', $message = '', "Service (".$res->service_order_id.") Deleted Successfully ", $appData);
        }
    }

// EO actionDeleteFullService()
    /**
     * Function for getting all the customer services
     */
    public function actionCustomerservices() {    
        $data = [];
        try {
            $serviceModel = new Services();
            $services = $serviceModel->getServicesbyCustomers();            
            $serviceData = array();
            foreach ($services as $row) {
                $serviceData[$row['customer_name']]['customer_id'] =$row['customer_id'];
                $serviceData[$row['customer_name']]['customer_name'] = $row['customer_name'];
                $interfaceData   =   $serviceModel->getVariableValue($row['service_model_id'], 'int_desc');
                $vrfData    =   $serviceModel->getVariableValue($row['service_model_id'], 'vrf_name');      
                $hostnames  =   $serviceModel->getDeviceHostNamesByInstanceID($row['id']);
                $diff   =   intval(strtotime($row['created_on']) - strtotime($row['provisioning_datetime']));
                $years   = floor($diff / (365*60*60*24)); 
                $months  = floor(($diff - $years * 365*60*60*24) / (30*60*60*24)); 
                $days    = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
                $hours   = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));    
                $serviceData[$row['customer_name']]['services'][] = array('service_id'  => $row['service_id'],
                                                  'service_name'    =>  $row['service_type'],                                                  
                                                  'service_instance_id'   =>    $row['service_instance_id'],
                                                  'provisioning_datetime'   => $row['provisioning_datetime'],
                                                  'service_type'    =>  $row['service_type'],
                                                  'type_of_change'  =>  $row['type_of_change'],
                                                  'total_time_of_provision' => $hours." hours",
                                                  'provisioning_status' =>  $row['provisioning_status'],
                                                  'hostnames'   =>  $hostnames,                    
                                                  'interface'   =>  $interfaceData,
                                                  'vrf'         =>  $vrfData,												  
                                                  'detailed_configuration'  =>  $row['detailed_configuration'],
                        );  
            }
            //$data['services'] = $serviceData;   
            $data   =   $serviceData;
            return $this->apiResponse(200, 2009, 2010, $data, $message = "Service Data retrieval successfully");
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            return $this->apiResponse(200, 300, 3000, $data, $message = "", $error);
        }
    }
    
}
