<?php

namespace api\controllers;

use Yii;
use backend\models\Customer;
use backend\models\DeviceRole;
use backend\models\ResourceManager;
use backend\models\ServiceInstance;
use backend\models\ServiceInstanceDevices;
use backend\models\ServiceModel;
use backend\models\ServiceModelTemplate;
use backend\models\SubServices;
use common\components\Nso;
use common\components\sccmCommonFunction;
use backend\models\ServiceDeploy;


class ServicesDeployController extends \yii\web\Controller {

	public function beforeAction($action) {
		$this->enableCsrfValidation = false;
		return parent::beforeAction($action);
	}

	// $str_request = '{"service_order_id": "987","customer_id": "43454534","sub_service_id": "1","endpoint_device_count": "2","app_data":""}';
	public function actionStep1() {

		$str_request = Yii::$app->request->post('api_data');
		if (empty(trim($str_request))) {
			$arr_str = [
				"status_code" => "300",
				"sub_status_code" => "3000",
				"message" => "data missing.",
				"data" => array(),
				"app_data" => array(),
			];
			return json_encode($arr_str);
		}

		$obj = json_decode($str_request);
		$app_data = $obj->app_data;
		$sub_service_id = $obj->sub_service_id;

		$subService = SubServices::find()->where(["ref_id"=>$sub_service_id])->one();
		if(!$subService){
			$response = [
				"status_code" => "300",
				"sub_status_code" => "3000",
				"message" => "Sub service not found",
				"data" => array(),
				"app_data" => array(),
			];
			return json_encode($response);
		}
		
		$ServiceModel = ServiceModel::find()->where(['sub_service_id' => $subService->id])->one();
		if (!$ServiceModel) {
			$arr_str = [
				"status_code" => "300",
				"sub_status_code" => "3000",
				"message" => "Service Model not found",
				"data" => array(),
				"app_data" => array(),
			];
			return json_encode($arr_str);
		}

		$service_model_id = $ServiceModel->id;
		$service_id = $ServiceModel->service_id;

		#check customer validation
		$CustomerModel = Customer::find()->where(['customer_id' => $obj->customer_id])->one();
		if ($CustomerModel != '') {
			$customer_id = $CustomerModel->id;
		} else {
			$arr_str = [
				"status_code" => "300",
				"sub_status_code" => "3000",
				"message" => "Customer Id Missing.",
				"data" => array(),
				"app_data" => array(),
			];
			return json_encode($arr_str);
		}
		###

		$ServiceInstanceModel = new ServiceInstance();
		$ServiceInstanceModel->name = $obj->customer_id . "-" . $obj->service_order_id . "-" . $service_id;
		$ServiceInstanceModel->service_order_id = $obj->service_order_id;
		$ServiceInstanceModel->customer_id = $customer_id;
		$ServiceInstanceModel->service_model_id = $service_model_id;
		$ServiceInstanceModel->endpoints = $obj->endpoint_device_count;
		$ServiceInstanceModel->created_by = 1;
		$ServiceInstanceModel->updated_by = 1;

		if ($ServiceInstanceModel->save()) {
			$transaction_id = $ServiceInstanceModel->id;
			$arr_str = array("status_code" => "200", "sub_status_code" => "2000", "message" => "Service Invoke Create Step1 Success",
				"data" => array("transaction_id" => $transaction_id),
				"app_data" => array());
		} else {
			// print_r($ServiceInstanceModel->getErrors());
			$arr_str = array("status_code" => "300", "sub_status_code" => "3000", "message" => "Service Invoke Create Step1 Fail",
				"data" => array(),
				"app_data" => array());

		}
		return json_encode($arr_str);
	} 



