<?php
namespace common\components\services;

use backend\models\Device;
use backend\models\Ipv6PoolAssignment;
use backend\models\ResourceManager;
use backend\models\ServiceInstance;
use backend\models\VariablesMaster;
use common\components\Ipv4Assignment;
use yii\helpers\ArrayHelper;

/*
 * @Desc : Build Payload
 * @Author : Nikhil Vikhe & Yatin Mistry
 * AAR = AG2
 * PAR = AG1
 * CSS/ESR or ECR
 *
 */
Class Payload extends \common\components\PayLoadFrontend{

	public $errors = [];
	public $data = [];
	public $variables = [];

	public function createPayload($service_instance_id, $devices, $userDefined,$uniqueIdGenerate) {
		
		$userDefined = $this->setDemarcUserDefinedVariablesLogic($userDefined);
		$userDefined = $this->setIpTypeForLab($userDefined);		
		$userDefined['cust-lan-pool'] = $userDefined['lan-pool-ipv4-mask']; // newly added
		$this->data["userDefined"] = $userDefined;

		$serviceInstance = $this->getServiceInstance($service_instance_id);
		if (!$serviceInstance) {
			return false;
		}
		
		$this->data["components"] = $serviceInstance["components"];
		$this->data['customer_id'] = $serviceInstance["customer_id"];


		//Todo::remove for now add static customer for lab test
		$serviceInstance["customer_id"] = 1;
		$serviceInstance["customer"]["id"] = 1;

		$customer = $serviceInstance["customer"];
		
		// logic for storing the SD variables
		$modelVariables = $serviceInstance["serviceModel"]["serviceModelTemplate"][0]['serviceModelTemplateVariables'];
		$systemVariables = array();
		foreach ($modelVariables as $var) {
			if ($var['variable']['type'] == 'system') {
				$systemVariables[$var['variable']['id']] = $var['variable']['variable_name'];
			}
		}

		$this->variables['sd_key'] = $systemVariables;
		$this->variables['sd_val'] = array_flip($systemVariables);

		$subService = $serviceInstance["serviceModel"]["subService"];
		$sub_service_id = $subService["ref_id"];
		$this->data["sub_service"] = $subService;
		$this->data['sub_service_id']  = $sub_service_id;		
		$service_id = $serviceInstance["serviceModel"]['service_id'];
		
		$this->variables["sub-service-id"] = $sub_service_id;
		$this->variables["service-id"] = $service_id;

		$dualHomed = strstr($subService['home_type'], 'dual homed') ? true : false;
		$this->data["dual_homed"] = $dualHomed;
		$this->data["dual_homed_devices"] = $dualHomed?2:1;

		// No of devices should be same as dual homed devices (it should be 1 or 2)
		if($this->data["dual_homed_devices"] !=count($devices)){
			$this->errors[] = "Invalid Request. ".$this->data["dual_homed_devices"]." devices required to create payload. Sub Service : ".$subService["name"];
			return false;
		}

		$this->data["is_managed"] = $isManaged = $subService['is_managed'];

		$serviceModelTemplate = $serviceInstance["serviceModel"]["serviceModelTemplate"][0];

		$nsoPayload = $serviceModelTemplate["nso_payload"];

		$variables = self::findVariables($nsoPayload);
		$variablesMaster = VariablesMaster::find()->where(["variable_name" => $variables["master"]])->asArray()->all();
		$variablesIdMap = ArrayHelper::map($variablesMaster, 'variable_name', 'id');
		$this->data["variablesIdMap"] = $variablesIdMap;
		

		// Now There is only one Service Template (Device role) so first taken
		$template_id = $serviceInstance["serviceModel"]["serviceModelTemplate"][0]["id"];
		$this->data["template_id"] = $template_id;
		$customer_id = $serviceInstance["customer_id"];
		$this->data["service_order_id"] = $serviceInstance["service_order_id"];

		$serviceOrderId = $serviceInstance["service_order_id"];
		$nsoPayload = strtr($nsoPayload, $variables["replacements"]);
		$nsoPayloadArray = json_decode($nsoPayload, true);

		$payloadKey = array_keys($nsoPayloadArray)[0];
		$linkArray = $nsoPayloadArray[$payloadKey]["link"];

		$nsoPayloadArray[$payloadKey]["service-id"] = $serviceOrderId;
		$nsoPayloadArray[$payloadKey]["sub-service-id"] = $this->variables["sub-service-id"];

		$device_id1 = $devices[0]['device'];
		$device_id2 = $devices[1]['device'];

		$hostname1 = Device::getDevice($device_id1);
		$hostname2 = Device::getDevice($device_id2);

		$devices = [
			$hostname1,
			$hostname2,
			$userDefined["demarc-device"],
		];

		$ag2Devices = [];
		$topology = $this->findTopology($hostname1);
		if (!$topology) {
			return false;
		}
	
		$ag2Devices = $this->data["devices"]["AG2"];
		$ag2DevicesData = Device::getDevicesByHostnames($ag2Devices);
		$ag2DevicesData = ArrayHelper::index($ag2DevicesData, "hostname");

		// Set Common variables 
		if(array_key_exists('vpn-id',$variablesIdMap)){
			$this->data["vpn-id"] = ResourceManager::getVPNID($template_id, $device_id1, $variablesIdMap["vpn-id"], $customer_id);
		}

		if(array_key_exists('vrf-name',$variablesIdMap)){
			$this->data["vrf-name"] = ResourceManager::getVrfName($template_id, $variablesIdMap["vrf-name"], $this->data['customer_id']);
		}
		$this->setTrunkNo();
		$this->setCustVlan();
		
		
		// Above code should go in some initialize method
		

		foreach ($linkArray as $key => $link) {

			foreach ($link as $linkKey => $linkVal) {
				
				$blockResponse = is_array($linkVal)?[]:$linkVal;
				// Remove module from Payload if it is not required/assigned for Sub Service 
				if(is_array($linkVal) && !in_array($linkKey,$serviceInstance["components"]["modules"])){
					unset($linkArray[$key][$linkKey]);
					continue;
				}
				//$linkKey = "erps-config";//static 
				if ($linkKey == "interface") { 
					$blockResponse = $this->getInterfaceBlock($topology, $devices, $serviceInstance, $dualHomed, $isManaged, $variablesIdMap, $userDefined);										
				} else if ($linkKey == "vrf-definitions") {
					$blockResponse = $this->getVrfDefinitions($ag2Devices, $userDefined, $customer_id, $service_id, $service_instance_id,$this->data["service_order_id"]);				
				} else if ($linkKey == "bgp") {
					$blockResponse = $this->getBgpBlock();
				} else if ($linkKey == "demarc") {
					$blockResponse = $this->getDemarc($userDefined, $dualHomed);
					
				} else if ($linkKey == "erps-config") {
					$blockResponse = $this->getErpsConfig($topology, $hostname1, $hostname2, $userDefined, $service_instance_id);
				} else if ($linkKey == "esar-config") {
					$blockResponse = $this->getEsarConfigBlock();
				} 
				//pe($blockResponse);
				$linkArray[$key][$linkKey] = $blockResponse;

			}
			break; // Todo::Currently We have only one link in Payload in future we will implement it
		}

		$nsoPayloadArray[$payloadKey]["link"] = $linkArray;

		return [
			"payload" => $nsoPayloadArray,
			"variables" => [],
			"vpn-id" => $this->data["vpn-id"],
			"demarc-ingress-port-no" => @$this->data["demarc-ingress-port-no"],
		];
	}

	public function getServiceInstance($service_instance_id) {

		$serviceInstance = ServiceInstance::find()->alias("si")
			->joinWith([
				"serviceModel.subService.combonentBlocks.component",
				"customer",
				"serviceModel.serviceModelTemplate.serviceModelTemplateVariables.variable",				
			])
			->where(["si.id" => $service_instance_id])
			->asArray()->one();

		if (!$serviceInstance) { // Service Instance not found
			$this->errors[] = "Service Instance {$service_instance_id} not found";
			return false;
		}	

		if (!isset($serviceInstance["serviceModel"]["subService"]) || empty($serviceInstance["serviceModel"]["subService"])) { // Sub service not found
			$this->errors[] = "Subservice not found in service model";
			return false;
		}

		if (!isset($serviceInstance["serviceModel"]["serviceModelTemplate"][0]) || empty($serviceInstance["serviceModel"]["serviceModelTemplate"][0])) { // Service Model template not found
			$this->errors[] = "Service Model Template Not found";
			return false;
		}
		$this->data["service_instance_id"] = $service_instance_id;
		// Set Module and their blocks mapping 	
	    if($serviceInstance && isset($serviceInstance["serviceModel"]["subService"]["combonentBlocks"])){
	    	$components = $serviceInstance["serviceModel"]["subService"]["combonentBlocks"];
	    	$ret = [];
	    	foreach ($components as $key => $value) {
	    		$module = $value["component"]["system_name"];	
	    		$ret["modules"][] = $module;
	    		$ret["modules_blocks"][$module][] = $value["component_block"];
	    	}
	    	$ret["modules"] = array_values(array_unique($ret["modules"]));
	    	$serviceInstance["components"] = $ret;
	    }		

		return $serviceInstance;
	}




	private function findTopology($hostname1){
		
		$topology = ResourceManager::getTopology($hostname1);
		if (!$topology) {
			$this->errors[] = "Topology Not found";
			return false;
		}

		//$topology = json_decode($topology,true);
		$this->data["topology"] = $topology;
		$this->findDevicesFromTopology($topology);
		$this->setDevicesIds();
		return $topology;
	}

	public function setDevicesIds(){		
		$devices = $this->data["devices"];
		$deviceIds = [];
		$hostnameArray = [];
		foreach ($devices as $deviceType => $data) {	
			foreach($data as $hostname){
				$hostnameArray[] = $hostname;
			}
		}
		$devices = Device::getDevicesByHostnames($hostnameArray);
		$this->data["devices_id"] = ArrayHelper::map($devices,'hostname','id');
	}


	private function findDevicesFromTopology($topology){
		$devices = [];
		$devicesLooback =[];
		if(!isset($topology['parents'])){
			$this->errors[] = "Parent Devices not found";
			return false;
		}
		$devicesLooback[$topology["hostname"]] = $topology["loopback0"];

		// CSS Device
		if(strstr($topology["hostname"],"ESR") || strstr($topology["hostname"],"CSS")){
			$devices["CSS"][] = $topology["hostname"];
		}
		// ECR Device
		else if(strstr($topology["hostname"],"ECR")){
			$devices["ECR"][] = $topology["hostname"];
		}

		foreach ($topology['parents'] as $key => $val) {
			$devicesLooback[$val["hostname"]] = $val["loopback0"];
			// CSS Device
			if(strstr($val["hostname"],"ESR") || strstr($val["hostname"],"CSS")){
				$devices["CSS"][] = $topology;
			}
			// ECR Device
			else if(strstr($val["hostname"],"ECR")){
				$devices["ECR"][] = $val["hostname"];
			}
			//AG2 device	
			else if (strstr($val['hostname'], "AAR")) {
				$devices["AG2"][] = $val['hostname'];
			}
			//AG1 Device
			else if (strstr($val['hostname'], "PAR")) {
				$devices["AG1"][] = $val['hostname'];
			}
		}
		foreach ($topology['neighbours'] as $key => $val) {
			$devicesLooback[$val["hostname"]] = $val["loopback0"];
			// CSS Device
			if(strstr($val["hostname"],"ESR") || strstr($val["hostname"],"CSS")){
				$devices["CSS"][] = $topology;
			}
			// ECR Device
			else if(strstr($val["hostname"],"ECR")){
				$devices["ECR"][] = $val["hostname"];
			}
		}
		$this->data["devices"] = $devices;
		$this->data["devices_loopback"] = $devicesLooback;
	}

	private function getDeviceLoopback($hostname){
		if(isset($this->data["devices_loopback"][$hostname]) && !empty($this->data["devices_loopback"][$hostname])){
			return $this->data["devices_loopback"][$hostname];
		}
		return false;
	}

	/*@Desc : Get Module Block from $this->data property of class
	 *@Param : $module : interface,bgp,l2vpn... etc
	 *@Return : array of all block in module
	 */
	private function getModuleBlocks($module){
		return isset($this->data["components"]["modules_blocks"][$module])?$this->data["components"]["modules_blocks"][$module]:[];
	}

	/*@Desc: Check if block exit in module
	 */
	protected function isModuleBlocksExist($module,$block){

		$moduleBlocks = $this->getModuleBlocks($module);
		if(!$moduleBlocks){ 
			return false;
		}
		if(strpos($block,"_1")){
			$block= str_replace("_1", "_PRIMARY", $block);
		}
		if(strpos($block,"_2")){
			$block= str_replace("_2", "_SECONDARY", $block);
		}		
		return in_array($block, $moduleBlocks);			 
	}

	protected function getDeviceId($hostname){
		$device_id = isset($this->data["devices_id"][$hostname])?$this->data["devices_id"][$hostname]:false;
		if(!$device_id){
			$device_id = Device::getDeviceByHostname($hostname);
			if($device_id){
				$this->data["devices_id"][$hostname]= $device_id;
			}
		}
		return $device_id;
	}

	protected function getUD($variable){	
		return isset($this->data["userDefined"][$variable])?$this->data["userDefined"][$variable]:false;
	}
	
	private function setTrunkNo(){//Todo::tikdam
		if($this->data["sub_service"]["terminated_at"] == "CSS"){
			$this->data['trunk_no'] =1;
		}else{
			$this->data['trunk_no'] =1;
		}
	}
	
	private function setCustVlan(){
		$this->data["cust-vlan"] =explode(",",$this->getUD("cust-vlan"));
		$ag2Devices = $this->data["devices"]["AG2"];
		foreach($ag2Devices as $key=>$device){
			$this->data[$device]["cust-vlan"] = $this->data["cust-vlan"][$key];
		}
	}
}