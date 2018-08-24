<?php
namespace common\components;

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
 */
Class PayloadFrontend {

	public $errors = [];
	public $data = [];
	public $variabls = [];

	public function getServiceInstance($service_instance_id) {

		$serviceInstance = ServiceInstance::find()->alias("si")
			->joinWith([
				"customer",
				"serviceModel.serviceModelTemplate.serviceModelTemplateVariables.variable",
				"serviceModel.subService",
			])
			->where(["si.id" => $service_instance_id])
			->asArray()->one();

		return $serviceInstance;
	}

	private function setVariableMaster($serviceInstance) {
		$templateVariables = $serviceInstance["serviceModel"]["serviceModelTemplate"][0]['serviceModelTemplateVariables'];
		$variableMaster = [];
		foreach ($templateVariables as $variable) {

		}
	}

	/*@Desc : If interface value comming as gi,gigabite like then 
	 * it will convert to actual value that is required in nso
	 */
	private function setDemarcInterface($demarcInterface) {
	
		$demarcInterface = strtolower($demarcInterface);
		$interfaceArray = [
			"Gig" => ["gi", "gigabitethernet", "gigabite"],
			"TenGig" => ["tengige", "te", "tengigethernet", "tengigabitethernet"],
			"Hun" => ["HundredGigE", "Hu", "HundredGigEthernet"],
			"Fou" => ["FourtyGigE", "Fo", "FourtyGigEthernet"],
		];
		$ret = "";
		foreach ($interfaceArray as $replaceValue => $intfArray) {
			foreach ($intfArray as $interface) {
				if (strpos($demarcInterface, $interface) !== false) {
					$ret = $replaceValue . string_after_number($demarcInterface);
					break;
				}
			}
			if (!empty($ret)) {
				break;
			}
		}

		return $ret;
	}
	
	
	/* @Desc : For now it only set interface values*/
	private function setDemarcUserDefinedVariablesLogic($userDefined) {
		for ($i = 1; $i <= 2; $i++) {
			if (isset($userDefined["demarc-interface-id-" . $i])) {
				$interfaceId = $this->setDemarcInterface($userDefined["demarc-interface-id-" . $i]);
				$userDefined["demarc-interface-id-" . $i] = $this->setDemarcInterface($interfaceId);
			}
		}
		return $userDefined;
	}
	
	private function setIpTypeForLab($userDefined){
		
		 $variables = array_keys($userDefined);
		 if(in_array("ipv4-wan-address",$variables) && in_array("ipv6-wan-address",$variables)){
			 $userDefined["ip-type"] = "both";
		 }
		 else if(in_array("ipv4-wan-address",$variables) ){
			 $userDefined["ip-type"] = "ipv4";
		 }
		 else if(in_array("ipv6-wan-address",$variables)){
			 $userDefined["ip-type"] = "ipv6";
		 }
		return $userDefined;
	}
	
	public function findCustomerService($customer_id,$service_order_id){
        /*$serviceInstance = ServiceInstance::find()->alias("si")
                                        ->joinWith(["serviceModel"=>function($q){ return $q->alias("sm");}])
                                        ->where([
										"sm.service_id"=> $service_id,
										"si.customer_id"=>$customer_id,						
										])
										->andWhere(["IS NOT","si.rt_export_import",null])
                                        ->asArray()->one();*/
										//->createCommand()->rawSql;
										//pe($serviceInstance);
			$serviceInstance = ServiceInstance::find()->alias("si")
                                        //->joinWith(["serviceModel"=>function($q){ return $q->alias("sm");}])
                                        ->where([
										"si.service_order_id"=> $service_order_id,
										"si.customer_id"=>$customer_id,						
										])
										->andWhere(["IS NOT","si.rt_export_import",null])
                                        ->asArray()->one();							
       return $serviceInstance;

    }
	
	public function createPayload($service_instance_id, $devices, $userDefined,$uniqueIdGenerate) {
		
		$userDefined = $this->setDemarcUserDefinedVariablesLogic($userDefined);
		$userDefined = $this->setIpTypeForLab($userDefined);
		$userDefined['cust-lan-pool'] = $userDefined['lan-pool-ipv4-mask'];// newly added
		$this->data["userDefined"] = $userDefined;
	
		$serviceInstance = $this->getServiceInstance($service_instance_id);
		if (!$serviceInstance) {
			$this->errors[] = "Service Instance {$service_instance_id} not found";
			return false;
		}
		$this->data['customer_id'] = $serviceInstance["customer_id"];
		//Todo::remove for now add static customer for lab test 
		$serviceInstance["customer_id"] = 1;
		$serviceInstance["customer"]["id"] = 1;

		if (!isset($serviceInstance["serviceModel"]["subService"]) || empty($serviceInstance["serviceModel"]["subService"])) {
			$this->errors[] = "Subservice not found in service model";
			return false;
		}

		if (!isset($serviceInstance["serviceModel"]["serviceModelTemplate"][0]) || empty($serviceInstance["serviceModel"]["serviceModelTemplate"][0])) {
			$this->errors[] = "Service Model Template Not found";
			return false;
		}	

		
		// logic for storing the SD variables
		$modelVariables = $serviceInstance["serviceModel"]["serviceModelTemplate"][0]['serviceModelTemplateVariables'];
        $systemVariables = array();
        foreach($modelVariables as $var){                    
            if($var['variable']['type'] == 'system'){
                $systemVariables[$var['variable']['id']] = $var['variable']['variable_name'];
            }
        }                
        $this->variabls['sd_key'] = $systemVariables;
        $this->variabls['sd_val'] = array_flip($systemVariables);
        
		$subService = $serviceInstance["serviceModel"]["subService"];
		$sub_service_id = $subService["ref_id"];		
		$this->data['sub_service_id']  = $sub_service_id;
		$service_id = $serviceInstance["serviceModel"]['service_id'];		
		$this->variables["sub-service-id"] = $sub_service_id;
		$this->variables["service-id"] = $service_id;		
		
		$customer = $serviceInstance["customer"];
		

		$this->data['dualhomed'] = $dualHomed = strstr($subService['name'], 'dual homed') ? true : false;
		//to check the userdefined variables
		/*
		if(strpos($userDefined['lan-pool-ipv4-mask'],",") === false && $dualHomed === true){
				$this->errors[] = "Kindly  provide missing values for lan-pool-ipv4-mask";
				return false;
		}
		if(strpos($userDefined['lan-pool-ipv6-mask'],",") === false && $dualHomed === true){
				$this->errors[] = "Kindly  provide missing values for lan-pool-ipv6-mask";
				return false;
		}*/
		if(!isset($userDefined['pe-type']) && empty($userDefined['pe-type'])){
				$this->errors[] = "Kindly provide values for user defined variable pe-type";
				return false;
		}
		
		$isManaged = $subService['is_managed'] ? "managed" : "unmanaged";
		$this->data["is_managed"] = $isManaged;

		$serviceModelTemplate = $serviceInstance["serviceModel"]["serviceModelTemplate"][0];

		$nsoPayload = $serviceModelTemplate["nso_payload"];

		$variables = self::findVariables($nsoPayload);
		$variablesMaster = VariablesMaster::find()->where(["variable_name" => $variables["master"]])->asArray()->all();
		$variablesIdMap = ArrayHelper::map($variablesMaster, 'variable_name', 'id');

		$template_id = $serviceInstance["serviceModel"]["serviceModelTemplate"][0]["id"];
		$customer_id = $serviceInstance["customer_id"];
		
		$serviceOrderId = $serviceInstance["service_order_id"];
		$nsoPayload = strtr($nsoPayload, $variables["replacements"]);		
		$nsoPayloadArray = json_decode($nsoPayload, true);

		$payloadKey = array_keys($nsoPayloadArray)[0];

		$linkArray = $nsoPayloadArray[$payloadKey]["link"];		
		$nsoPayloadArray[$payloadKey]["service-id"] =  $serviceOrderId;// old logic $serviceOrderId;	//$this->variables["service-id"]; old logic
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
		$topology = ResourceManager::getTopology($hostname1);		
		if (!$topology) {
			$this->errors[] = "Topology Not found";
			return false;
		}

		//$topology2 = ResourceManager::getTopology($hostname2);
		foreach ($topology['parents'] as $key => $val) {
			if (strstr($val['hostname'], "AAR")) {
				//AG2
				$ag2Devices[] = $val['hostname'];
			}
		}
		if (in_array($sub_service_id, [2.38, "2.38", 2.41, "2.41",2.92, "2.92", 2.95, "2.95","2.101",2.101,"2.104",2.104])) {
			$this->data['trunk_no'] =1;
		}else{
			$this->data['trunk_no'] =2;
		}
		$ag2DevicesData = Device::getDevicesByHostnames($ag2Devices);
		$ag2DevicesData = ArrayHelper::index($ag2DevicesData, "hostname");		
		$this->data["vpn-id"] = ResourceManager::getVPNID($template_id, $device_id1, $variablesIdMap["vpn-id"], $customer_id);

		foreach ($linkArray as $key => $link) {

			foreach ($link as $linkKey => $linkVal) {

				if ($linkKey == "interface") {
					$interfaceModule = $this->getInterfaceModule($topology, $devices, $serviceInstance, $dualHomed, $isManaged, $variablesIdMap, $userDefined,$ag2Devices);
					$linkArray[$key][$linkKey] = $interfaceModule;
				} else if ($linkKey == "l2-vfi") {
					$l2vfiModule = $this->getL2Vfi($hostname1, $hostname2, $ag2Devices, $topology);
					$linkArray[$key][$linkKey] = $l2vfiModule;
				} else if ($linkKey == "ag1-l2-ext") {
					$ag1L2Ext = $this->getAg1L2Ext($hostname1, $hostname2, $topology);
					$linkArray[$key][$linkKey] = $ag1L2Ext;
				} else if ($linkKey == "phwe") {
					$phwe = $this->getPHWE($topology, 'both', $userDefined['cust-lan-pool'], $this->getServiceInstance($service_instance_id), $variablesIdMap);
					$linkArray[$key][$linkKey] = $phwe;
				} else if ($linkKey == "l2vpn") {
					$l2vpn = $this->getL2VPN($hostname1, $hostname2, $topology, $template_id, $customer_id, $variablesIdMap);
					$linkArray[$key][$linkKey] = $l2vpn;
				} else if ($linkKey == "vrf-definitions") {
					$vrfDefinition = $this->getVrfDefinitions($ag2Devices, $userDefined, $this->data['customer_id'] ,$service_id,$service_instance_id,$serviceOrderId);
					//$vrfDefinition = $this->getVrfDefinitions($ag2Devices, $userDefined, $customer_id);
					$linkArray[$key][$linkKey] = $vrfDefinition;
				} else if ($linkKey == "bgp") {
					$bgp = $this->getBgp($service_instance_id, $template_id, $ag2Devices, $ag2DevicesData, $customer, $variablesIdMap, $userDefined, $phwe);
					$linkArray[$key][$linkKey] = $bgp;
				} else if ($linkKey == "demarc") {
					$demarc = $this->getDemarc($userDefined, $dualHomed);
					$linkArray[$key][$linkKey] = $demarc;
				} else if ($linkKey == "erps-config") {
					$erpsConfig = $this->getErpsConfig($topology, $hostname1, $hostname2, $userDefined,$service_instance_id);
					$linkArray[$key][$linkKey] = $erpsConfig;
				} else if ($linkKey == "static") {
					$staticBlock = $this->getStaticBlock($topology, $userDefined['ip-type'], $userDefined['lan-pool-ipv4-mask'], $userDefined['lan-pool-ipv6-mask'], $phwe, $template_id, $variablesIdMap, $customer,$userDefined);
					$linkArray[$key][$linkKey] = $staticBlock;
				} else if ($linkKey == "l3vpn-static-xr") {
					$l3vpnStatic = $this->getL3vpnStaticXR($topology, $template_id, $variablesIdMap, $customer_id, $userDefined['cust-lan-pool'], $phwe, $userDefined['ip-type'], $staticBlock);
					$linkArray[$key][$linkKey] = $l3vpnStatic;
				} else if ($linkKey == "prefix-set-and-route-policy") {
					$prefixRoute = $this->getPrefixRoutePolicy($topology, $userDefined['ip-type'], $userDefined['lan-pool-ipv4-mask'], $userDefined['lan-pool-ipv6-mask'], $customer['company_name']);
					$linkArray[$key][$linkKey] = $prefixRoute;
				} else if ($linkKey == "route-policy-pass-all") {
					$routeAll = $this->getRoutePolicyPassAll($topology);
					$linkArray[$key][$linkKey] = $routeAll;
				}

				// if any error raised during any module then break other module logic 
				if(!empty($this->errors)){
					return false;
				}
			}
			break; // Currently We have only one link in Payload in future we will implement it
		}		
		//unset($linkArray[0]['demarc']);
		//unset($linkArray[0]['erps-config']);
		//unset($linkArray[0]['route-policy-pass-all']); // route policy deleted
		$bgp = [2.37, 2.38, "2.37", "2.38",2.91,2.92,"2.91","2.92",2.46,2.47,"2.46","2.47",2.55,2.56,"2.55","2.56",2.64,2.65,"2.64","2.65",2.100,2.101,"2.100","2.101"];
		$static = [2.40, 2.41, "2.40", "2.41",2.94,2.95,"2.94","2.95",2.49,"2.49",2.5,"2.5",2.58,2.59,"2.58","2.59",2.67,2.68,"2.67","2.68",2.103,2.104,"2.103","2.104"];
		$nol2ext = [2.38, "2.38", 2.41, "2.41",2.92,2.95,"2.92","2.95",2.47,"2.47",2.5,2.56,"2.56",2.59,2.59,"2.65","2.65",2.68,"2.68",2.101,"2.101",2.104,"2.104"];
		$erps = [2.37, 2.38, "2.37", "2.38",2.40, 2.41, "2.40", "2.41",2.91,2.92,"2.91","2.92",2.94,2.95,"2.94","2.95",2.100,2.101,"2.100","2.101",2.103,2.104,"2.103","2.104"];
		$nodemarc = [2.55, 2.56, "2.55", "2.56",2.58, 2.59, "2.58", "2.59",2.64,2.65,"2.64","2.65",2.67,2.68,"2.67","2.68",2.100,2.101,"2.100","2.101",2.103,2.104,"2.103","2.104"];
		$l2vfi =[2.46,2.47,"2.46","2.47",2.49,"2.49",2.5,"2.5",2.55,2.56,"2.55","2.56",2.58,"2.58",2.59,"2.59",2.64,2.65,2.67,2.68,'2.64','2.65','2.67','2.68'];
		$linkArray[0]['circuit-id']=$uniqueIdGenerate;
		if(!in_array($sub_service_id,$erps)){
			unset($linkArray[0]['erps-config']);
		}
		if (in_array($sub_service_id, $bgp)) {
			unset($linkArray[0]['static']);
		}
		if (in_array($sub_service_id, $static )) {
			unset($linkArray[0]['bgp']);
		}
		if (in_array($sub_service_id, $nol2ext)) {
			unset($linkArray[0]['ag1-l2-ext']);			
		}
		if (in_array($sub_service_id, $nodemarc)) {
			unset($linkArray[0]['demarc']);			
		}
		if (in_array($sub_service_id, $l2vfi)) {
			unset($linkArray[0]['l2-vfi']);			
		}
		$nsoPayloadArray[$payloadKey]["link"] = $linkArray;

		if(!empty($this->errors)){
			return false;
		}

		return [
			"payload" => $nsoPayloadArray,
			"variables" => [],
			"vpn-id" => $this->data["vpn-id"],
			"demarc-ingress-port-no" => $this->data["demarc-ingress-port-no"],
		];
	}

	private function getVrfBlock($device, $ipType, $topologyType) {
		$vrfBlock = [
			"device" => $device,
			"vrf-name" => $this->data["vrf-name"],
			"address-family" => $ipType,
		];

		if (in_array($ipType, ["ipv4", "both"])) {

			$vrfBlock["rt-import"] = [
				[
					"rt" => "65535:103",
				],
			];

			$vrfBlock["rt-export"] = [
				[
					"rt" => "65535:101",
				],
			];

		}

		if (in_array($ipType, ["ipv4", "both"])) {

			$vrfBlock["rt-import-ipv6"] = [
				[
					"rt" => "65535:103",
				],
			];

			$vrfBlock["rt-export-ipv6"] = [
				[
					"rt" => "65535:101",
				],
			];

		}
	}

	public function getVrfDefinitions_old($ag2Devices, $userDefined, $customer_id) {
			$vrfDefinitions = array();
			$vrf = array();
			$ipType = $userDefined["ip-type"];
			$topologyType = isset($userDefined["topology"]) && !empty($userDefined["topology"]) ? $userDefined["topology"] : "hub";
			$rtExport = ResourceManager::getRT($customer_id, $qos = 'major');
			if ($topologyType == "hub") {
				//$vrfDefinitions[] =  $this->getVrfBlock($ag2Devices[0],$ipType,$topologyType);
				for($i=0;$i<=1;$i++){ // newly added
					$vrfBlock = [
						"device" => $ag2Devices[$i], //"device" => $ag2Devices[0],
						"vrf-name" => $this->data["vrf-name"],
						"address-family" => $ipType,
						
					];
					
					// For IPv4
					if (in_array($ipType, ["ipv4", "both"])) {
						$vrfBlock["rt-export"] = [
								[
									"rt" => $rtExport,
								],
							];
					}
					
					// For IPv6
					if (in_array($ipType, ["ipv6", "both"])) {
						$vrfBlock["rt-export-ipv6"] = [
								[
									"rt" => $rtExport,
								],
							];
					}
					
					$vrfDefinitions[] = $vrfBlock;
					
					//$vrf[] = $vrfDefinitions; // newly added
				}
			} else if ($topologyType == "spoke") {
			}
			//return $vrfDefinitions;
			return $vrfDefinitions;
		}

	
	public function getVrfDefinitions($ag2Devices, $userDefined, $customer_id,$service_id,$service_instance_id,$service_order_id) {
		$model = new ServiceInstance();		
		//$service = $this->findCustomerService($customer_id,$service_id);	
		$service = $this->findCustomerService($customer_id,$service_order_id);	
		
		if($service){
			$topologyType = "spoke";
			$value = explode("|",$service['rt_export_import']);
			//$value = explode("|","BGLRRLABAAR001,BGLRRLABAAR002|55836:365,55836:366");
			$hubag2 = explode(",",$value[0]);
			$rt = explode(",",$value[1]);	
			$rtExport = $rt[0];
			$rtImport = $rt[1];		
		}else{
			$topologyType = "hub";
		}	
			
		$vrfDefinitions = array();
		$vrf = array();
		$ipType = $userDefined["ip-type"];	

		if ($topologyType == "hub") {
			//$vrfDefinitions[] =  $this->getVrfBlock($ag2Devices[0],$ipType,$topologyType);
			$rtExport = ResourceManager::getRT($customer_id, $qos = 'major');
			$rtImport = ResourceManager::getRT($customer_id, $qos = 'major');			
			$model = ServiceInstance::findOne($service_instance_id);
			$model->rt_export_import = implode(",",array_values($ag2Devices))."|".$rtExport.",".$rtImport;
			$model->save();
			for($i=0;$i<=1;$i++){ // newly added
				$vrfBlock = [
					"device" => $ag2Devices[$i], //"device" => $ag2Devices[0],
					"vrf-name" => $this->data["vrf-name"],
					"address-family" => $ipType,
					
				];
				
				// For IPv4
				if (in_array($ipType, ["ipv4", "both"])) {
					$vrfBlock["rt-export"] = [
							[
								"rt" => $rtExport,
							],
						];
						$vrfBlock["rt-import"] = [
							[
								"rt" => $rtImport,
							],
						];
				}
				
			// For IPv6
				if (in_array($ipType, ["ipv6", "both"])) {
					$vrfBlock["rt-export-ipv6"] = [
							[
								"rt" => $rtExport,
							],
						];
						$vrfBlock["rt-import-ipv6"] = [
							[
								"rt" => $rtImport,
							],
						];
				}
				
				$vrfDefinitions[] = $vrfBlock;				
				if(strtolower($this->data['userDefined']['pe-type']) == 'single'){	
					break;
				}
				//$vrf[] = $vrfDefinitions; // newly added
			}
		} else if ($topologyType == "spoke") {
			for($i=0;$i<=1;$i++){ // newly added
				$vrfBlock = [
					"device" => $hubag2[$i], //"device" => $ag2Devices[0],
					"vrf-name" => $this->data["vrf-name"],
					"address-family" => $ipType,
					
				];
				
				// For IPv4
				if (in_array($ipType, ["ipv4", "both"])) {
					$vrfBlock["rt-import"] = [
							[
								"rt" => $rtImport,
							],
						];
				}
				
				// For IPv6
				if (in_array($ipType, ["ipv6", "both"])) {
					$vrfBlock["rt-import-ipv6"] = [
							[
								"rt" => $rtImport,
							],
						];
				}
				
				$vrfDefinitions[] = $vrfBlock;
				if(strtolower($this->data['userDefined']['pe-type']) == 'single'){	
					break;
				}
				//$vrf[] = $vrfDefinitions; // newly added
			}
			for($i=0;$i<=1;$i++){ // newly added
				$vrfBlockTopo = [
					"device" => $ag2Devices[$i], //"device" => $ag2Devices[0],
					"vrf-name" => $this->data["vrf-name"],
					"address-family" => $ipType,
					
				];
				
				// For IPv4
				if (in_array($ipType, ["ipv4", "both"])) {
					$vrfBlockTopo["rt-export"] = [
							[
								"rt" => $rtImport,
							],
						];
					$vrfBlockTopo["rt-import"] = [
							[
								"rt" => $rtExport,
							],
						];					
				}
				
				// For IPv6
				if (in_array($ipType, ["ipv6", "both"])) {
					$vrfBlockTopo["rt-export-ipv6"] = [
							[
								"rt" => $rtImport,
							],
						];	
					$vrfBlockTopo["rt-import-ipv6"] = [
							[
								"rt" => $rtExport,
							],
						];
					
				}
				
				$vrfDefinitions[] = $vrfBlockTopo;
				if(strtolower($this->data['userDefined']['pe-type']) == 'single'){	
					break;
				}
				//$vrf[] = $vrfDefinitions; // newly added
			}
		}

		//return $vrfDefinitions;
		return $vrfDefinitions;
	}

	/*
				{
		              "device": "RAD_DEMARC_15", - Done
		              "tagged": "tagged", - Done
		              "pe-type": "dual", - Done
		              "flows": [
		                {
		                  "vlan-id": 111,
		                  "egress-bridge-port-no": 3
		                },
		                {
		                  "vlan-id": 105,
		                  "egress-bridge-port-no": 1
		                }
		              ],
		              "bit-value": 7, - Done
		              "block-port-no": "0/1", - Done
		              "ingress-port-no": 2,
		              "erps-config": [
		                {
		                  "erps-id": 1,
		                  "vlan-id": 111,
		                  "erps-mode": "major"
		                },
		                                                          {
		                  "erps-id": 2,
		                  "vlan-id": 105,
		                  "erps-mode": "major"
		                }

		              ]
		            }
					New Payload :
					  "device": "RAD_DEMARC_15",
		                   "tagged": "tagged",
		                   "pe-type": "dual",
		                   "bit-value": 7,
		                   "block-port-no": "0/1",
		                   "ingress-port-no": 5,
		                   "customer-vlan-1": 2001,
		                   "egress-bridge-port-no-1": 8,
		                   "erps-mode-1":"major",
		                   "erps-id-1":"1",
		                   "customer-vlan-2": 2002,
		                   "egress-bridge-port-no-2": 8,
		                   "erps-mode-2":"major",
		                   "erps-id-2":"2"
	*/
	public function getDemarc($userDefined, $dualHomed) {
		
		if(isset($userDefined['demarc-ingress-port-no'])){
			$this->data["demarc-ingress-port-no"] = $userDefined["demarc-ingress-port-no"]; // Todo:: In future this value will be from SYstem defined logic
			$ingress_port_1 = $userDefined["demarc-ingress-port-no"];
			$ingress_port_2 = "1";//$userDefined["demarc-ingress-port-no"];			
		}else if(isset($userDefined['demarc-ingress-port-no-1']) && sset($userDefined['demarc-ingress-port-no-2'])){
			$ingress_port_1 = $userDefined["demarc-ingress-port-no-1"];
			$ingress_port_2 = $userDefined["demarc-ingress-port-no-2"];			
		}
		/*return [
			"device" => $userDefined["demarc-device"],
			"tagged" => $userDefined["tag"],
			"pe-type" => $userDefined["pe-type"],
			"bit-value" => $userDefined["bit-value"],
			"block-port-no" => $userDefined["block-port-no"],
			"ingress-port-no" => $userDefined["demarc-ingress-port-no"],
			"customer-vlan-1" => $this->data["cust-vlan"][0],
			"egress-bridge-port-no-1" => $userDefined["demarc-egress-bridge-port-no-1"],
			"erps-mode-1" => strtolower($userDefined["demarc-erps-mode-1"]),
			"erps-id-1" => $userDefined["demarc-erps-id-1"],
			"customer-vlan-2" => $this->data["cust-vlan"][1],
			"egress-bridge-port-no-2" => $userDefined["demarc-egress-bridge-port-no-2"],
			"erps-mode-2" => strtolower($userDefined["demarc-erps-mode-2"]),
			"erps-id-2" => $userDefined["demarc-erps-id-2"],
		];*/
		
		
			if(strtolower($this->data['userDefined']['pe-type']) == 'single'){									
			$demarc =  [
			"device" => $userDefined["demarc-device"],
			"tagged" => $userDefined["tag"],
			"pe-type" => $userDefined["pe-type"],
			"bit-value" => $userDefined["bit-value"],
			"block-port-no" => $userDefined["block-port-no"],
			"ingress-port-no-1" => $ingress_port_1,
			"ingress-port-no-2" => $ingress_port_2,
			"customer-vlan-1" => $this->data["cust-vlan"][0],
			"customer-vlan-2" => $this->data["cust-vlan"][0],
			"egress-bridge-port-no-1" => $userDefined["demarc-egress-bridge-port-no-1"],
			"egress-bridge-port-no-2" => $userDefined["demarc-egress-bridge-port-no-2"],
			"erps-mode-1" => strtolower($userDefined["demarc-erps-mode-1"]),
			"erps-id-1" => $userDefined["demarc-erps-id-1"],			
			];			
			}else{
					$demarc =  [
			"device" => $userDefined["demarc-device"],
			"tagged" => $userDefined["tag"],
			"pe-type" => $userDefined["pe-type"],
			"bit-value" => $userDefined["bit-value"],
			"block-port-no" => $userDefined["block-port-no"],
			"ingress-port-no-1" => $ingress_port_1,
			"ingress-port-no-2" => $ingress_port_2,
			"customer-vlan-1" => $this->data["cust-vlan"][0],
			"egress-bridge-port-no-1" => $userDefined["demarc-egress-bridge-port-no-1"],
			"egress-bridge-port-no-2" => $userDefined["demarc-egress-bridge-port-no-2"],
			"erps-mode-1" => strtolower($userDefined["demarc-erps-mode-1"]),
			"erps-id-1" => $userDefined["demarc-erps-id-1"],
			"erps-id-2" => $userDefined["demarc-erps-id-2"],
			"customer-vlan-2" => $this->data["cust-vlan"][1],
			"egress-bridge-port-no-2" => $userDefined["demarc-egress-bridge-port-no-2"],
			"erps-mode-2" => strtolower($userDefined["demarc-erps-mode-2"]),
			
		];	
				}	
		return $demarc;
	}

	/* For AG2 - AAR
				 {
		                "id": "65000",	// unique id device specific
		                "vrf": [
		                  {
		                    "vrf-name": "vrf123vpn", //same for all
		                    "bgp-id": "65000", // 55836 SD in var mapping
		                    "ag2-device": "ag2-primary", //
		                    "role": "Primary",
		                    "rd": "201:33", // same rd vrf-definitions
		                    "password": "pwd", // Static
		                    "address-family": "both", // UD
		                    "nei-ce-ip-ipv4": "34.23.34.45", // BVI same ip
		                    "nei-ce-ip-ipv6": "2403:101:101:208::2", // ipv6-wan-address +1 ip take same pool
		                    "fttp-lp-ipv4": "ASDF1", // Take logic from RIL document
							"fftp-lp-ipv6":"ASDF2", // Take logic from RIL document
		                    "remote-as": 9730 // UD
		                  }
		                ]
		              },
	*/
	public function getBgp($service_instance_id, $template_id, $ag2Devices, $ag2DevicesData, $customer, $variablesIdMap, $userDefined, $phwe) {

		$bgp = [];
		$bgpId = ResourceManager::getVariablevalue($variablesIdMap["bgp-id"], $customer["id"]);
		$ipType = $userDefined["ip-type"];

		$neiCeIpIpv4Array = isset($userDefined["nei-ce-ip-ipv4"]) ? explode(",", $userDefined["nei-ce-ip-ipv4"]) : [];
		foreach ($ag2Devices as $key => $device) {

			$role = $key == 0 ? "Primary" : "Secondary";

			$fttpIpIpv4 = $fttpIpIpv6 = $neiCeIpIpv6 = $neiCeIpIpv4 = "";
			if (in_array($ipType, ["ipv4", "both"])) {
				$fttpIpIpv4 = ResourceManager::getFttpLp($customer["company_name"], "ipv4");

				$keyIpv4 = ($role == "Primary") ? 0 : 1;
				if (!empty($neiCeIpIpv4Array) && isset($neiCeIpIpv4Array[$keyIpv4]) && !empty($neiCeIpIpv4Array[$keyIpv4])) {
					$neiCeIpIpv4 = $neiCeIpIpv4Array[$keyIpv4];
				} else {
					$neiCeIpIpv4 = $phwe[$key]["routing-protocol"]["ce-intf-ip"];
				}
			}

			if (in_array($ipType, ["ipv6", "both"])) {

				$fttpIpIpv6 = ResourceManager::getFttpLp($customer["company_name"], "ipv6");

				$device_id = $ag2DevicesData[$device];
				// $resulIpv6 = Ipv6PoolAssignment::getIpv6PoolAssignment($service_instance_id, $template_id, $device_id, 1, [
				// 	$variablesIdMap["nei-ce-ip-ipv6"] =>"nei-ce-ip-ipv6"
				// ]);
				// $neiCeIpIpv6 = $resulIpv6["nei-ce-ip-ipv6"];
				if (isset($this->data["ipv6"][$key]["nei-ce-ip-ipv6"])) {
					// Jugad
					$neiCeIpIpv6 = $this->data["ipv6"][$key]["nei-ce-ip-ipv6"];
				}
			}

			$vrfArray = [
				"vrf-name" => $this->data["vrf-name"],
				"bgp-id" => $bgpId,
				"ag2-device" => $device,
				"role" => $role,
				"rd" => ResourceManager::getRD($template_id, $device_id, $variablesIdMap['rd'], $customer['id']),
				//"password"=>"pwd",
				"address-family" => $userDefined["ip-type"],
				"nei-ce-ip-ipv4" => $neiCeIpIpv4,
				"nei-ce-ip-ipv6" => $neiCeIpIpv6,
				"ftth-lp-ipv4" => $fttpIpIpv4,
				"ftth-lp-ipv6" => $fttpIpIpv6,
				"remote-as" => $userDefined["remote-as"],
			];

			if ($ipType == "ipv4") {
				unset($vrfArray["ftth-lp-ipv6"], $vrfArray["nei-ce-ip-ipv6"]);
			} else if ($ipType == "ipv6") {
				unset($vrfArray["ftth-lp-ipv4"], $vrfArray["nei-ce-ip-ipv4"]);
			}
			/*if ($role == 'Secondary') {
				unset($vrfArray["ftth-lp-ipv6"], $vrfArray["ftth-lp-ipv4"]);
			}*/
			$bgp[] = [
				"id" => rand(1, 1000),
				"vrf" => [$vrfArray],
			];
		}
		if(strtolower($this->data['userDefined']['pe-type']) == 'single'){									
					$bgp =$bgp[0];
					$bgp['vrf'][0]['role'] = 'Secondary';
				}
		return $bgp;
	}

	/*
				 [ // if input device is CSS then this block works for AG1
		              {
		                "device-name": "AG1-primary", // CSS1 parents find AG1 device "PAR"
		                "vrf-name": "vrf123vpn", //same for all
		                "vpn-id": 102, //same for all
		                "address-list": [
		                  {
		                    "nei-ip": "25.20.20.21" // Parent AG2 loopback
		                  },
						  {
		                    "nei-ip": "1.1.1.1" // Child CSS loopback
		                  }
		                ]
		              },
		              {
		                "device-name": "AG1-Secondary",// CSS2 parents find AG1 device "PAR"
		                "vrf-name": "vrf123vpn",
		                "vpn-id": 102,
		                "address-list": [
		                  {
		                    "nei-ip": "25.20.20.21" // Parent AG2 loopback
		                  },
		                  {
		                    "nei-ip": "1.1.1.1" // Child CSS loopback
		                  }

		                ]
		              }
	*/
	public function getAg1L2Ext($hostname1, $hostname2, $topology) {

		$return = [];
		$devices = [];
		$devices["css"][] = $topology["loopback0"];
		foreach ($topology["neighbours"] as $parentDevice) {
			if (strpos($parentDevice["hostname"], "ESR") !== false || strpos($parentDevice["hostname"], "CSS") !== false) {
				$devices["css"][] = $parentDevice["loopback0"];
			}
		}
		foreach ($topology["parents"] as $parentDevice) {
			if (strpos($parentDevice["hostname"], "PAR") !== false) {
				$devices["ag1"][] = $parentDevice["hostname"];
			}
			if (strpos($parentDevice["hostname"], "AAR") !== false) {
				$devices["ag2"][] = $parentDevice["loopback0"];
			}
		}

		foreach ($devices["ag1"] as $key => $device) {

			$return[] = [
				"device-name" => $device,
				"vrf-name" => $this->data["vrf-name"],
				"vpn-id" => $this->data["vpn-id"],
				"address-list" => [
					["nei-ip" => $devices["ag2"][$key]],
					["nei-ip" => $devices["css"][$key]],
				],
			];
			if(strtolower($this->data['userDefined']['pe-type']) == 'single'){	
					break;
				}
		}

		return $return;

	}
	/*
			 "l2-vfi": [ // for CSS/ECR input devices
		              {
		                "vrf-name": "vrf123vpn_ESR5", //same for all
		                "device-name": "ASR920_ESR5", // input device CSS/ECR 1
		                "vpn-id": 102, //same for all
		                "vlan-cust": 404, // primary
		                "nei-ag2-ip": "34.23.34.45" // parent AG2 primary loopback
		              },
		              {
		                "vrf-name": "vrf123vpn_ECR1", //same for all
		                "device-name": "ASR920_ECR1", // input device CSS/ECR 2
		                "vpn-id": 102,//same for all
		                "vlan-cust": 404,// secondary
		                "nei-ag2-ip": "34.23.34.45" // parent AG2 secondary loopback
		              }
		            ],
	*/
	public function getL2Vfi($hostname1, $hostname2, $ag2Devices, $topology) {
	
		$deviceType = substr($hostname1,8,3);
		
		$neiAg2Ip = [];
		foreach ($topology["parents"] as $parentDevice) {
				if (strpos($parentDevice["hostname"], "PAR") !== false) { //AG1 Devices
					$ag1Devices[] = $parentDevice["loopback0"];
				}
		 }
		 
		foreach ($ag2Devices as $device) {
			foreach ($topology["parents"] as $parentDevice) {				
				if ($parentDevice["hostname"] == $device) {
					$neiAg2Ip[$device] = $parentDevice["loopback0"];
				}
			}
		}
		
		for ($i = 1; $i <= 2; $i++) {
			$hostname = 'hostname' . $i;
			$ag2Device = $ag2Devices[$i - 1];
			
			$l2vfiBlock  = [
				"vrf-name" => $this->data["vrf-name"],
				"device-name" => $$hostname,
				"vpn-id" => $this->data["vpn-id"],
				"vlan-cust" => $this->data[$ag2Device]["cust-vlan"],
				//"nei-ag2-ip" => $neiAg2Ip[$ag2Device],
				"nei-ag2-ip" => $ag1Devices[$i - 1],
			];
			
			if(in_array($deviceType,["ESR","CSS"])){
				$l2vfiBlock["nei-ag2-ip"] =  $ag1Devices[$i - 1];
			}
			else if($deviceType == "ECR"){ 
				$l2vfiBlock["nei-ag2-ip"] =  $neiAg2Ip[$ag2Device];
			}
			$l2vfiModule[] = $l2vfiBlock;
			if(strtolower($this->data['userDefined']['pe-type']) == 'single'){	
					break;
				}
		}

		return $l2vfiModule;

	}

	public function getInterfaceModule($topology, $devices, $serviceInstance, $dualHomed, $isManaged, $variablesIdMap, $userDefined,$ag2Devices) {

		$hostname1 = $devices[0]; //AG2
		$hostname2 = $devices[1]; //AG2
		$demarc = $devices[2]; // Demarc

		$customer_id = $serviceInstance["customer_id"];
		$template_id = $serviceInstance["serviceModel"]["serviceModelTemplate"][0]["id"];
		$service_instance_id = $serviceInstance["id"];

		$interfaceArray = array();
		$interface = array();
		$i = 1;
		$ipType = $userDefined["ip-type"];
		$custVlanArray = isset($userDefined["cust-vlan"]) ? explode(",", $userDefined["cust-vlan"]) : [];
		$ipv4WanAddressArray = isset($userDefined["ipv4-wan-address"]) ? explode(",", $userDefined["ipv4-wan-address"]) : [];

		// Logic to create AAR/AG2 device interfaces starts here
		foreach ($topology['parents'] as $key => $val) {
			if (strstr($val['hostname'], "AAR")) {
				//AG2

				$connectedDevice = $val['hostname'];
				$hostname = $val['hostname'];
				// device mode variables of interface module
				if ($i == 1) {
					$deviceMode = "primary";
					$customerVlan = isset($custVlanArray[0]) ? $custVlanArray[0] : "";
				} else {
					$deviceMode = "secondary";
					$customerVlan = isset($custVlanArray[1]) ? $custVlanArray[1] : "";
				}

				$device = Device::getDeviceByHostname($val['hostname']);

				$device_id = $device["id"];

				// logic as per declared in the resourcemanager file
				//$service_model_template_id, $device_id, $resource_manager_id, $customer_id
				//$customerVlan   =   (int)ResourceManager::getCustomerServiceVlan($template_id,$device_id,$variablesIdMap["customer-vlan"],$customer_id);
				//$customerVlan = $deviceMode =="secondary"?$customerVlan+1:$customerVlan;

				// interface description variable
				$interfaceDesc = ResourceManager::getInterfaceDescription($service_instance_id, 23, $isManaged);

				// vrf name for the interface : $service_model_template_id, $device_id, $resource_manager_id, $customer_id
				$vrfName = ResourceManager::getVrfName($template_id, $variablesIdMap["vrf-name"], $this->data['customer_id']);
				$this->data["vrf-name"] = $vrfName;

				$interfaceID = "BVI" . $customerVlan;
				$interfaceArray['interface-id'] = $interfaceID;
				$interfaceArray['connected-device'] = $connectedDevice;
				$interfaceArray['device-mode'] = $deviceMode;
				$interfaceArray['ip-type'] = $ipType;
				//$interfaceArray['dual-homed'] = $dualHomed;
				$interfaceArray['customer-vlan']['vlan-id'] = $customerVlan;
				$interfaceArray['desc'] = $interfaceDesc;
				$interfaceArray['vrf-name'] = $vrfName;

				// Check ipv4-wan-address coming from UD then take as UD otherwise form System defined
				$ipv4WanAddress = "";
				if (!empty($ipv4WanAddressArray)) {
					$ipIndex = $deviceMode == "primary" ? 0 : 1;
					$ipv4WanAddress = isset($ipv4WanAddressArray[$ipIndex]) ? $ipv4WanAddressArray[$ipIndex] : "";
				}
				if (empty($ipv4WanAddress)) {
					// IPv4 from pool as System Defined Value
					$ipv4Assignment = new Ipv4Assignment();
					$resultIpv4 = $ipv4Assignment->asssignIp($service_instance_id, $template_id, $device_id, 1, [
						$variablesIdMap["ipv4-wan-address"] => "ipv4-wan-address",
					]);
					$ipv4WanAddress = $resultIpv4["ipv4-wan-address"] . " 255.255.255.254";
				}
				$interfaceArray['ipv4-wan-address'] = $ipv4WanAddress; // . " 255.255.255.254";

				// IPV6 : ipv6-wan-address and nei-ce-ip-ipv6
				// Set Ipv6 if coming as UD
				$resulIpv6 = [];
				if (isset($userDefined["ipv6-wan-address"]) && !empty($userDefined["ipv6-wan-address"]) &&
					isset($userDefined["nei-ce-ip-ipv6"]) && !empty($userDefined["nei-ce-ip-ipv6"])
				) {
					$ipv6WanAddressArray = explode(",", $userDefined["ipv6-wan-address"]);
					$neiCeIpIpv6Array = explode(",", $userDefined["nei-ce-ip-ipv6"]);
					
					$ipIndex = $deviceMode == "primary" ? 0 : 1;
					
					$ipv6WanAddress = isset($ipv6WanAddressArray[$ipIndex]) ? $ipv6WanAddressArray[$ipIndex] : "";
					$neiCeIpIpv6 = isset($neiCeIpIpv6Array[$ipIndex]) ? $neiCeIpIpv6Array[$ipIndex] : "";
					if (!empty($ipv6WanAddress) && !empty($neiCeIpIpv6)) {
						$resulIpv6["ipv6-wan-address"] = $ipv6WanAddress;
						$resulIpv6["nei-ce-ip-ipv6"] = $neiCeIpIpv6;
					}
				}
				
				// IPV6 from Pool if not coming form UD
				if (empty($resulIpv6)) {
					$resulIpv6 = Ipv6PoolAssignment::getIpv6PoolAssignment($service_instance_id, $template_id, $device_id, 2, [
						$variablesIdMap["ipv6-wan-address"] => "ipv6-wan-address",
						$variablesIdMap["nei-ce-ip-ipv6"] => "nei-ce-ip-ipv6",
					]);
					$resulIpv6["ipv6-wan-address"] = $resulIpv6["ipv6-wan-address"] . '/' . $resulIpv6['subnet'];
				}
				$this->data["ipv6"][] = $resulIpv6;

				$interfaceArray['ipv6-wan-address'] = $resulIpv6["ipv6-wan-address"];

				$interface[] = $interfaceArray;
				$vlan[]['vlan-id'] = (int) $customerVlan;
				$this->data['vlan']=$vlan;
				$this->data[$hostname]["cust-vlan"] = (int) $customerVlan;
				$this->data["cust-vlan"][] = (int) $customerVlan;
				$i++;
				if(strtolower($this->data['userDefined']['pe-type']) == 'single'){	
					break;
				}
			} // if for AAR device ends here
			
		} // parents for each loop ends here// logic to create AAR/AG2 device interfaces starts here
		// logic for ESR to ESR devices

		// Get Device Type
		foreach ($topology['neighbours'] as $k => $v) {
			if ($v['hostname'] == $hostname2) {
				$deviceType = $v['devicetype'];
				break;
			}
		}
		$data = $userDefined;

		//Get Interface for CSS1 To CSS2  and CSS2 To CSS1
		//if (in_array($this->data['sub_service_id'], ["2.46","2.47",2.49,"2.49",2.5,"2.5"])) {
			if(!$dualHomed && isset($this->data['userDefined']['pe-type']) && strtolower($this->data['userDefined']['pe-type']) == 'single'){
		
			$interface[] = $this->getCssToDemarcInterface($hostname1, $data, 'primary', $dualHomed, $vlan,$topology,$ag2Devices);		
		}else{
			if (in_array($this->data['sub_service_id'], [2.55,2.56,"2.55","2.56",2.58,"2.59",2.58,"2.59"])) {
				$interface[] = $this->getCssToDemarcInterface($hostname1, $data, 'primary', $dualHomed, $vlan,$topology,$ag2Devices);
				$interface[] = $this->getCssToDemarcInterface($hostname2, $data, 'secondary', $dualHomed, $vlan,$topology,$ag2Devices);
			}else{
				$interface[] = $this->getCssToCssInterface($hostname1, $hostname2, 'primary', $topology, $ipType, $dualHomed, $vlan, $hostname1,$custVlanArray,$topology);
				$interface[] = $this->getCssToDemarcInterface($hostname1, $data, 'primary', $dualHomed, $vlan,$topology,$ag2Devices);
		//Get Interface for CSS2 To CSS1

				$interface[] = $this->getCssToCssInterface($hostname2, $hostname1, 'secondary', $topology, $ipType, $dualHomed, $vlan, $hostname2,$custVlanArray);
				$interface[] = $this->getCssToDemarcInterface($hostname2, $data, 'secondary', $dualHomed, $vlan,$topology,$ag2Devices);

			}	
				
		
			}
		

		foreach ($interface as $interfaceArray) {
			$device = $interfaceArray["connected-device"];
			foreach ($interfaceArray as $variable => $value) {

				$this->variables[$device][$variable] = $value;
			}
		}
		return $interface;

	} // function to generate the interface ends here

	private function getCssToCssInterface($hostname1, $hostname2, $deviceMode, $topology, $ipType, $dualHomed, $vlan, $deviceType,$custVlanArray) {

		$interfaceECR1toECR2 = ResourceManager::getNeibhourHost($hostname1, $hostname2);

		// get the trunk number for ECR1 to ECR2
		$trunkNo = ResourceManager::trunk_no($hostname1, $interfaceECR1toECR2);

		//$serviceInst[]  =array();

		// old logic to find out thenew interface from port_1_service_instance
		//$inst_no = ResourceManager::service_inst($hostname1,$interfaceECR1toECR2);
		//$vlan_raps = ResourceManager::vlan_raps($hostname1);
		// calculate the ring number
		$ring = ResourceManager::ring_number($hostname1,$interfaceECR1toECR2); //-- commented on 9th aug
		$intf_facing_ecr = substr($interfaceECR1toECR2, 0, 3) . string_after_number($interfaceECR1toECR2);
		// Service Instance

		$intfECRtoECRPrimary = array();				
		$intfECRtoECRPrimary['interface-id'] = $this->setDemarcInterface($intf_facing_ecr);		
		
		$intfECRtoECRPrimary['connected-device'] = $deviceType;
		$intfECRtoECRPrimary['ip-type'] = $ipType;
		//$intfECRtoECRPrimary['dual-homed'] = $dualHomed;
		$intfECRtoECRPrimary['device-mode'] = $deviceMode;
		$intfECRtoECRPrimary['customer-vlan'] = $vlan;
		if (strstr(strtolower($ring), "major")) {
			// trunk no will be applicable for major ring only
			$intfECRtoECRPrimary['trunk-no'] = $trunkNo;
			$inst_no = ResourceManager::get_instance_number($hostname, $ring);
			$this->data['inst-no'][] = $serviceInst[0]['inst-no'] = $inst_no;
			$this->data['raps-vlan'][] = $serviceInst[0]['raps-vlan'] = (int) $vlan_raps;//$this->data["userDefined"]['demarc-raps-vlan-1'];
			if (!in_array($this->data['sub_service_id'], [2.91, "2.91", 2.92, "2.92",2.94,2.95,"2.94","2.95"])) {
					$this->data['inst-no'][] = $serviceInst[1]['inst-no'] = $inst_no + 10;
					$this->data['raps-vlan'][] = $serviceInst[1]['raps-vlan'] = (int) $vlan_raps + 1;//$this->data["userDefined"]['demarc-raps-vlan-2'];
				}
			
			/* if(!empty($custVlanArray)){
			 $k=2;
			 foreach($custVlanArray as $cust_vlan_id){
                            $this->data['inst-no'][] = $serviceInst[$k]['inst-no'] =  $cust_vlan_id;
                            $this->data['raps-vlan'][] = $serviceInst[$k]['raps-vlan'] = (int) $cust_vlan_id;//$this->data["userDefined"]['demarc-raps-vlan-2'];
				$k++;
                        }} */

			$intfECRtoECRPrimary['service-instance-no'] = $serviceInst;
			
		} else {
			//$this->data['inst-no'][] = "001";
			//$this->data['raps-vlan'][] = $this->data["userDefined"]['demarc-raps-vlan-1'];//(int) $vlan_raps;//$this->data["userDefined"]['demarc-raps-vlan-1'];
			//$this->data['inst-no'][] = "002";
			//$this->data['raps-vlan'][] = $this->data["userDefined"]['demarc-raps-vlan-1']+10;//$this->data["userDefined"]['demarc-raps-vlan-2'];
                          //  $this->data['inst-no'][] =  $v_lan;
                          //  $this->data['raps-vlan'][] = (int) $v_lan;//$this->data["userDefined"]['demarc-raps-vlan-2'];
			 if(!empty($custVlanArray)){
			foreach($custVlanArray as $cust_vlan_id){
                  $this->data['inst-no'][] = $serviceInst['inst-no'] =  (int) $cust_vlan_id;
                  $this->data['raps-vlan'][] = $serviceInst['raps-vlan'] = (int) $cust_vlan_id;//$this->data["userDefined"]['demarc-raps-vlan-2'];
                  $inst[] = $serviceInst;
                }
            } 
            $intfECRtoECRPrimary['service-instance-no'] = $inst;
			
		}
		return $intfECRtoECRPrimary;
	}

	private function getCssToCssInterfaceBackup($hostname1, $hostname2, $deviceMode, $topology, $ipType, $dualHomed, $vlan, $deviceType) {

		$interfaceECR1toECR2 = ResourceManager::getNeibhourHost($hostname1, $hostname2);

		// get the trunk number for ECR1 to ECR2
		$trunkNo = ResourceManager::trunk_no($hostname1, $interfaceECR1toECR2);

		//$serviceInst[]  =array();
		$inst_no = ResourceManager::service_inst($hostname1, $interfaceECR1toECR2);
		$vlan_raps = ResourceManager::vlan_raps($hostname1);

		$intf_facing_ecr = substr($interfaceECR1toECR2, 0, 3) . string_after_number($interfaceECR1toECR2);

		// Service Instance
		$this->data['inst-no'][] = $serviceInst[0]['inst-no'] = $inst_no;
		$this->data['raps-vlan'][] = $serviceInst[0]['raps-vlan'] = (int) $vlan_raps;
		$this->data['inst-no'][] = $serviceInst[1]['inst-no'] = $inst_no + 1;
		$this->data['raps-vlan'][] = $serviceInst[1]['raps-vlan'] = (int) $vlan_raps + 1;

		$intfECRtoECRPrimary = array();
		$intfECRtoECRPrimary['interface-id'] = $intf_facing_ecr;
		$intfECRtoECRPrimary['connected-device'] = $deviceType;
		$intfECRtoECRPrimary['ip-type'] = $ipType;
		$intfECRtoECRPrimary['dual-homed'] = $dualHomed;
		$intfECRtoECRPrimary['device-mode'] = $deviceMode;
		$intfECRtoECRPrimary['customer-vlan'] = $vlan;
		$intfECRtoECRPrimary['trunk-no'] = $trunkNo;
		$intfECRtoECRPrimary['service-instance-no'] = $serviceInst;

		return $intfECRtoECRPrimary;
	}

	/*
			{                // ESR/css -> Demarc
		                "interface-id": "Gig0/0/5",  --- For Now UD / Demarc Facing interface - Done
		                "connected-device": "ASR920_ECR1", - Done
		                "device-mode": "primary", - Done
		                "ip-type": "both", - Done
		                "dual-homed": "true", - Done
		                "trunk-no": 9, -- UD for Now / Otherwise from inventory send CSS hostname - Done
		                "customer-vlan": [  Same as AAR device customer vlan id( PRmary Secondary Both)
		                  {
		                    "vlan-id": 12
		                  },
		                                                            {
		                    "vlan-id": 13
		                  }
		                ],
		                "service-instance-no": [ depend on sub-service-id if it is dual then take 2 input form user otherwise single
		                  {
		                    "inst-no": 21,	--- UD for Now
		                    "raps-vlan": 38 --- UD for Now
		                  },
		                                                            {
		                    "inst-no": 22,	-- UD For Now
		                    "raps-vlan": 39 --- UD for Now
		                  }
		                ]
		              },
	*/
	private function getCssToDemarcInterface($hostname, $data, $deviceMode, $dualHomed, $vlan,$topology,$ag2Devices) {

		$id = $deviceMode == "primary" ? 1 : 2;

		$interface['interface-id'] = $data["demarc-interface-id-" . $id];
		$interface['connected-device'] = $hostname; //$data["demarc-device"];
		$interface['device-mode'] = $deviceMode;
		$interface['ip-type'] = $data["ip-type"];
		//$interface['dual-homed'] = $dualHomed;	

		$interface['trunk-no'] = $this->data['trunk_no'];//ResourceManager::trunk_no($hostname, $$data["demarc-interface-id-" . $id]);//$this->data['trunk_no'];
		//if($this->data['pe-type'] == 'single' && !$dualHomed){
		if (in_array($this->data['sub_service_id'], [2.46,2.47,"2.46","2.47",2.49,"2.49",2.5,"2.5",2.55,2.56,"2.55","2.56",2.58,"2.58",2.59,"2.59",2.64,2.65,2.67,2.68,'2.64','2.65','2.67','2.68'])) {
			unset($interface['trunk-no']);
		}
		//1;// it will be derived from inventory // UD $data["demarc-trunk-no-1"];
		if (in_array($this->data['sub_service_id'], [2.5,"2.5",2.55,2.56,"2.55","2.56",2.58,"2.58",2.59,"2.59"])) {
		if($id == 1){
			$interface['customer-vlan'] = $vlan[0];
		}else{
			$interface['customer-vlan'] = $vlan[1];
		}
		}else{
			$interface['customer-vlan'] = $vlan;
		}
		$instNo = $data["demarc-inst-no-" . $id];
		$this->data['demarc-inst-no'][$deviceMode] = $instNoArray = explode(",", $instNo);

		$rapsVlan = $data["demarc-raps-vlan-" . $id];
		$rapsVlanArray = explode(",", $rapsVlan);

		/*foreach($instNoArray as $key=>$instNo){
				$interface['service-instance-no'][$key]   = [
					"inst-no"=> $instNo,
					//"raps-vlan"=> $rapsVlanArray[$key],
					"raps-vlan"=> $rapsVlan,
				];
			}			*/
		//foreach($instNoArray as $key=>$instNo){
		/*for($i=0;$i<=1;$i++){
				$interface['service-instance-no'][$i]   = [
					"inst-no"=> $instNo,
					//"raps-vlan"=> $rapsVlanArray[$key],
					"raps-vlan"=> $rapsVlan,
				];
			}	*/
		/*$interface['service-instance-no'][0]['inst-no'] = $data["demarc-inst-no-1"];
		$interface['service-instance-no'][0]['raps-vlan'] = $this->data['raps-vlan'][0]; //$data["demarc-raps-vlan-1"];
		$interface['service-instance-no'][1]['inst-no'] = $data["demarc-inst-no-2"];
		$interface['service-instance-no'][1]['raps-vlan'] =$this->data['raps-vlan'][1]; // $data["demarc-raps-vlan-2"];*/
			// calculate the ring number						
			// if($id == 2){
			// 	$this->data['ring_no'][1] = $ring = "SUB_PS2";					
			// 	$inst_no = "405";		
			// }else{
				$this->data['ring_no'][] = $ring = ResourceManager::ring_number($hostname,$interface['interface-id']); // newly added 9th aug					
				//$inst_no = ResourceManager::get_instance_number($hostname,$ring);	\$this->data['ring_no'][] = $ring = ResourceManager::ring_number($hostname,$interface['interface-id']); // newly added 9th aug		

				$inst_no = ResourceManager::get_instance_number($hostname,$ring);	
					if(!isset($inst_no) && $inst_no <1){
					$this->data['errors'] = "Instance number not found for demarc device $hostname having interface as ".$interface['interface-id'];
					return false;
				}
			//}
			if (!isset($interface['trunk-no'])) {
				$this->data['demarc-inst-no'][] = $serviceInst[0]['inst-no'] = $instNo =(int) $inst_no+10;
			}else{
				$this->data['demarc-inst-no'][] = $serviceInst[0]['inst-no'] = $instNo =(int) $inst_no;
			}
			
			if (!in_array($this->data['sub_service_id'], [2.46,2.47,"2.46","2.47",2.49,"2.49",2.64,2.65,2.67,2.68,'2.64','2.65','2.67','2.68'])) {
				$this->data['demarc-raps-vlan'][] = $serviceInst[0]['raps-vlan'] = $this->data["userDefined"]['demarc-raps-vlan-1'];			
			}
			if (in_array($this->data['sub_service_id'], [2.55,2.56,"2.55","2.56",2.58,"2.58",2.59,'2.59',2.64,2.65,2.67,2.68,'2.64','2.65','2.67','2.68'])) {
			if($id == 1){
			$this->data['demarc-raps-vlan'][] = $serviceInst[0]['raps-vlan'] =   $vlan[0]['vlan-id'];
				}else{
					$this->data['demarc-raps-vlan'][] = $serviceInst[0]['raps-vlan'] =   $vlan[1]['vlan-id'];
				}				
			}	
			//$vlan_raps;//$this->data["userDefined"]['demarc-raps-vlan-1'];
			
			if (in_array($this->data['sub_service_id'], [2.46,2.47,"2.46","2.47",2.49,"2.49",2.5,"2.5",2.55,2.56,"2.55","2.56",2.58,"2.58",2.59,"2.59",2.64,2.65,2.67,2.68,'2.64','2.65','2.67','2.68'])) {
					$deviceType = substr($hostname,8,3);
					
					$neiAg2Ip = [];
					foreach ($topology["parents"] as $parentDevice) {
							if (strpos($parentDevice["hostname"], "PAR") !== false) { //AG1 Devices
								$ag1Devices[] = $parentDevice["loopback0"];
							}
					 }
					 
					foreach ($ag2Devices as $device) {
						foreach ($topology["parents"] as $parentDevice) {				
							if ($parentDevice["hostname"] == $device) {
								$neiAg2Ip[] = $parentDevice["loopback0"];
							}
						}
					}					
					if($deviceMode == 'primary'){
					if(in_array($deviceType,["ESR","CSS"])){
						$serviceInst[0]['nei-ip'] =  $ag1Devices[0];
					}
					else if($deviceType == "ECR"){ 
						$serviceInst[0]["nei-ip"] =  $neiAg2Ip[0];
					}
					$serviceInst[0]['vpn-id'] = $this->data['vpn-id'];
					}else{
					if(in_array($deviceType,["ESR","CSS"])){
						$serviceInst[0]['nei-ip'] =  $ag1Devices[1];
					}
					else if($deviceType == "ECR"){ 
						$serviceInst[0]["nei-ip"] =  $neiAg2Ip[1];
					}
					$serviceInst[0]['vpn-id'] = $this->data['vpn-id'];
					
					}
				//$this->data['demarc-inst-no'][] = $serviceInst[0]['inst-no'] = 
				//$this->data['demarc-inst-no'][] = $serviceInst[0]['inst-no'] = 
			}
			//if (!in_array($this->data['sub_service_id'], [2.91, "2.91", 2.92, "2.92",2.94,2.95,"2.94","2.95",2.46,2.47,"2.46","2.47",2.49,"2.49",2.5,"2.5"])) {
				if($dualHomed && isset($this->data['userDefined']['pe-type']) && strtolower($this->data['userDefined']['pe-type']) == 'dual' && in_array($this->data['sub_service_id'], [2.37, "2.37", 2.38, "2.38",2.40,2.41,"2.40","2.41"])){
						$this->data['demarc-inst-no'][] = $serviceInst[1]['inst-no'] = $instNo + 10;
						$this->data['demarc-raps-vlan'][] = $serviceInst[1]['raps-vlan'] = (int) $this->data["userDefined"]['demarc-raps-vlan-2'];
				}
		//$this->data["userDefined"]['demarc-raps-vlan-2'];
			$interface['service-instance-no'] = $serviceInst;
		return $interface;
	}

	public function getPHWE($topology, $ipType, $custvlanpool, $serviceInstance, $variablesIdMap) {
		$phweArr = array();
		$phwe = array();
		$i = 1;
		$template_id = $serviceInstance["serviceModel"]["serviceModelTemplate"][0]["id"];
		foreach ($topology['parents'] as $key => $val) {
			if (strstr($val['hostname'], "AAR")) {
				//AG2
				$this->sdVariables[$val['hostname']][$this->variabls['sd_val']['hostname']]= $phweArr['ag2-device'] = $val['hostname'];
				$phweArr['ip-type'] = $ipType;
				$bviConfig = array();
				$this->sdVariables[$val['hostname']][$this->variabls['sd_val']['bvi-no']] = $bviConfig['bvi-no'] = $this->data[$val['hostname']]['cust-vlan'];

				$this->sdVariables[$val['hostname']][$this->variabls['sd_val']['vrf-name']] = $bviConfig['vrf-name'] = $this->data["vrf-name"];
				$phweArr['bvi-config'] = $bviConfig;
				$routingProtocol = array();
				$device = Device::getDeviceByHostname($val['hostname']);
				$device_id = $device["id"];

				$ipv4Assignment = new Ipv4Assignment();
				$resultIpv4 = $ipv4Assignment->asssignIp($serviceInstance['id'], $template_id, $device_id, 1, [
					$variablesIdMap["ce-intf-ip"] => "ce-intf-ip",
				]);
				$this->sdVariables[$val['hostname']][$this->variabls['sd_val']['ce-intf-ip']] = $routingProtocol["ce-intf-ip"] = $resultIpv4['ce-intf-ip'];
				$this->sdVariables[$val['hostname']][$this->variabls['sd_val']['cust-lan-pool']] = $routingProtocol['cust-lan-pool'] = $custvlanpool;
				$phweArr['routing-protocol'] = $routingProtocol;
				$phwe[] = $phweArr;
				if(strtolower($this->data['userDefined']['pe-type']) == 'single'){	
					break;
				}
				$i++;
			}
		}
		return $phwe;
	}

	public function getL2VPN($hostname1, $hostname2, $topology, $template_id, $customer_id, $variablesIdMap) {
	
		$l2vpnArr = array();
		$l2vpn = array();
		$i = 1;
		$devices = array();
		// Get AG1 devices from topology
		foreach ($topology["parents"] as $parentDevice) {
			if (strpos($parentDevice["hostname"], "PAR") !== false) {
				$devices["ag1"][] = $parentDevice["loopback0"];
			}
		}
	
		foreach ($topology["neighbours"] as $neighDevice) {
			
				// Get  CSS devices from topology
			if (strpos($neighDevice["hostname"], "ESR") !== false || strpos($neighDevice["hostname"], "CSS") !== false) {
				$devices["esr"][] = $neighDevice["loopback0"];
			}
			
			// Get ECR devices from topology
			if (strpos($neighDevice["hostname"], "ECR") !== false) {
				$devices["ecr"][] = $neighDevice["loopback0"];
			}
		}
		//pe($topology,$devices);
		
		
		
		
		foreach ($topology['parents'] as $key => $val) {
			if (strstr($val['hostname'], "AAR")) { 	// for AG2
			
				$device_id = Device::getDeviceByHostname($val['hostname']);
				$l2vpnArr['name'] = "L2vpnAG2Device-" . $i;
				$l2vpnArr['device'] = $val['hostname'];
				$l2vpnArr['pw-class'] = ResourceManager::getP2PCrossConnect($template_id, $device_id, $variablesIdMap["pw-class"], $this->data['customer_id']);
				$l2vpnArr['bri-grp-name'] = ResourceManager::getBridgeGroupName($template_id, $device_id, $variablesIdMap["bri-grp-name"], $this->data['customer_id']);
				$l2vpnArr['bri-dom-name'] = ResourceManager::getBridgeGroupName($template_id, $device_id, $variablesIdMap["bri-dom-name"], $this->data['customer_id']);
				$neighbor = array();
				if ($i == 1) {
					if (strstr($hostname1, 'CSS') || strstr($hostname1, 'ESR')) {
						$neighbor['ip'] = $devices['ag1'][$i - 1];
						$neighbor['vpn-id'] = $this->data["vpn-id"];
					} else if (strstr($hostname1, 'ECR')) {
						$neighbor['ip'] = $topology['loopback0'];
						$neighbor['vpn-id'] = $this->data["vpn-id"];
					}
					
				} else {
				
					if (strstr($hostname2, 'CSS') || strstr($hostname2, 'ESR')) {
						$neighbor['ip'] = $devices['ag1'][$i-1];
						$neighbor['vpn-id'] = $this->data["vpn-id"];
					} else if (strstr($hostname2, 'ECR')) {
						$neighbor['ip'] = $devices['ecr'][0];
						$neighbor['vpn-id'] = $this->data["vpn-id"];
					}
				}

				$l2vpnArr['neighbor'] = $neighbor;
				$l2vpnArr['cust-vlan'] = "BVI" . $this->data[$val['hostname']]["cust-vlan"];
				$l2vpn[] = $l2vpnArr;
				$i++;
				if(strtolower($this->data['userDefined']['pe-type']) == 'single'){	
					break;
				}
			}
			
		}
		return $l2vpn;
	}

	// function to generate the Erps Config module for L3vpn
	public function getErpsConfig($topology, $hostname1, $hostname2, $userDefined,$service_instance_id) {
		foreach ($topology["parents"] as $parentDevice) {
			if (strpos($parentDevice["hostname"], "AAR") !== false) {
				$devices["ag2"][] = $parentDevice["hostname"];
			}
		}
		$inclusionVlan = $hostname1.",".$hostname2;
		$model = ServiceInstance::findOne($service_instance_id);
		$model->inclusion_hostname = $inclusionVlan;
		$model->save();
		$erpsConfig = array();
		$instNO = rand(2, 2000);
		if(strtolower($this->data['userDefined']['pe-type']) == 'single'  && $this->data['dualhomed'] === false){	
			$erpsConfig[] = $this->getErpsConfigBlock($hostname1, $hostname2, 'primary', $instNO, $devices, $userDefined);
		}else{
		$erpsConfig[] = $this->getErpsConfigBlock($hostname1, $hostname2, 'primary', $instNO, $devices, $userDefined);
		$erpsConfig[] = $this->getErpsConfigBlock($hostname2, $hostname1, 'secondary', $instNO + 2, $devices, $userDefined);	
	}		
		//}
		return $erpsConfig;

	}
	public function getErpsConfigBlock($hostname, $neighbors_hostname, $deviceMode, $instNo, $devices, $userDefined) {
	
		$erps = array();	
		
		$erps['device'] = $hostname;
		if ($deviceMode == "secondary") {
			$ringNo =  $this->data['ring_no'][1];
		}else{
			$ringNo =  $this->data['ring_no'][0];
		}
		//$ringNo =  $this->data['ring_no'];//ResourceManager::ring_number_new($hostname); //ResourceManager:: ring_no($hostname);//ResourceManager:: ring_data($hostname);
		$erps['ring-no'] = $ringNo;
		$erps['device-mode'] = $deviceMode;
		$erps['erps-desc'] = ResourceManager:: description_erps($hostname,$ringNo);//ResourceManager::ring_number($hostname); // as per new logic ResourceManager:: description_erps($ringNo);
		
		// If ring no sub ring then exclusion list will not added
		//if(strpos(strtolower($ringNo),"sub")===false){ // removed 
			
		if(strtolower($this->data['userDefined']['pe-type']) == 'single'){	
		$erps['exclusion-list'][]["vlan-id"] = 	$this->data[$devices['ag2'][0]]['cust-vlan'];//json_decode(ResourceManager::exclusion_vlan($hostname, $neighbors_hostname, ''), true); 		
			//$erps['exclusion-list'][]["vlan-id"] = 	$this->data[$devices['ag2'][1]]['cust-vlan'];
			
		}else{
			$erps['exclusion-list'][]["vlan-id"] = 	$this->data[$devices['ag2'][0]]['cust-vlan'];//json_decode(ResourceManager::exclusion_vlan($hostname, $neighbors_hostname, ''), true); 		
			$erps['exclusion-list'][]["vlan-id"] = 	$this->data[$devices['ag2'][1]]['cust-vlan'];
		}	
			
		//}
		$erps['major-ring-name'] = ResourceManager::get_major_ring_number($hostname);
		$erps['intf-facing-other'] = ResourceManager::intf_facing_ecr($hostname, $neighbors_hostname, '');
		//$erps['intf-facing-demarc'] = //rand(10,100);  //$intf_facing_demarc; // UD Value rand Value for now
		if ($deviceMode == "secondary") {
			//$erps['rpl-port-owner'] = "port1"; // it can e done by internally Value Resource Mapping
			$erps['intf-facing-demarc'] = $this->data['userDefined']['demarc-interface-id-2'];
		} else {
			$erps['intf-facing-demarc'] = $this->data['userDefined']['demarc-interface-id-1'];
		}
		$erps['rpl-port-owner'] = "port0"; // it can be done by internally Value Resource Mapping
		$interface = ResourceManager::getNeibhourHost($hostname, $neighbors_hostname);

		$erpsInst = array();
		for ($i = 0; $i <= 1; $i++) {
			$erpsInst['erps-inst-no'] = $i + 1;
			//$erpsInst['cust-vlan'] = $this->data[$devices['ag2'][$i]]['cust-vlan'];
			//$erpsInst['raps-vlan'] = $this->data['raps-vlan'][$i];
			
			if($i == 0){
			if(strtolower($this->data['userDefined']['pe-type']) == 'single'){						
					$erpsInst['inclusion-list'] = json_decode(ResourceManager::getVlans($hostname, $neighbors_hostname, $this->data[$devices['ag2'][0]]['cust-vlan'],$ringNo,$this->data['raps-vlan'][0]),true);
				}else{
					$erpsInst['inclusion-list'] = json_decode(ResourceManager::getVlans($hostname, $neighbors_hostname, $this->data[$devices['ag2'][$i]]['cust-vlan'],$ringNo,$this->data['raps-vlan'][$i]),true);
				}
			}else{
				$incl[]['vlan-id'] = $this->data[$devices['ag2'][$i]]['cust-vlan'];
				$incl[]['vlan-id'] = $this->data['demarc-raps-vlan'][$i];
				$erpsInst['inclusion-list'] = $incl;
			}
			if(strtolower($this->data['userDefined']['pe-type']) == 'single'){	
			$erpsInst['service-inst-other'] = (string) $this->data['inst-no'][0]; //ResourceManager::service_inst($hostname,$interface);			
			}else{
			$erpsInst['service-inst-other'] = (string) $this->data['inst-no'][$i]; //ResourceManager::service_inst($hostname,$interface);
			}
			//$erpsInst['service-inst-demarc'] =  $this->data['demarc-inst-no'][$deviceMode][$i];
			$erpsInst['service-inst-demarc'] = (string) $this->data['demarc-inst-no'][$i];//$this->data["userDefined"]["demarc-inst-no-" . $erpsInst['erps-inst-no']];
			$erpsInstance[] = $erpsInst;
			if(strtolower($this->data['userDefined']['pe-type']) == 'single'){	
					break;
				}
		}
		
		$erps['erps-inst-list'] = $erpsInstance;
		return $erps;
	}

	// function to generate l3vpn-static-xr
	public function getL3vpnStaticXR($topology, $service_model_template_id, $variablesIdMap, $customer_id, $custlanpool, $phwe, $ipType, $staticBlock) {
		$l3vpnStatic = array();
		$l3vpnStatic['vrf-name'] = $this->data["vrf-name"];
		$l3vpnStatic['cust-lan-pool'] = $custlanpool;
		$l3vpnStatic['bvi-nei-address-family'] = $ipType;
		$l3vpnStatic['bvi-nei-ip'] = $phwe[0]['routing-protocol']['ce-intf-ip'];
		$l3vpnStatic['rd'] = $staticBlock[0]['rd'];
		return $l3vpnStatic;
	}

	// function to generate static block
	public function getStaticBlock($topology, $ipType, $customer_lan_pool_ipv4, $customer_lan_pool_ipv6, $phwe, $service_model_template_id, $variablesIdMap, $customer,$userDefined) {
		$staticBlock = array();
		$neiCeIpIpv6Array = explode(",", $userDefined["nei-ce-ip-ipv6"]);
		$neiCeIpIpv4Array = isset($userDefined["nei-ce-ip-ipv4"]) ? explode(",", $userDefined["nei-ce-ip-ipv4"]) : [];
		if(strpos($customer_lan_pool_ipv4,",") !== false){		
			$lanpool_ipv4 = explode(",", $customer_lan_pool_ipv4);
			$lanpool_ipv6 = explode(",", $customer_lan_pool_ipv6);
		}else{
			$lanpool_ipv4[0] = $lanpool_ipv4[1] = trim($customer_lan_pool_ipv4);
			$lanpool_ipv6[0] = $lanpool_ipv6[1] = trim($customer_lan_pool_ipv6);
		}
		$neiCeIpIpv6 = $neiCeIpIpv4 = "";
		

		$block = array();
		$i = 0;
		$rand = rand(2, 10000);
		foreach ($topology['parents'] as $key => $val) {			
			if (strstr($val['hostname'], "AAR")) {
				if (in_array($ipType, ["ipv4", "both"])) {		

				/*$keyIpv4 = ($role == "Primary") ? 0 : 1;
				if (!empty($neiCeIpIpv4Array) && isset($neiCeIpIpv4Array[$keyIpv4]) && !empty($neiCeIpIpv4Array[$keyIpv4])) {
					$neiCeIpIpv4 = $neiCeIpIpv4Array[$keyIpv4];
				} else {
					$neiCeIpIpv4 = $phwe[$key]["routing-protocol"]["ce-intf-ip"];
				}*/
			}
			
			if (isset($this->data["ipv6"][$key]["nei-ce-ip-ipv6"])) {
					// Jugad
					$neiCeIpIpv6 = $this->data["ipv6"][$key]["nei-ce-ip-ipv6"];
				}
				//AG2
				$role = $i == 0 ? "Primary" : "Secondary";
				if (!empty($neiCeIpIpv4Array) && isset($neiCeIpIpv4Array[$i]) && !empty($neiCeIpIpv4Array[$i])) {
					$neiCeIpIpv4 = $neiCeIpIpv4Array[$i];
				} else {
					$neiCeIpIpv4 = $phwe[$key]["routing-protocol"]["ce-intf-ip"];
				}
				$deviceID = Device::getDeviceByHostname($val['hostname']);
				$block['device'] = $val['hostname'];
				$block['role']  = $role;
				$block['vrf-name'] = $this->data['vrf-name'];
				$block['address-family'] = $ipType;
				$block['customer-lan-pool-ipv4'] = trim($lanpool_ipv4[$i]);//trim($customer_lan_pool_ipv4);
				$block['customer-lan-pool-ipv6'] =  trim($lanpool_ipv6[$i]);//trim($customer_lan_pool_ipv6);
				$block['ipv4-address'] = $neiCeIpIpv4;//$phwe[$i]['routing-protocol']['ce-intf-ip'];
				$block['ipv6-address'] = $neiCeIpIpv6Array[$i];
				$block['route-policy-ipv4'] = ResourceManager::getFttpLp($customer['company_name'], "ipv4");	
				$block['route-policy-ipv6'] = ResourceManager::getFttpLp($customer['company_name'], "ipv6");
				 

				//$block['ipv6-address'] = $phwe[$i]['routing-protocol']['ce-intf-ipv6'];
				$block['rd'] = ResourceManager::getRD($service_model_template_id, $deviceID, $variablesIdMap['rd'], $customer['id']);
				if ($i == 0) {
					$block['bgp-id'] = 55836;//$rand;
				} else {
					$block['bgp-id'] = 55836;//$rand + 1;
				}
				$i++;
				$staticBlock[] = $block;
				if(strtolower($this->data['userDefined']['pe-type']) == 'single'){	
					break;
				}
			}

		}		
		
		return $staticBlock;
	}
	
	public function getPrefixRoutePolicy($topology, $ipType, $lan_pool_ipv4_mask, $lan_pool_ipv6_mask, $customer_name) {
	
			$routePolicy = array();
			$route = array();
			if(strpos($lan_pool_ipv4_mask,",") !== false){		
			$lanpool_ipv4 = explode(",", $lan_pool_ipv4_mask);
			$lanpool_ipv6 = explode(",", $lan_pool_ipv6_mask);
		}else{
			$lanpool_ipv4[0] = $lanpool_ipv4[1] = trim($lan_pool_ipv4_mask);
			$lanpool_ipv6[0] = $lanpool_ipv6[1] = trim($lan_pool_ipv6_mask);
		}
			$i=0;
			foreach ($topology['parents'] as $key => $val) {
							
					if (strstr($val['hostname'], "AAR")) {
					
							$role =  empty($route)?"Primary" : "Secondary";
							$routePolicy['deviceName'] = $val['hostname'];
							$routePolicy['ip-type'] = $ipType;
							if (in_array($ipType, ["both", "ipv4"])) {
											$routePolicy['prefix-set-ipv4'] = ResourceManager::getFttpLp($customer_name, "ipv4");
											$routePolicy['lan-pool-ipv4-mask'] = $lanpool_ipv4[$i]; //trim($lan_pool_ipv4_mask);
											$routePolicy['lp-route-policy-v4'] = ResourceManager::getFttpLp($customer_name, 'ipv4');
							}
							if (in_array($ipType, ["both", "ipv6"])) {
											$routePolicy['prefix-set-ipv6'] = ResourceManager::getFttpLp($customer_name, "ipv6");
											$routePolicy['lan-pool-ipv6-mask'] =$lanpool_ipv6[$i];//trim($lan_pool_ipv6_mask);
											$routePolicy['lp-route-policy-v6'] = ResourceManager::getFttpLp($customer_name, 'ipv6');
							}                                                              
							
							$routePolicy["role"] = $role;
							
							$route[] = $routePolicy;
							$i++;		
					}

			}
			if(strtolower($this->data['userDefined']['pe-type']) == 'single'){	
								$route = $route[0];
								$route['role'] ="Secondary";
			}
			return $route;
	}              

				
	public function getPrefixRoutePolicyOld_27072018($topology, $ipType, $lan_pool_ipv4_mask, $lan_pool_ipv6_mask, $customer_name) {
		$routePolicy = array();
		foreach ($topology['parents'] as $key => $val) {
			if (strstr($val['hostname'], "AAR")) {
				$routePolicy['deviceName'] = $val['hostname'];
				$routePolicy['ip-type'] = $ipType;

				if (in_array($ipType, ["both", "ipv4"])) {
					$routePolicy['prefix-set-ipv4'] = ResourceManager::getFttpLp($customer_name, "ipv4");
				}

				if (in_array($ipType, ["both", "ipv6"])) {
					$routePolicy['prefix-set-ipv6'] = ResourceManager::getFttpLp($customer_name, "ipv6");
				}

				$routePolicy['lan-pool-ipv4-mask'] = $lan_pool_ipv4_mask;
				$routePolicy['lan-pool-ipv6-mask'] = $lan_pool_ipv6_mask;
				$routePolicy['lp-route-policy-v4'] = ResourceManager::getFttpLp($customer_name, 'ipv4');
				$routePolicy['lp-route-policy-v6'] = ResourceManager::getFttpLp($customer_name, 'ipv6');
				return $routePolicy;
			}
		}
	}

	public function getRoutePolicyPassAll($topology) {
		$passAllArr = array();
		$passAll = array();
		foreach ($topology['parents'] as $key => $val) {
			if (strstr($val['hostname'], "AAR")) {
				$passAllArr['route-policy-name'] = "rjil-pass-all";
				$passAllArr['device-name'] = $val['hostname'];
				$passAll[] = $passAllArr;
				if(strtolower($this->data['userDefined']['pe-type']) == 'single'){	
					break;
				}
			}
		}
		return $passAll;
	}

	public static function get_string_between($string, $start = "{", $end = "}") {
		$string = ' ' . $string;
		$ini = strpos($string, $start);
		if ($ini == 0) {
			return '';
		}

		$ini += strlen($start);
		$len = strpos($string, $end, $ini) - $ini;
		return substr($string, $ini, $len);
	}

	public static function findVariables($cli_config, $find_start = "{", $find_end = "}", $placeHolder = "##") {

		$cli_config_arr = explode("\n", $cli_config);
		$variables = [
			"replacemnets" => [],
			"master" => [],
		];
		foreach ($cli_config_arr as $key => $line) {
			$variable = self::get_string_between($line, $find_start, $find_end);
			if (!empty($variable) && strpos($variable, ":") === false) {
				$variables["replacemnets"]["{" . $variable . "}"] = '"' . $placeHolder . $variable . $placeHolder . '"';
				$variables["master"][] = $variable;

			}
		}
		$variables["master"] = array_unique($variables["master"]);
		$variables["replacements"] = array_unique($variables["replacemnets"]);
		return $variables;
	}



}
