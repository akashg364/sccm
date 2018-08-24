<?php

namespace app\modules\versiSatu\controllers;

use backend\models\Customer;
use backend\models\Device;
use backend\models\DeviceRole;
use backend\models\ResourceManager;
use backend\models\ServiceInstance;
use backend\models\ServiceInstanceDevices;
use backend\models\ServiceModel;
use backend\models\ServiceModelTemplate;
use backend\models\SubServices;
use common\components\Nso;
use backend\models\ServiceInstanceLogs;
use common\components\SccmCommonFunction;
use Yii;

Class ServicedeployController extends \app\components\Controller {

    /**
     * Sample Request String
     * $str_request = 
     * '{
     *   "service_order_id": "987",
     *   "customer_id": 1234,
     *   "sub_service_id": 2.37,
     *   "endpoint_device_count": 2,
     *   "app_data":""
     * }';
     */
	
    public function actionStep1() {

        $request = Yii::$app->request->getBodyParams();      
        $appData = "";
        
        try {
            if (!isset($request['endpoint_device_count']) || empty($request['endpoint_device_count'])) {
                $request['endpoint_device_count'] = 1;
            }

            if (isset($request['app_data'])) {
                $appData = $request['app_data'];
            }

            /**
             * API Request body input data missing. Required data is Missing from API body.
             */
            if (empty($request)) {
                return $this->apiResponse(200, 6000, 6001, $data = '', $message = '', "data missing.", $appData);
            }

            /**
             * API Error response when Sub Service or Service Flavor not found.
             */
            $sub_service_id = $request['sub_service_id'];
            $subService = SubServices::find()->where(["ref_id" => $sub_service_id])->one();
            if (!$subService) {
                return $this->apiResponse(200, 6000, 6001, $data = '', $message = '', "Sub service not found", $appData);
            }

            /**
             * API Error response when Service Model not found.
             */
            $ServiceModel = ServiceModel::find()->where(['sub_service_id' => $subService->id,"is_deleted"=>0])->one();
            if (!$ServiceModel) {
                return $this->apiResponse(200, 6000, 6001, $data = '', $message = '', "Service Model not found", $appData);
            }

            $service_model_id = $ServiceModel->id;
            $service_id = $ServiceModel->service_id;

            /**
             * API Error when customer is not valid or does not exist.
             */
            if (empty($request['customer_id'])) {
                return $this->apiResponse(200, 6000, 6001, $data = '', $message = '', "Customer Id Missing", $appData);
            }

            $CustomerModel = Customer::find()->where(['customer_id' => $request['customer_id']])->one();
            if ($CustomerModel) {
                $customer_id = $CustomerModel->id;
            } else {
                return $this->apiResponse(200, 6000, 6001, $data = '', $message = '', "Invalid Customer Id", $appData);
            }

            if (empty($request['endpoint_device_count'])) {
                return $this->apiResponse(200, 6000, 6001, $data = '', $message = '', "Enpoints cannot be empty", $appData);
            }
            if (empty($request['service_order_id'])) {
                return $this->apiResponse(200, 6000, 6001, $data = '', $message = '', "service_order_id cannot be empty", $appData);
            }

            // Create Service instance
            $ServiceInstanceModel = new ServiceInstance();
            $ServiceInstanceModel->service_order_id = $request['service_order_id'];
            $ServiceInstanceModel->customer_id = $customer_id;
            $ServiceInstanceModel->service_model_id = $service_model_id;
            $ServiceInstanceModel->endpoints = $request['endpoint_device_count'];
            $ServiceInstanceModel->created_by = 1;
            $ServiceInstanceModel->updated_by = 1;

            if ($ServiceInstanceModel->save()) {
                $transaction_id = $ServiceInstanceModel->id;
                $data['transaction_id'] = $transaction_id;
                return $this->apiResponse(200, 2001, 2001, $data, "Service Invoke Create Step1 Success", $error = "", $appData);
            } else {
			
                /*if (isset($ServiceInstanceModel->errors['service_order_id'])) {
                    $error = implode(",", $ServiceInstanceModel->errors['service_order_id']);
                    return $this->apiResponse(409, 6000, 409, $data = '', $message = "$error", $appData);
                } else { */ // commented on 16th August
                    return $this->apiResponse(200, 6000, 6009, $data = '', $message = "", "Service Invoke Create Step1 Fail", $appData);
               // }
            }
        } catch (\Exception $e) {
            return $e->getMessage() . "  |  " . $e->getTraceAsString();
        }
    }

   /**
    *  Step 2 request Data Old
    *  Sample Data :
    *  $str_request = '{ "transaction_id": 1, "endpoint_devices":
    *  [ { "device_id": 567324763,
    *  "parameters": { "CFPE_vip_primary": "192.168.136.12",
    *  "CFPE_vip_secondary": "192.168.136.13",
    *  "CFPE_customer_vlan": 202,
    *  "CFPE_ip_address": "fdda:5cc1:23:5::1f",
    *  "CFPE_bandwidth": 500,
    *  "CFPE_qos_template_id": 25,
    *  "CFPE_asn": "6745",
    *  "DCFP_interface_number": "0/0/13",
    *  "DCFP_ip_address": "172.168.10.2",
    *  "DMAC_customer_vlan": 203,
    *  "DMAC_p_bit_value": 1,
    *  "DMAC_customer_name": "HSBC" } } ],
    *  "app_data": {} }';
    */

    public function actionStep2Old() {

        $request = Yii::$app->request->getBodyParams();
        $appData = "";

        if (isset($request['app_data'])) {
                $appData = $request['app_data'];
        }
        //get SAR and Demark and 2 ECR device
        //        $appData = array('sar' => array(array('id'=>1, 'hostname' => 'wewerer', 'ip' => '', 'interface' => 'Ten0/1')),
        //            'esr' => array(array('id'=>2, 'hostname' => 'wewerer', 'ip' => '', 'interface' => 'Ten0/1'),
        //                array('id'=>3, 'hostname' => 'wewerer', 'ip' => '', 'interface' => 'Ten0/1')),
        //            'demark' => array(array('id'=>4, 'hostname' => 'wewerer', 'ip' => '', 'interface' => 'Ten0/1')));

        if (empty($request)) {
                return $this->apiResponse(200, 7000, 7001, $data = '', $message = '', "Request body missing.", $appData);
        }

        // get Device details on the basis of ID
        $ids = array();
        foreach ($request['endpoint_devices'] as $endp) {
            $ids[] = $endp['device_id'];
        }

        if (YII_ENV != 'dev') {
            $getDeviceDetails = Yii::$app->inventoryApi->getDeviceDetailsById($ids);
        } else {
            $getDeviceDetails = Device::getDeviceById([
                "id" => $request['endpoint_devices'][0]['device_id'],
            ]);
        }

        // call invantory to get topology
        if (YII_ENV != 'dev') {
            $get_topology = Yii::$app->inventoryApi->getTopology([
                "hostname" => $getDeviceDetails['hostname'],
            ]);
        }

        $device_id = array();
        if (isset($appData['sar'])) {
            foreach ($appData['sar'] as $sar) {
                $device_id['PE-DC Facing'][] = $sar['id'];
                $inputDevices['PE-DC Facing'][$sar['id']] = $sar;
            }
        } else {
            $device_id['PE-DC Facing'][] = "1";
        }
        if (isset($appData['esr'])) {
            foreach ($appData['esr'] as $esr) {
                $device_id['PE-Customer Facing'][] = $esr['id'];
                $inputDevices['PE-Customer Facing'][$esr['id']] = $esr;
            }
        } else {
            $device_id['PE-Customer Facing'][1] = "1";
        }
        if (isset($appData['Demarc'])) {
            foreach ($appData['Demarc'] as $demarc) {
                $device_id['Demarc'][] = $demarc['id'];
                $inputDevices['Demarc'][$demarc['id']] = $demarc;
            }
        } else {
            $device_id['Demarc'][] = "3";
        }
        
        //print_r($device_id);exit;
        //$device_id['dc-facing-pe'] = "2";
        //$device_id['demarc'] = "3";
        # To get service instance data
        $transaction_id = $request['transaction_id'];

        if (!$transaction_id) {
            return $this->apiResponse(200, 7000, 7001, $data = '', $message = '', "Transaction id cannot be blank", $appData);
        }

        $ServiceInstanceModel = ServiceInstance::find()->where(['id' => $transaction_id])->one();
        if (!$ServiceInstanceModel) {
            return $this->apiResponse(200, 7000, 7001, $data = '', $message = '', "Transaction id not found in service instance", $appData);
        }

        $service_model_id = $ServiceInstanceModel->service_model_id;
        if (!isset($request['endpoint_devices']) || empty($request['endpoint_devices'])) {
            return $this->apiResponse(200, 7000, 7001, $data = '', $message = '', "Endpoints not found in requested data", $appData);
        }
        $endpoint_devices = $request['endpoint_devices'];
        foreach ($endpoint_devices as $ekey => $endpoint_device) {
            $variable = array();
            $device_role = array();
            $user_defined_data = $endpoint_device['parameters'];
            foreach ($user_defined_data as $ukey => $params) {
                // $device_role_name = explode("_", $key, 2);
                // $variable[$device_role_name[0]][$device_role_name[1]] = $val;
                // foreach ($device_id[$device_role_name[0]] as $row1) {
                //   $variable[$device_role_name[0]]['device'][] = $row1;
                // }
                // $device_role[] = $device_role_name[0];
                foreach ($params as $k => $v) {
                    $variable[$ukey][$k] = $v;
                    //$variable[$key]['device'][] = $device_id[$key];
                }
                $device_role[] = $ukey;
            }
                    
            $device_role = array_unique($device_role);
            $count = 0;
            $transaction = Yii::$app->db->beginTransaction();
            try {
                ## To get device role id
                $return = array();
                foreach ($device_role as $row) {
                    $deviceRoleModel = DeviceRole::find()->where(['role_name' => $row])->one();
                    if ($deviceRoleModel != '') {
                        $device_role_id = $deviceRoleModel->id;
                    } else {
                        $device_role_id = '';
                    }

                    // Find Template
                    $templateData = ServiceModelTemplate::find()->where([
                        'device_role_id' => $device_role_id,
                        'service_model_id' => $service_model_id,
                    ])->one();
                    if ($templateData != '') {
                        $template_id = $templateData->id;
                    } else {
                        $template_id = '';
                    }
                    //pe($variable);
                    $i = 0;
                    foreach ($device_id[$row] as $dev => $did) {
                        $ServiceInstanceDevicesModel = new ServiceInstanceDevices();
                        // $insert['service_instance_id'] = $transaction_id;
                        // $insert['template_id'] = $template_id;
                        // $insert['device_id'] = $dev;
                        // $insert['role_id'] = $device_role_id;
                        // $insert['user_defined_data'] = serialize($variable[$row]);
                        // calling function from resource manager to get system defined variables
                        // $insert['system_defined_data'] =   serialize($system_defined['value']);
                        $ServiceInstanceDevicesModel->service_instance_id = $transaction_id;
                        $ServiceInstanceDevicesModel->template_id = $template_id;
                        $ServiceInstanceDevicesModel->device_id = $did;
                        $ServiceInstanceDevicesModel->role_id = $device_role_id;
                        $ServiceInstanceDevicesModel->user_defined_data = serialize($variable[$row][$i]);
                        //$system_defined = ResourceManager::getSystemDefinedVariablesArray($device_role_id, $did, $service_model_id, $ServiceInstanceModel->customer_id, $transaction_id, $variable[$row]);
                        $system_defined = ResourceManager::getSystemDefinedVariablesArray($device_role_id, $did, $service_model_id, $ServiceInstanceModel->customer_id, $transaction_id, $inputDevices[$row][$did]);
                        if (!empty($system_defined['value'])) {
                            $systemDefined = $system_defined['value'];
                        }
                        $i++;
                        $ServiceInstanceDevicesModel->system_defined_data = serialize($systemDefined);
                        # vaidation
                        // if (isset($variable[$row]['vip-primary'])) {
                        //   $vip_primary = $variable[$row]['vip-primary'];
                        //   if (!filter_var($vip_primary, FILTER_VALIDATE_IP)) {
                        //      return $this->apiResponse(200, 7000, 7004, $data = '', $message = '', "$vip_primary is a Invalid vip-primary", $appData);
                        //   }
                        // }
                        // if (isset($variable[$row]['vip-secondary'])) {
                        //     $vip_secondary = $variable[$row]['vip-secondary'];
                        //     if (!filter_var($vip_secondary, FILTER_VALIDATE_IP)) {
                        //         return $this->apiResponse(200, 7000, 7004, $data = '', $message = '', "$vip_secondary is a Invalid vip-secondary", $appData);
                        //     }
                        // }
                        //
                        // $validateRes = ServiceDeploy::getValidateVariable($template_id, $variable[$row]);
                        //
                        // if (!$validateRes["success"]) {
                        //     return $this->apiResponse(200, 7000, 7004, $data = '', $message = '', $validateRes["error"], $appData);
                        // }
                        // $ServiceInstanceDevicesModel->user_defined_data = serialize($variable[$row]);

                        // $systemDefined = array();
                        // $systemValues = ResourceManager::getSystemDefinedArray($device_role_id, $dev, $service_model_id);

                        // if (!empty($systemValues['value'])) {
                        //     $systemDefined = $systemValues['value'];
                        // }
                        //
                        // $ServiceInstanceDevicesModel->system_defined_data = serialize($systemDefined);

                        $payload = $ServiceInstanceDevicesModel->nso_payload = ServiceInstance::setPayloadValues(array("template" => $template_id, "UserDefined" => $variable[$row], "SystemDefined" => $systemDefined));
                        if ($ServiceInstanceDevicesModel->save()) {
                                $return[$row] = "Inserted " . $ServiceInstanceDevicesModel->id;
                        } else {
                                $return[$row] = $ServiceInstanceDevicesModel->getErrors();
                                return $this->apiResponse(200, 7000, 7007, $data = '', $message = '', "User Input save failed", $appData);
                        }
                    }
                } // end for loop

                $transaction->commit();
            } catch (\Exception $e) {
                    $this->saveLog($logfile, $e);
                    $this->saveLog($logfile, "TRANSACTION ID : " . $request['transaction_id']);
                    $transaction->rollBack();
                    throw $e;
            }
        } // end transation
        return $this->apiResponse(200, 2002, 2002, array("transaction_id" => $transaction_id), "Service Invoke Create Step2 Success", $error = "", $appData);
    }

    /* Sample data format
        //{ "transaction_id": 76458765, "action":2, "datetime": "2018-03-22T12:38:00+05:30", â€œapp_dataâ€?:{} }
    */

    public function actionStep3() {
        
        $request = Yii::$app->request->getBodyParams(); 
        $appData = "";
        
        try {
            if (isset($request['app_data'])) {
                $appData = $request['app_data'];
            }

            if ($request == '') {
                return $this->apiResponse(200, 8000, 8001, $data = '', $message = '', "Data missing.", $appData);
            }

            $transaction_id = $request['transaction_id'];
            $ServiceInstance = ServiceInstance::find()->where(['id' => $transaction_id])->one();
            if (!$ServiceInstance) {
                return $this->apiResponse(200, 8000, 8004, $data = '', $message = '', "Invalid Transaction id. Service Instance not found", $appData);
            }
            $unique_id = $ServiceInstance->uniqueId;
            $inclusionHostname = $ServiceInstance->inclusion_hostname;
            if (!$inclusionHostname) {
                return $this->apiResponse(200, 8000, 8004, $data = '', $message = '', "Step 3 Service Invoke Failed", $appData);
            }
            if ($request['action'] == 1) {
                $ServiceInstance->scheduled_status = 'NOW';
                $ServiceInstance->scheduled_date = date("Y-m-d H:i:s");
            } else if ($request['action'] == 2) {
                $ServiceInstance->scheduled_status = 'SCHEDULE';
                $ServiceInstance->scheduled_date = date("Y-m-d H:i:s", strtotime($request['datetime']));
            } else if ($request['action'] == 3) {
                $ServiceInstance->scheduled_status = 'NEAR FUTURE';
                $ServiceInstance->scheduled_date = date("Y-m-d H:i:s", strtotime($request['datetime']));
            } else {
                $ServiceInstance->scheduled_status = 'NOW';
                $ServiceInstance->scheduled_date = date("Y-m-d H:i:s");
            }

            //$finalNsoPayload = ServiceInstance::getFinalNsoPayload($transaction_id);
            $finalNsoPayload = $ServiceInstance['final_nso_payload'];

            if (trim($finalNsoPayload) == '') {
                return $this->apiResponse(200, 8000, 8001, $data = '', $message = '', "Payload Required", $appData);
            }

            //$ServiceInstance->final_nso_payload = $finalNsoPayload;
            //$ServiceInstance->save();

            $service_model_id = $ServiceInstance->service_model_id;
            $ServiceModel = ServiceModel::find()->where(['id' => $service_model_id])->one();

            if ($ServiceModel != '') {
                $service_id = $ServiceModel->service_id;
                $service_name = $ServiceModel->name;
            } else {
                return $this->apiResponse(200, 8000, 8004, $data = '', $message = '', "Invalid Service Id", $appData);
            }
        } catch (\Exception $e) {
            return $e->getMessage() . "  |  " . $e->getTraceAsString();
        }

        //NSO Api CALL
        $payload = $finalNsoPayload;
        $service = $service_name;
       
        try {
            $res = "";
			$isDryRun = false;		

			if(isset($request['action']) &&  $request['action'] ==4){ // Dry Run
				
				$ServiceInstance->status = 0;  // No Process in Cron
				$ServiceInstance->action = 4; // Action Dry Run
				
				$ServiceInstance->save();
				$isDryRun = true;		
				$nso = new Nso();
				$res = $nso->pushToService($payload, $service, $isDryRun, $inclusionHostname);
				$resArray = json_decode($res, true);
				if (isset($resArray["errors"]["error"])) {
					$response = $this->apiResponse(200, 7001, 7001, array("transaction_id" => $transaction_id), "", $error = $resArray["errors"]["error"], $appData);
					//$this->saveApiLog($request_api_url, $request_api_name, $request_api_str, $response, $service_template_id, 'POST');
					return $response;
				} 
				  
			}		
			else if(isset($request['action']) &&  $request['action'] ==1){  // Commit Queue
				$ServiceInstance->status = 1; // Ready To Proccess
				$ServiceInstance->action = 1; // Commit Action
				$ServiceInstance->save();
			}else if(isset($request['action']) &&  in_array($request['action'],[2,3])){  // Commit Queue for Schedule 
				$ServiceInstance->status = 1;// Ready To Proccess
				$ServiceInstance->action = $request['action'];
				$ServiceInstance->save();
			}else{
				$ServiceInstance->status = 0;// No Proccess
				$ServiceInstance->action = $request['action'];
				$ServiceInstance->save();
			}


            # for unique id
            $customer_id = $ServiceInstance->customer_id;
            $service_instance_id = $ServiceInstance->id;
            /* $ServiceInstanceDevices = ServiceInstanceDevices::find()->where(['service_instance_id' => $service_instance_id])->one();
                if ($ServiceInstanceDevices != '') {
                    $device_id = $ServiceInstanceDevices->device_id;
                } else {
                    $device_id = '';
                }
                $port_id = "";
            */
            //$unique_id = $customer_id . "/".$service_id . "/" . $device_id . "/" . $port_id;
            //$unique_id = $customer_id . "/".$service_id."/".$service_instance_id ;

            /*if(strpos("error",$res)!==false) {
                return $this->apiResponse(200, 7001, 7001, array("transaction_id" => $transaction_id), "", $error = "Service Deploy Failed : \n$res", $appData);
            } else {
            */
            $data = [
                "unique_id" => $unique_id,
                "payload"   => json_decode($finalNsoPayload),
                "dry_run"   => $res,
            ];
			
			
            
            return $this->apiResponse(200, 2000, 2000, $data, "Service Invoke Create Step3 Success", $error = "", $appData);
            /* }
            } else {
            return $this->apiResponse(200, 8000, 8004, $data = '', $message = '', "Deploy Failed", $appData);
            */
        } catch (\Exception $e) {
            return $this->apiResponse(200, 7001, 7001, array("transaction_id" => $transaction_id), "", $error = "Service Deplay Failed", $appData);
        }
    }

   /**
    *   Step 2 request Data
    *   {
    *       "transaction_id": 0,
    *       "endpoint_devices": [{
    *           "device_id": [],
    *           "parameters": {}
    *       }]
    *   }
    */

    public function actionStep2() {

        $request = Yii::$app->request->getBodyParams(); 
        
        //$logfile = "Step-2" . time();
        //$this->saveLog($logfile, $request);
        //$this->saveLog("Step 2 ", "TRANSACTION ID : " . @$request['transaction_id']);
        //$this->saveLog("Step 2 Request by MANO", $request);

        $uniqueCustomertId = $uniquesubServiceId = $uniqueDemarkId = $uniqueport = $appData = "";
        
        try {
            if (isset($request['app_data'])) {
                $appData = $request['app_data'];
            }

            if (empty($request)) {
                return $this->apiResponse(200, 7000, 7001, $data = '', $message = '', "Request body missing.", $appData);
            }

            $transaction_id = $request['transaction_id'];
            if (!$transaction_id) {
                return $this->apiResponse(200, 7000, 7001, $data = '', $message = '', "Transaction id cannot be blank", $appData);
            }

            $parameters = isset($request['endpoint_devices'][0]['parameters']) ? $request['endpoint_devices'][0]['parameters'] : [];
            if (empty($parameters)) {
                return $this->apiResponse(200, 7000, 7001, $data = '', $message = '', "Parameters cannot be blank", $appData);
            }

            // $port = isset($request['port']) ? $request['port']:"";
            // if (empty($port)) {
            //    return $this->apiResponse(200, 7000, 7001, $data = '', $message = '', "Port cannot be blank", $appData);
            // }
            // Each Varilables Empty Validation
            $emptyParameters = [];
            if (is_array($parameters)) {
                foreach ($parameters as $parameter => $value) {
                    if (empty(trim($value))) {
                        $emptyParameters[$parameter] = $parameter . " cannot be blank";
                    }
                    // For Unique ID
                    if ($parameter == "demarc-interface-id-1" || $parameter == "interface-id-1"
                        || $parameter == "demarc-interface-id-2" || $parameter == "interface-id-2") {
                        $uniqueport .= $value;
                    }
                }

                if (!empty($emptyParameters)) {
                    return $this->apiResponse(200, 7004, 7004, $data = '', $message = '', $emptyParameters, $appData);
                }
            }

            $ServiceInstanceModel = ServiceInstance::find()->alias("si")
            //	->with(["serviceModel.serviceModelOneTemplate"])
					->joinWith(["customer"])
                    ->where(['si.id' => $transaction_id])->one();

            if (!$ServiceInstanceModel) {
                return $this->apiResponse(200, 7000, 7001, $data = '', $message = '', "Transaction id not found in service instance", $appData);
            }    

            //For unique id
            $ServiceModel = ServiceModel::find()->where(['id' => $ServiceInstanceModel->service_model_id])->one();
            $SubServiceModel = SubServices::find()->where(['id' => $ServiceModel->sub_service_id])->one();
            $uniquesubServiceId = $SubServiceModel->ref_id;

            // Generate Unique Id  = $uniqueCustomertId + $uniquesubServiceId = $uniqueDemarkId = $uniqueport;

            //FOr Unique ID
            $uniqueCustomertId = $ServiceInstanceModel->customer->customer_id;
            $ServiceInstanceModel->user_defined_data = json_encode($parameters, JSON_UNESCAPED_SLASHES);
            $ServiceInstanceModel->save();

            // if(isset($ServiceInstanceModel->serviceModel->serviceModelOneTemplate) && !empty($ServiceInstanceModel->serviceModel->serviceModelOneTemplate)){
            // $template_id = $ServiceInstanceModel->serviceModel->serviceModelOneTemplate->id;
            // $validateRes = ServiceDeploy::getValidateVariable($template_id, array_keys($parameters));
            // if (!$validateRes["success"]) {
            // return $this->apiResponse(200, 7000, 7004, $data = '', $message = '', $validateRes["error"], $appData);
            // }
            // }

            // get Device details on the basis of ID
            $devices = array();
            // TODO remove once logic developed only hub config

            if (!is_array($request['endpoint_devices'][0]['device_id'])) {

                $uniqueDemarkId = $request['endpoint_devices'][0]['device_id'];
                if ($request['endpoint_devices'][0]['device_id'] == "1031") {
                    $demark['11']['demark-hostname'] = $demark['1031']['demark-hostname'] = "MUMBRLB1EDS017";
                    $demark['11']['perent1'] = $demark['1031']['perent1'] = Device::getDeviceByHostname("BGLRRLABESR001");
                    $demark['11']['perent2'] = $demark['1031']['perent2'] = Device::getDeviceByHostname("BGLRRLABESR002");
                    $ids[] = $devices[]['device'] = $demark[$request['endpoint_devices'][0]['device_id']]['perent1']['id'];
                    $ids[] = $devices[]['device'] = $demark[$request['endpoint_devices'][0]['device_id']]['perent2']['id'];
                    $request['endpoint_devices'][0]['parameters']['demarc-device'] = $demark[$request['endpoint_devices'][0]['device_id']]['demark-hostname'];
                } else {

                    $demark['12']['demark-hostname'] = $demark['1032']['demark-hostname'] = "MUMBRLB1EDS002";
                    $demark['12']['perent1'] = $demark['1032']['perent1'] = Device::getDeviceByHostname("MUMBRLB1ECR001");
                    $demark['12']['perent2'] = $demark['1032']['perent2'] = Device::getDeviceByHostname("MUMBRLB1ECR003");
                    $ids[] = $devices[]['device'] = $demark[$request['endpoint_devices'][0]['device_id']]['perent1']['id'];
                    $ids[] = $devices[]['device'] = $demark[$request['endpoint_devices'][0]['device_id']]['perent2']['id'];
//		   print_r($devices);exit;		
                    $request['endpoint_devices'][0]['parameters']['demarc-device'] = $demark[$request['endpoint_devices'][0]['device_id']]['demark-hostname'];
                }
            } else {
                foreach ($request['endpoint_devices'][0]['device_id'] as $endp) {
                    $ids[] = $endp;
                    $input['id'] = $endp;
                    $getDeviceDetails = Yii::$app->inventoryApi->getDeviceDetailsById($input);
                    $hostname = $getDeviceDetails['devices'][0][0]['hostname'];
                    $deviceArr = Device::getDeviceByHostname($hostname);
                    $devices[]['device'] = $deviceArr['id'];
                }
            }

            if (empty($ids)) {
                return $this->apiResponse(200, 7000, 7001, $data = '', $message = '', "Device ids cannot be blank", $appData);
            }
        } catch (\Exception $e) {
            return $e->getMessage() . "  |  " . $e->getTraceAsString();
        }

        $transaction = Yii::$app->db->beginTransaction();
        $error = "";

        try {
			//$uniqueIdGenerate = $uniqueCustomertId . $uniquesubServiceId . $uniqueDemarkId . $parameters['demarc-ingress-port-no'].$finalPayload['vpn-id'];			
			$uniqueIdGenerate = $uniqueCustomertId . $uniquesubServiceId . $uniqueDemarkId . $parameters['demarc-ingress-port-no'].$ServiceInstanceModel->service_order_id;
            $payload = new \common\components\PayLoadFrontend();
            $serviceInstance = $payload->getServiceInstance($service_instance_id = $ServiceInstanceModel);
            $finalPayload = $payload->createPayload($transaction_id, $devices, $parameters,$uniqueIdGenerate);
            $this->saveLog("Step 2 Create payload", $finalPayload);

            if (!$finalPayload) {
                //$error = implode(',',$payload->errors);
                return $this->apiResponse(200, 7001, 7001, array("transaction_id" => $transaction_id), "", $error = implode(',', $payload->errors), $appData);
            }
	
            //$uniqueIdGenerate = $uniqueCustomertId . $uniquesubServiceId . $uniqueDemarkId . $finalPayload['demarc-ingress-port-no'].$finalPayload['vpn-id']; //16th Aug
			
            $ServiceInstanceModel->uniqueId = $uniqueIdGenerate;
            $ServiceInstanceModel->final_nso_payload = json_encode($finalPayload['payload'], JSON_UNESCAPED_SLASHES);
            $res = $ServiceInstanceModel->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $this->apiResponse(200, 7001, 7001, array("transaction_id" => $transaction_id), "", $error = "Service Invoke Create Step2 Failed. ".$e->getMessage(), $appData);
        }
        return $this->apiResponse(200, 2002, 2002, array("transaction_id" => $transaction_id), "Service Invoke Create Step2 Success", $error = "", $appData);
    }

    public function saveLog($fname, $dataToStore) {
        $fileName = "/var/www/html/sccmlogs/";
        if (!is_dir($fileName)) {
            mkdir($fileName, 0777, TRUE);
        }

        $fileName .= $fname . ".txt";
        $msg = date('Y-m-d H:i:s') . ' ->' . PHP_EOL;
        if (is_array($dataToStore)) {
            $msg .= print_r($dataToStore,true);
            // foreach ($dataToStore as $key => $value) {
            // 	$msg .= $key . " = " . $value . "\n";
            // }
        }

        $msg .= PHP_EOL . PHP_EOL;
        file_put_contents($fileName, $msg, FILE_APPEND); // Store log
    }

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);

       /**
        * On Service Deploy Step 2 if any error occurs then remove service_instance and make log of it in server_instance_logs table
        */
        if($action->id == "step2" && isset($result["error"]) && !empty($result)) {
            $request = Yii::$app->request->getBodyParams();
            if(isset($request["transaction_id"]) && !empty($request["transaction_id"])) {
                $serviceInstance = ServiceInstance::findOne($request["transaction_id"]);
                if($serviceInstance) {
                    $serviceInstanceLogs = new ServiceInstanceLogs();
                    $serviceInstanceLogs->is_active = $serviceInstance->is_active;
                    $serviceInstanceLogs->attributes = $serviceInstance->attributes;
                    $serviceInstanceLogs->save();
                    $serviceInstance->delete();
                }
            }
        }
        return $result;
    }
}
