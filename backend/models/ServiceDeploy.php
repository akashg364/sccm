<?php
namespace backend\models;

use common\components\Nso;
use Yii;
use yii\base\Model;

class ServiceDeploy extends Model {

	public static function getValidateVariable($template_id, $user_input_variables) {

		//$template_id=1;

		$arr_variable_backend = array();
		$missingVariables = array();

		if (empty($template_id)) {
			return [
				"success" => false,
				"error" => "Template id is blank.",
			];
		}

		if (!empty($template_id)) {
			$sql = "SELECT variable_name
				    FROM tbl_service_model_template_variables mtv
					LEFT JOIN tbl_variables_master vm ON mtv.resource_manager_id = vm.id
					WHERE template_id = $template_id AND vm.type='user'";

			$results = \Yii::$app->db->createCommand($sql)->queryAll();

			$arr_variable_backend = array_column($results, 'variable_name');

			if (count($arr_variable_backend) != count($user_input_variables)) {
				return [
					"success" => false,
					"error" => "Required variable count mismatch",
				];
			}

			foreach ($arr_variable_backend as $key => $val) {
				if (!in_array($val, $user_input_variables)) {
					$missingVariables[] = $val;
				}
			}

			if (!empty($missingVariables)) {
				return [
					"success" => false,
					"error" => "This Variables Must required : " . implode(",", $missingVariables),
				];
			}
		}

		return [
			"success" => true,
		];
	}

	/*
		Reference : duplicate from swagger api
		Author : Jitendra
		Date : 3rdMay, 2018
		@params $transaction_id : service_instance_id
	*/
	public static function ServiceInvokeStep3($transaction_id, $action = '', $scheduled_date = '') {

		if (trim($transaction_id) == '') {
			echo "Invalid transaction id";
			exit;
		}

		$ServiceInstance = ServiceInstance::find()->where(['id' => $transaction_id])->one();
		if (!$ServiceInstance) {
			return "Invalid Transaction id. Service Instance not found";
		}

		if ($action == 1) {
			$ServiceInstance->scheduled_status = 'NOW';
			$ServiceInstance->scheduled_date = date("Y-m-d H:i:s");
		} else if ($action == 2) {
			$ServiceInstance->scheduled_status = 'SCHEDULE';
			$ServiceInstance->scheduled_date = date("Y-m-d H:i:s", strtotime($scheduled_date));
		} else if ($action == 3) {
			$ServiceInstance->scheduled_status = 'NEAR FUTURE';
			$ServiceInstance->scheduled_date = date("Y-m-d H:i:s", strtotime($scheduled_date));
		} else {
			$ServiceInstance->scheduled_status = 'NOW';
			$ServiceInstance->scheduled_date = date("Y-m-d H:i:s");
		}

		$finalNsoPayload = $ServiceInstance['final_nso_payload'];
		if (trim($finalNsoPayload) == '') {
			//return $this->apiResponse(200, 8000, 8001, $data = '', $message = '', "Payload Required", $appData);
			return "Payload Required";
		}

		//$ServiceInstance->final_nso_payload = $finalNsoPayload;
		//$ServiceInstance->save();

		//$service_model_id = $ServiceInstance->service_model_id;
		$ServiceModel = ServiceModel::find()->where(['id' => $ServiceInstance->service_model_id])->one();
		if (!empty($ServiceModel)) {
			$service_id = $ServiceModel->service_id;
			$service_name = $ServiceModel->name;
		} else {
			//return $this->apiResponse(200, 8000, 8004, $data = '', $message = '', "Invalid Service Id", $appData);
			return "Invalid Service Id";
		}

		//NSO Api CALL
		//$service = 'sip-trunk';
		$service = $service_name;
		$isDryRun = true;

		$nso = new Nso();
		$res = $nso->pushToService($finalNsoPayload, $service, $isDryRun);

		return $res;

		if ($ServiceInstance->save()) {

			# for unique id
			$customer_id = $ServiceInstance->customer_id;

			$service_instance_id = $ServiceInstance->id;
			$ServiceInstanceDevices = ServiceInstanceDevices::find()->where(['service_instance_id' => $service_instance_id])->one();

			if ($ServiceInstanceDevices != '') {
				$device_id = $ServiceInstanceDevices->device_id;
			} else {
				$device_id = '';
			}
			$port_id = "";

			$unique_id = $customer_id . $service_id . "/" . $device_id . "/" . $port_id;
			//return $this->apiResponse(200, 2000, 2000, array("unique_id" => $unique_id), "Service Invoke Create Step3 Success", $error = "", $appData);
			return "Service Invoke Create Step3 Success";
		} else {
			//return $this->apiResponse(200, 8000, 8004, $data = '', $message = '', "Deploy Failed", $appData);
			return "Deploy Failed";
		}

	} // EO ServiceInvokeStep3()