	/*
	Sample Data :
	$str_request = '{ "transaction_id": 1, "endpoint_devices":
					[ { "device_id": 567324763,
					"parameters": { "CFPE_vip_primary": "192.168.136.12",
							"CFPE_vip_secondary": "192.168.136.13",
							"CFPE_customer_vlan": 202,
							"CFPE_ip_address": "fdda:5cc1:23:5::1f",
							"CFPE_bandwidth": 500,
							"CFPE_qos_template_id": 25,
							"CFPE_asn": "6745",
							"DCFP_interface_number": "0/0/13",
							"DCFP_ip_address": "172.168.10.2",
							"DMAC_customer_vlan": 203,
							"DMAC_p_bit_value": 1,
							"DMAC_customer_name": "HSBC" } } ],
							"app_data": {} }';
	**/
	public function actionStep2() {

		$str_request = Yii::$app->request->post('api_data');
		if (trim($str_request) == '') {
			$response = [
							"status_code" => "300", 
							"sub_status_code" => "3000", 
							"message" => "data missing.",
							"data" => array(),
							"app_data" => array()
						];
			return  json_encode($response);
		}
		// call invantory to get topology
		$device_id = array();
		$device_id['customer-facing-pe'] = "1";
		$device_id['dc-facing-pe'] = "2";
		$device_id['demarc'] = "3";

		$obj = json_decode($str_request, true);
		# To get service instance data
		$transaction_id = $obj['transaction_id'];
		$ServiceInstanceModel = ServiceInstance::find()->where(['id' => $transaction_id])->one();
		if (!$ServiceInstanceModel) {
			$response = [
							"status_code" => "300", 
							"sub_status_code" => "3000", 
							"message" => "Transaction id not found in service instance",
							"data" => array(),
							"app_data" => array()
						];
			return  json_encode($response);
		} 

		$service_model_id = $ServiceInstanceModel->service_model_id;
		$transaction_id = $obj['transaction_id'];

		if(!isset($obj['endpoint_devices']) || empty($obj['endpoint_devices'])){
			$response = [
							"status_code" => "300", 
							"sub_status_code" => "3000", 
							"message" => "Endpoints not found in requested data",
							"data" => array(),
							"app_data" => array()
						];
			return  json_encode($response);

		}
		$endpoint_devices  = $obj['endpoint_devices'];

		foreach ($endpoint_devices as $key => $endpoint_device) {

			$variable = array();
			$device_role = array();
			$user_defined_data = $endpoint_device['parameters'];

			foreach ($user_defined_data as $key => $val) {
				$device_role_name = explode("_", $key, 2);
				$variable[$device_role_name[0]][$device_role_name[1]] = $val;
				$variable[$device_role_name[0]]['device'] = $device_id[$device_role_name[0]];
				$device_role[] = $device_role_name[0];
			}
			$device_role = array_unique($device_role);

			
		$transaction = Yii::$app->db->beginTransaction();
		
			try {	
				## To get device role id
				$return = array();
				foreach ($device_role as $row) {
					$ServiceInstanceDevicesModel = new ServiceInstanceDevices();
					$deviceRoleModel = DeviceRole::find()->where(['role_name' => $row])->one();
					if ($deviceRoleModel != '') {
						$device_role_id = $deviceRoleModel->id;
					} else {
						$device_role_id = '';
					}
					
					// Find Template 
					$data = ServiceModelTemplate::find()->where([
						'device_role_id' => $device_role_id, 
						'service_model_id' => $service_model_id
					])->one();

					if ($data != '') {
						$template_id = $data->id;
					} else {
						$template_id = '';
					}

					$insert['service_instance_id'] = $transaction_id;
					$insert['template_id'] = $template_id;
					$insert['device_id'] = $device_id[$row];
					$insert['role_id'] = $device_role_id;
					$insert['user_defined_data'] = serialize($variable[$row]);

					//print_r($variable[$row]);
					//exit;

					$ServiceInstanceDevicesModel->service_instance_id = $transaction_id;
					$ServiceInstanceDevicesModel->template_id = $template_id;
					$ServiceInstanceDevicesModel->device_id = $device_id[$row];
					$ServiceInstanceDevicesModel->role_id = $device_role_id;
					
					# vaidation 
					if (isset($variable[$row]['vip-primary'])) {
						$vip_primary = $variable[$row]['vip-primary'];
							if (!filter_var($vip_primary, FILTER_VALIDATE_IP)) {
								echo("$vip_primary is a In valid vip-primary");
								exit;
							} 
					}
					if (isset($variable[$row]['vip-secondary'])) {
						$vip_secondary = $variable[$row]['vip-secondary'];
							if (!filter_var($vip_secondary, FILTER_VALIDATE_IP)) {
								echo("$vip_secondary is a In valid IP address");
								exit;
							} 
					}
					
					$validateRes = ServiceDeploy::getValidateVariable($template_id, $variable[$row]);
					
					if(!$validateRes["success"]) {
						$response = array(
								"status_code" => "300", 
								"sub_status_code" => "3000", 
								"message" => $validateRes["error"],
								"data" => [],
								"app_data" => array());
							return json_encode($response, JSON_UNESCAPED_SLASHES);
					}
				  
					
					$ServiceInstanceDevicesModel->user_defined_data = serialize($variable[$row]);
					$systemDefined = array();
					$systemValues = ResourceManager::getSystemDefinedArray($device_role_id, $device_id[$row], $service_model_id);

					if (!empty($systemValues['value'])) {
						$systemDefined = $systemValues['value'];
					}

					$ServiceInstanceDevicesModel->system_defined_data = serialize($systemDefined);

					$payload = $ServiceInstanceDevicesModel->nso_payload = ServiceInstance::setPayloadValues(array("template" => $template_id, "UserDefined" => $variable[$row], "SystemDefined" => $systemDefined));
					
					if ($ServiceInstanceDevicesModel->save()) {

						$return[$row] = "Inserted " . $ServiceInstanceDevicesModel->id;
					} else {
						$return[$row] = $ServiceInstanceDevicesModel->getErrors();
						$arr_str = array("status_code" => "300", 
								"sub_status_code" => "3000", 
								"message" => "User Input save failed",
								"data" => array(""),
								"app_data" => array());
							return json_encode($arr_str);
					}
				} // end for loop

			$transaction->commit();
			} catch (\Exception $e) {
					$transaction->rollBack();
					throw $e;
			}
		} // end transation
		
		$arr_str = array("status_code" => "200", 
			"sub_status_code" => "2000", 
			"message" => "User Input Saved Successfully",
			"data" => array("transaction_id" => $transaction_id),
			"app_data" => array());
			
		return json_encode($arr_str);
	} 

