<?php

namespace app\modules\versiSatu\controllers;
use Yii;
use app\components\Controller;
use app\models\Sccmcrd;
use yii\helpers\Json;

class SubserviceController extends Controller {


	
	public function actionGetSubServiceId(){
		
		try {
			$model = Sccmcrd::find()
					 ->one();
			
			$deviceRoles = Json::decode($model->device_roles);
			
			$variables = ["SD"=>[],"UD"=>[]];
			foreach($deviceRoles as $role_name => $deviceRoleVariables){
				if(trim($role_name) == "PE-Customer Facing"){
						$role_name = "CFPE";
				}
				else if(trim($role_name) == "Demarc"){
						$role_name = "DMAC";
				}
				
				foreach($deviceRoleVariables as $roleVariable){
					
					$variables[$roleVariable["inputtype"]][] = $role_name."_".$roleVariable["variableId"];
				}
			}		
			
		
			$data["subservice"]	= [
				"sub_service_id"=>$model->service_id,
				"service_name"=> $model->service_subflavour,
				"system_define"=>$variables["SD"],
				"user_define"=>$variables["UD"],
			]; 
			
			return $this->apiResponse(200, 2009, 2009, $data, $message = "Service Data retrieval successfully");
		}catch (\Exception $ex) {
			$error = $ex->getMessage();
			return $this->apiResponse(200, 300, 300, $data, $message = "", $error);
		}
		return $data;
	}

        /*
	 *$params : service_id
	 * is_managed:
	 * terminated_at:
	 * routing_protocol: 
	 */
	public function actionGetSubServices($service_id,$is_managed,$terminated_at,$routing_protocol){
            $data = [];	
	
		try {
			
			$req = Yii::$app->request;
			$service_id 	= $req->get("service_id");
			$is_managed 	= $req->get("is_managed");
			$terminated_at 	= $req->get("terminated_at");
			$routing_protocol = $req->get("routing_protocol");
			$topology_type 	= $req->get("topology_type");
			$dual 	= $req->get("dual");
			$witheds 	= $req->get("witheds");
			
			$data = Sccmcrd::getSubService($service_id,$is_managed,$terminated_at,$routing_protocol,$dual,$witheds);
				//TODO - remove after logic build
				$remove_237 = array("erps-mode","egress-bridge-port-no","ingress-port-no",
				"address-family","cust-lan-pool","customer-lan-pool-ipv4","customer-lan-pool-ipv6");
				if($data['sub_service']['sub_service_id'] == '2.37' || $data['sub_service']['sub_service_id'] == '2.38'){
					$ud = array_diff($data['sub_service']['user_define'],$remove_237);
					$data['sub_service']['user_define'] = array_values($ud);
				}

			if($data){
				return $this->apiResponse(200, 2011, 2011, $data, $message = "Sub Service Data retrieval successfully");
			}else{
				return $this->apiResponse(200, 300, 300, $data, $message = "", $error="Sub service not found");
			}
			
			
		}catch (\Exception $ex) {
			$error = $ex->getMessage();
			return $this->apiResponse(200, 300, 3000, $data, $message = "", $error);
		}
	
	}
}