	/*********************************************************
     * @Desc : Nso Commit Queue is workaround for lab test as
	 * as we don't have NSO commit queue. After get Commit queue from
	 * NSO tema will remove this functionality
	 * Status : 0 = No Process, 1 = Ready To Process, 2 = In Process for CRON,3 = Completed
	 * Action : 1 = Commit, 2=Schedule, 3= Near Future,4=Dry Run	 
	 */
	public function commitQueue($id="") {
		// $transaction = Yii::$app->db->beginTransaction();
		//try {
			
			if(!empty($id)){
					$serviceInstance = ServiceInstance::find()->alias("si")->joinWith(["serviceModel"])->where(["si.id" => $id])->limit(1)->one();
			}else{
				// Action 1 = Commit , 2 = Schedule,3 = Schedule Near Future
				$serviceInstance = ServiceInstance::find()->alias("si")
					->joinWith(["serviceModel"])
					->where(["status" => '1','action'=>[1,2,3]])
					->limit(1)
					->orderBy(["id"=>SORT_ASC])->one();
			}
			
			if (!$serviceInstance) {
				$this->addError("err", "There is no pending queues found");
				return false;
			}
			
			echo "Commiting for service instance id => ".$serviceInstance->id." \n\n";
			
			// Status Changed to In Process for CRON for push to NSO API
			$serviceInstance->status = '2';
			$serviceInstance->save();

		
			$payload = $serviceInstance->final_nso_payload;
			$isDryRun = false;
			$service = $serviceInstance->serviceModel->name;
			$inclusionHostname = $serviceInstance->inclusion_hostname;

			//$payload = str_replace("UNI:ENT:NV::RILHDFC1:23:54537371hdfcvpn001","UUNI:ENT:NV::RILHDFC1:23:54537371hdfcvpn001",$payload);
			// NSO Api Call
			$nso = new Nso();
			$res="";
			$res = $nso->pushToService($payload, $service, $isDryRun, $inclusionHostname);
			
			// Save response in db

			$serviceInstance->remarks = $res;
			$serviceInstance->status = '3'; // Status chagned to Completed
			$serviceInstance->save();
			
			echo "NSO Response : : \n";
			print_r($res);
			
			$orderID = $serviceInstance->service_order_id;
			$expUID = explode(".",$serviceInstance->uniqueId);
			$deviceId = substr(end($expUID),2,4);
			$manoData = "";
			if(empty($res)){
				$manoData = "{
					'service_order_id' : $orderID,
					'status_code' : 2000,
					'sucess' :[$deviceId], 
					'fail' :[], 
					'data' : {}
					}";
			}else{
				$manoData = "{
					'service_order_id' : $orderID,
					'status_code' : 2000,
					'sucess' :[], 
					'fail' :[$deviceId], 
					'data' : {}
					}";		
	
			}			

			
			echo $manoData;
			$device_id = substr(end($expUID),2,4) ;
			$res = $this->curl("http://10.64.222.42:8192/PEAG/sbHandler/", 'POST', $manoData);
			
			 /* $transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		} */
	}

	public function curl($url, $requestType = 'GET', $data = '', $headers = array(), $xmldata = FALSE) {

        $ch = curl_init($url);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestType);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		 curl_setopt($ch, CURLOPT_HEADER, 0); 
		  curl_setopt($ch, CURLOPT_POST, 1); 

        if ($xmldata == TRUE) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
        }

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
	curl_close($ch);
	print "curl response is:" . $result;
	print_r($info);
        return $result;
    }

}