    /* Sample data format 
	//{ "transaction_id": 76458765, "action":2, "datetime": "2018-03-22T12:38:00+05:30", “app_data”:{} }
	**/
    public function actionStep3() {
        ini_set("display_errors", "1");
        error_reporting(E_ALL);

        $str_request = Yii::$app->request->post('api_data');
        if (trim($str_request) == '') {
            $arr_str = [
                "status_code" => "300",
                "sub_status_code" => "3000",
                "message" => "data missing.",
                "data" => array(),
                "app_data" => array(),
            ];
            return json_encode($arr_str);
        }
        $obj = json_decode($str_request);

        $transaction_id = $obj->transaction_id;
        $ServiceInstance = ServiceInstance::find()->where(['id' => $transaction_id])->one();
        if (!$ServiceInstance) {
            $response = [
                "status_code" => "300", 
                "sub_status_code" => "3000", 
                "message" => "Invalid Transaction id. Service Instance not found",
                "data" => array(),
                "app_data" => array()
            ];
            return  json_encode($response);
        }

        if ($obj->action = 1) {
            $ServiceInstance->scheduled_status = 'NOW';
        } else if ($obj->action = 2) {
            $ServiceInstance->scheduled_status = 'SCHEDULE';
        } else if ($obj->action = 3) {
            $ServiceInstance->scheduled_status = 'NEAR FUTURE';
        } else {
            $ServiceInstance->scheduled_status = 'NOW';
        }

        $ServiceInstance->scheduled_date = date("Y-m-d H:i:s", strtotime($obj->datetime));
        //$ServiceInstance->is_active = '1';
        $finalNsoPayload = ServiceInstance::getFinalNsoPayload($transaction_id);

        #
        if (trim($finalNsoPayload) == '') {
            $arr_str = array("status_code" => "300", "sub_status_code" => "3000", "message" => "Payload Required",
                            "data" => array(),
                            "app_data" => array());
            return json_encode($arr_str);
        }
        ##

        $ServiceInstance->final_nso_payload = $finalNsoPayload;
        $ServiceInstance->save();
        
        $service_model_id   = $ServiceInstance->service_model_id; 
        $ServiceModel       = ServiceModel::find()->where(['id' => $service_model_id])->one();

        if ($ServiceModel != '') {
            $service_id = $ServiceModel->service_id;
            $service_name = $ServiceModel->name;
        } else {
            $service_id = '';
            $arr_str = array("status_code" => "300", "sub_status_code" => "3000", "message" => "Invalid Service Id",
            "data" => array(),
            "app_data" => array());

            return json_encode($arr_str,JSON_UNESCAPED_SLASHES);
        }

        //NSO Api CALL
        $payload = $finalNsoPayload;
        //$service = 'sip-trunk';
        $service = $service_name;
        $isDryRun = true;

        /*
        $nso = new Nso();
        $res = $nso->pushToService($payload, $service, $isDryRun);
        print_r($res);
        return $res;
        ***/
        if ($ServiceInstance->save()) {
            # for unique id 
            $customer_id            = $ServiceInstance->customer_id;
            $customer_id            = $ServiceInstance->customer_id;
            $service_instance_id    = $ServiceInstance->id;
            $ServiceInstanceDevices = ServiceInstanceDevices::find()->where(['service_instance_id' => $service_instance_id])->one();

            if ($ServiceInstanceDevices != '') {
                $device_id = $ServiceInstanceDevices->device_id;
            } else {
                $device_id = '';
            }
            $port_id = "";

            $unique_id = $customer_id. $service_id. "/".$device_id. "/".$port_id;
            $arr_str = array("status_code" => "200", 
                            "sub_status_code" => "2000", 
                            "message" => "Deployed Successfully",
                            "data" => array("unique_id" => $unique_id),
                            "app_data" => array());
        } else {
            print_r($ServiceInstance->getErrors());
            $arr_str = array("status_code" => "300", "sub_status_code" => "3000", "message" => "Deploy Failed",
                    "data" => array(),
                    "app_data" => array());
        }
        return json_encode($arr_str,JSON_UNESCAPED_SLASHES);
    } // EO actionDeploy3()=
}
