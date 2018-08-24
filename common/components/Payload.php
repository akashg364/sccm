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
 * AAR = AG2
 * PAR = AG1
 * CSS/ESR or ECR
 *
 */
Class Payload {

	public $errors = [];
	public $data = [];
	public $variables = [];


	public function createPayload($service_instance_id, $devices, $userDefined) {

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

		// Now There is only one Service Template (Device role) so first taken
		$template_id = $serviceInstance["serviceModel"]["serviceModelTemplate"][0]["id"];
		$customer_id = $serviceInstance["customer_id"];

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

		foreach ($linkArray as $key => $link) {

			foreach ($link as $linkKey => $linkVal) {

				// Remove module from Payload if it is not required/assigned for Sub Service 
				if(is_array($linkVal) && !in_array($linkKey,$serviceInstance["components"]["modules"])){
					unset($linkArray[$key][$linkKey]);
					continue;
				}

				if ($linkKey == "interface") { // Done
					$interfaceModule = $this->getInterfaceModule($topology, $devices, $serviceInstance, $dualHomed, $isManaged, $variablesIdMap, $userDefined);
					$linkArray[$key][$linkKey] = $interfaceModule;
				} else if ($linkKey == "l2-vfi") { //Done
					$l2vfiModule = $this->getL2Vfi($hostname1, $hostname2, $ag2Devices, $topology);
					$linkArray[$key][$linkKey] = $l2vfiModule;
				} else if ($linkKey == "ag1-l2-ext") { //Done
					$ag1L2Ext = $this->getAg1L2Ext($hostname1, $hostname2, $topology);
					$linkArray[$key][$linkKey] = $ag1L2Ext;
				} else if ($linkKey == "phwe") { //Done
					$phwe = $this->getPHWE($topology,$userDefined["ip-type"], $userDefined['cust-lan-pool'], $serviceInstance, $variablesIdMap);
					$linkArray[$key][$linkKey] = $phwe;
				} else if ($linkKey == "l2vpn") { //Done
					$l2vpn = $this->getL2VPN($hostname1, $hostname2, $topology, $template_id, $customer_id, $variablesIdMap);
					$linkArray[$key][$linkKey] = $l2vpn;
				} else if ($linkKey == "vrf-definitions") { // Done
					$vrfDefinition = $this->getVrfDefinitions($ag2Devices, $userDefined, $customer_id, $service_id, $service_instance_id);
					//$vrfDefinition = $this->getVrfDefinitions($ag2Devices, $userDefined, $customer_id);
					$linkArray[$key][$linkKey] = $vrfDefinition;
				} else if ($linkKey == "bgp") { //Done
					$bgp = $this->getBgp($service_instance_id, $template_id, $ag2Devices, $ag2DevicesData, $customer, $variablesIdMap, $userDefined, $phwe);
					$linkArray[$key][$linkKey] = $bgp;
				} else if ($linkKey == "demarc") { // Done
					$demarc = $this->getDemarc($userDefined, $dualHomed);
					$linkArray[$key][$linkKey] = $demarc;
				} else if ($linkKey == "erps-config") { // Done
					$erpsConfig = $this->getErpsConfig($topology, $hostname1, $hostname2, $userDefined, $service_instance_id);
					$linkArray[$key][$linkKey] = $erpsConfig;
				} else if ($linkKey == "static") { // Done
					$staticBlock = $this->getStaticBlock($topology, $userDefined['ip-type'], @$userDefined['customer-lan-pool-ipv4'], @$userDefined['customer-lan-pool-ipv6'], $phwe, $template_id, $variablesIdMap, $customer_id);
					$linkArray[$key][$linkKey] = $staticBlock;
				} else if ($linkKey == "l3vpn-static-xr") { // Done
					$l3vpnStatic = $this->getL3vpnStaticXR($topology, $template_id, $variablesIdMap, $customer_id, $userDefined['cust-lan-pool'], $phwe, $userDefined['ip-type'], @$staticBlock);
					$linkArray[$key][$linkKey] = $l3vpnStatic;
				} else if ($linkKey == "prefix-set-and-route-policy") { // Done
					$prefixRoute = $this->getPrefixRoutePolicy($topology, $userDefined['ip-type'], $userDefined['lan-pool-ipv4-mask'], $userDefined['lan-pool-ipv6-mask'], $customer['company_name']);
					$linkArray[$key][$linkKey] = $prefixRoute;
				} else if ($linkKey == "route-policy-pass-all") { // Done
					$routeAll = $this->getRoutePolicyPassAll($topology);
					$linkArray[$key][$linkKey] = $routeAll;
				}

			}
			break; // Todo::Currently We have only one link in Payload in future we will implement it
		}

		$nsoPayloadArray[$payloadKey]["link"] = $linkArray;

		return [
			"payload" => $nsoPayloadArray,
			"variables" => [],
			"vpn-id" => $this->data["vpn-id"],
			"demarc-ingress-port-no" => $this->data["demarc-ingress-port-no"],
		];
	}

	

	public function getVrfDefinitions_old($ag2Devices, $userDefined, $customer_id) {
		$vrfDefinitions = array();
		$vrf = array();
		$ipType = $userDefined["ip-type"];
		$topologyType = isset($userDefined["topology"]) && !empty($userDefined["topology"]) ? $userDefined["topology"] : "hub";
		$rtExport = ResourceManager::getRT($customer_id, $qos = 'major');
		if ($topologyType == "hub") {
			for ($i = 0; $i <= 1; $i++) {
				// newly added
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

	public function getVrfDefinitions($ag2Devices, $userDefined, $customer_id, $service_id, $service_instance_id) {
		
		$model = new ServiceInstance();
		$service = $this->findCustomerService($customer_id, $service_id);
		if ($service) {
			$topologyType = "spoke";
			$value = explode("|", $service['rt_export_import']);
			$hubag2 = explode(",", $value[0]);
			$rt = explode(",", $value[1]);
			$rtExport = $rt[0];
			$rtImport = $rt[1];
		} else {
			$topologyType = "hub";
		}
		$vrfDefinitions = array();
		$vrf = array();
		$ipType = $userDefined["ip-type"];

		if ($topologyType == "hub") {
			$rtExport = ResourceManager::getRT($customer_id, $qos = 'major');
			$rtImport = ResourceManager::getRT($customer_id, $qos = 'major');
			$model = ServiceInstance::findOne($service_instance_id);
			$model->rt_export_import = implode(",", array_values($ag2Devices)) . "|" . $rtExport . "," . $rtImport;
			$model->save();
			for ($j = 1; $j <= $this->data["dual_homed_devices"]; $j++) {
				$i = $j-1;
				// newly added
				$vrfBlock = [
					"device" => $ag2Devices[$i], 
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
			}

		} else if ($topologyType == "spoke") {
			for ($j = 1; $j <= $this->data["dual_homed_devices"]; $j++) {
				$i = $j-1;
				// newly added
				$vrfBlock = [
					"device" => $hubag2[$i], 
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

			}
			for ($j = 1; $j <= $this->data["dual_homed_devices"]; $j++) {
				$i = $j-1;
				// newly added
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

				//$vrf[] = $vrfDefinitions; // newly added
			}
		}

		return $vrfDefinitions;
	}

	/*
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

		$this->data["demarc-ingress-port-no"] = $userDefined["demarc-ingress-port-no"]; // Todo:: In future this value will be from SYstem defined logic
		$ret =  [
			"device" => $userDefined["demarc-device"],
			"tagged" => $userDefined["tag"],
			"pe-type" => $userDefined["pe-type"],
			"bit-value" => $userDefined["bit-value"],
			"block-port-no" => $userDefined["block-port-no"],
			"ingress-port-no" => $userDefined["demarc-ingress-port-no"],
			
			"egress-bridge-port-no-1" => $userDefined["demarc-egress-bridge-port-no-1"],
			"erps-mode-1" => strtolower($userDefined["demarc-erps-mode-1"]),
			"erps-id-1" => $userDefined["demarc-erps-id-1"],			
		];
		if($this->data["dual_homed_devices"]==2){
			
			$ret["erps-id-2"] = isset($userDefined["demarc-erps-id-2"])?$userDefined["demarc-erps-id-2"]:"";
			$ret["erps-mode-2"] = isset($userDefined["demarc-erps-mode-2"])?strtolower($userDefined["demarc-erps-mode-2"]):"";
			$ret["egress-bridge-port-no-2"] = isset($userDefined["demarc-egress-bridge-port-no-2"])?$userDefined["demarc-egress-bridge-port-no-2"]:"";
		}
		if(isset($this->data["cust-vlan"]) && !empty($this->data["cust-vlan"])){
			foreach ($this->data["cust-vlan"] as $key => $value) {
				$ret["customer-vlan-".($key+1)] = $value;
			}
		}
		
		
		return $ret;
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
		 for($i=1;$i<=$this->data["dual_homed_devices"];$i++){
		 	if(!$this->isModuleBlocksExist('bgp','AG2_'.$i)){
				continue;
			}
		//foreach ($ag2Devices as $key => $device) {
		 	$key=$i-1;
			$role = $key == 0 ? "Primary" : "Secondary";
			$device = $ag2Devices[$i-1];

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
		for($i=1;$i<=$this->data["dual_homed_devices"];$i++){
			if(!$this->isModuleBlocksExist('ag1-l2-ext','AG1_'.$i)){
				continue;
			}
			$key = $i-1;
			$return[] = [
				"device-name" => $devices["ag1"][$key],
				"vrf-name" => $this->data["vrf-name"],
				"vpn-id" => $this->data["vpn-id"],
				"address-list" => [
					["nei-ip" => $devices["ag2"][$key]],
					["nei-ip" => $devices["css"][$key]],
				],
			];
		}

		// foreach ($devices["ag1"] as $key => $device) {
		// 	$return[] = [
		// 		"device-name" => $device,
		// 		"vrf-name" => $this->data["vrf-name"],
		// 		"vpn-id" => $this->data["vpn-id"],
		// 		"address-list" => [
		// 			["nei-ip" => $devices["ag2"][$key]],
		// 			["nei-ip" => $devices["css"][$key]],
		// 		],
		// 	];
		// }

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

		$deviceType = substr($hostname1, 8, 3);

		$neiAg2Ip = [];
		foreach ($topology["parents"] as $parentDevice) {
			if (strpos($parentDevice["hostname"], "PAR") !== false) {
				//AG1 Devices
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

		for ($i = 1; $i <= $this->data["dual_homed_devices"]; $i++) {

			if(!$this->isModuleBlocksExist('l2-vfi','CSS_'.$i)){
				continue;
			}
			$hostname = 'hostname' . $i;
			$ag2Device = $ag2Devices[$i - 1];

			$l2vfiBlock = [
				"vrf-name" => $this->data["vrf-name"],
				"device-name" => $$hostname,
				"vpn-id" => $this->data["vpn-id"],
				"vlan-cust" => $this->data[$ag2Device]["cust-vlan"],
				//"nei-ag2-ip" => $neiAg2Ip[$ag2Device],
				"nei-ag2-ip" => $ag1Devices[$i - 1],
			];

			if (in_array($deviceType, ["ESR", "CSS"])) {
				$l2vfiBlock["nei-ag2-ip"] = $ag1Devices[$i - 1];
			} else if ($deviceType == "ECR") {
				$l2vfiBlock["nei-ag2-ip"] = $neiAg2Ip[$ag2Device];
			}
			$l2vfiModule[] = $l2vfiBlock;
		}

		return $l2vfiModule;

	}

	public function getInterfaceModule($topology, $devices, $serviceInstance, $dualHomed, $isManaged, $variablesIdMap, $userDefined) {
		
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

		$ag2Devices = $this->data["devices"]["AG2"];
		// Logic to create AAR/AG2 device interfaces starts here
		for($i=1;$i<=$this->data["dual_homed_devices"];$i++){

				$deviceMode = ($i == 1)? "primary":"secondary";	
				if(!$this->isModuleBlocksExist('interface','AG2_'.$i)){
					continue;
				}
		//foreach ($topology['parents'] as $key => $val) {
		//	if (strstr($val['hostname'], "AAR")) { //AG2
				$hostname = $ag2Devices[$i-1];
				//$connectedDevice = $val['hostname'];
				//$hostname = $val['hostname'];
				// device mode variables of interface module
				if ($i == 1) {
					$customerVlan = isset($custVlanArray[0]) ? $custVlanArray[0] : "";
				} else {
					if(isset($this->data["userDefined"]["pe-type"]) && $this->data["userDefined"]["pe-type"]=='dual'){
						$customerVlan = isset($custVlanArray[0]) ? $custVlanArray[0] : "";

					}else{
						$customerVlan = isset($custVlanArray[1]) ? $custVlanArray[1] : "";
					}
				}

				$device = Device::getDeviceByHostname($hostname);

				$device_id = $device["id"];

				// logic as per declared in the resourcemanager file
				//$service_model_template_id, $device_id, $resource_manager_id, $customer_id
				//$customerVlan   =   (int)ResourceManager::getCustomerServiceVlan($template_id,$device_id,$variablesIdMap["customer-vlan"],$customer_id);
				//$customerVlan = $deviceMode =="secondary"?$customerVlan+1:$customerVlan;

				// interface description variable
				$interfaceDesc = ResourceManager::getInterfaceDescription($service_instance_id, 23, $isManaged);

				// vrf name for the interface : $service_model_template_id, $device_id, $resource_manager_id, $customer_id
				$vrfName = $this->data["vrf-name"];
				//ResourceManager::getVrfName($template_id, $variablesIdMap["vrf-name"], $this->data['customer_id']);
				$this->data["vrf-name"] = $vrfName;

				$interfaceID = "BVI" . $customerVlan;
				$interfaceArray['interface-id'] = $interfaceID;
				$interfaceArray['connected-device'] = $hostname;//$connectedDevice;
				$interfaceArray['device-mode'] = $deviceMode;
				$interfaceArray['ip-type'] = $ipType;
				$interfaceArray['dual-homed'] = $dualHomed;
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
				$this->data['vlan'] = $vlan;
				$this->data[$hostname]["cust-vlan"] = (int) $customerVlan;
				$this->data["cust-vlan"][] = (int) $customerVlan;

				//$i++;

			//} // if for AAR device ends here

		//} // parents for each loop ends here// logic to create AAR/AG2 device interfaces starts here
		// logic for ESR to ESR devices
		}		

		// CSS1-CSS2
		if($this->isModuleBlocksExist('interface','CSS_PRIMARY-CSS_SECONDARY')){
			$interface[] = $this->getCssToCssInterface($hostname1, $hostname2, 'primary', $topology, $ipType, $dualHomed, $vlan, $hostname1, $custVlanArray);
		}
		//CSS2-DEMARC
		if($this->isModuleBlocksExist('interface','CSS_PRIMARY-DEMARC')){
			$interface[] = $this->getCssToDemarcInterface($hostname1, $userDefined, 'primary', $dualHomed, $vlan);// Logic for CSS1 to Demarc
		}


		if($this->data["dual_homed_devices"]==2){
			//CSS2-CSS1
			if($this->isModuleBlocksExist('interface','CSS_SECONDARY-CSS_PRIMARY')){
				$interface[] = $this->getCssToCssInterface($hostname2, $hostname1, 'secondary', $topology, $ipType, $dualHomed, $vlan, $hostname2, $custVlanArray);
			}
			//CSS2-DEMARC
			if($this->isModuleBlocksExist('interface','CSS_SECONDARY-DEMARC')){
				$interface[] = $this->getCssToDemarcInterface($hostname2, $userDefined, 'secondary', $dualHomed, $vlan);
			}
		}
		
		return $interface;

	} // function to generate the interface ends here


	private function getCssToCssInterface($hostname1, $hostname2, $deviceMode, $topology, $ipType, $dualHomed, $vlan, $deviceType, $custVlanArray) {

		$interfaceECR1toECR2 = ResourceManager::getNeibhourHost($hostname1, $hostname2);

		// get the trunk number for ECR1 to ECR2
		$trunkNo = ResourceManager::trunk_no($hostname1, $interfaceECR1toECR2);

		//$serviceInst[]  =array();

		// old logic to find out thenew interface from port_1_service_instance
		//$inst_no = ResourceManager::service_inst($hostname1,$interfaceECR1toECR2);
		//$vlan_raps = ResourceManager::vlan_raps($hostname1);
		// calculate the ring number
		//$ring = ResourceManager::ring_number($hostname1); -- commented on 9th aug
		$ring = ResourceManager::ring_number($hostname1,$interfaceECR1toECR2);
		$intf_facing_ecr = substr($interfaceECR1toECR2, 0, 3) . string_after_number($interfaceECR1toECR2);
		// Service Instance

		$intfECRtoECRPrimary = array();
		if (empty($intf_facing_ecr)) {
			$intfECRtoECRPrimary['interface-id'] = "Gig0/0/12";
		} else {
			$intfECRtoECRPrimary['interface-id'] = $this->setDemarcInterface($intf_facing_ecr);
		}

		$intfECRtoECRPrimary['connected-device'] = $deviceType;
		$intfECRtoECRPrimary['ip-type'] = $ipType;
		$intfECRtoECRPrimary['dual-homed'] = $dualHomed;
		$intfECRtoECRPrimary['device-mode'] = $deviceMode;
		$intfECRtoECRPrimary['customer-vlan'] = $vlan;
		if (strstr(strtolower($ring), "major")) {
			// trunk no will be applicable for major ring only
			$intfECRtoECRPrimary['trunk-no'] = $trunkNo;
			$inst_no = ResourceManager::get_instance_number($hostname, $ring);
			$this->data['inst-no'][] = $serviceInst[0]['inst-no'] = $inst_no;
			$this->data['raps-vlan'][] = $serviceInst[0]['raps-vlan'] = (int) $vlan_raps; //$this->data["userDefined"]['demarc-raps-vlan-1'];
			$this->data['inst-no'][] = $serviceInst[1]['inst-no'] = $inst_no + 10;
			$this->data['raps-vlan'][] = $serviceInst[1]['raps-vlan'] = (int) $vlan_raps + 1; //$this->data["userDefined"]['demarc-raps-vlan-2'];
			/* if(!empty($custVlanArray)){
				 $k=2;
				 foreach($custVlanArray as $cust_vlan_id){
	                            $this->data['inst-no'][] = $serviceInst[$k]['inst-no'] =  $cust_vlan_id;
	                            $this->data['raps-vlan'][] = $serviceInst[$k]['raps-vlan'] = (int) $cust_vlan_id;//$this->data["userDefined"]['demarc-raps-vlan-2'];
					$k++;
*/

			$intfECRtoECRPrimary['service-instance-no'] = $serviceInst;

		} else {
			//$this->data['inst-no'][] = "001";
			//$this->data['raps-vlan'][] = $this->data["userDefined"]['demarc-raps-vlan-1'];//(int) $vlan_raps;//$this->data["userDefined"]['demarc-raps-vlan-1'];
			//$this->data['inst-no'][] = "002";
			//$this->data['raps-vlan'][] = $this->data["userDefined"]['demarc-raps-vlan-1']+10;//$this->data["userDefined"]['demarc-raps-vlan-2'];
			//  $this->data['inst-no'][] =  $v_lan;
			//  $this->data['raps-vlan'][] = (int) $v_lan;//$this->data["userDefined"]['demarc-raps-vlan-2'];
			if (!empty($custVlanArray)) {
				foreach ($custVlanArray as $cust_vlan_id) {
					$this->data['inst-no'][] = $serviceInst['inst-no'] = (int) $cust_vlan_id;
					$this->data['raps-vlan'][] = $serviceInst['raps-vlan'] = (int) $cust_vlan_id; //$this->data["userDefined"]['demarc-raps-vlan-2'];
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
	{    // ESR/css -> Demarc
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
	private function getCssToDemarcInterface($hostname, $data, $deviceMode, $dualHomed, $vlan) {

		$id = $deviceMode == "primary" ? 1 : 2;

		$interface['interface-id'] = $data["demarc-interface-id-" . $id];
		$interface['connected-device'] = $hostname; //$data["demarc-device"];
		$interface['device-mode'] = $deviceMode;
		$interface['ip-type'] = $data["ip-type"];
		$interface['dual-homed'] = $dualHomed;
		$interface['trunk-no'] = 2; //Todo::static //$data["demarc-trunk-no-" . $id];
		$interface['customer-vlan'] = $vlan;

		$instNo = isset($data["demarc-inst-no-" . $id])?$data["demarc-inst-no-" . $id]:"";
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
		*/
		// calculate the ring number
		if ($id == 2) {
			$this->data['ring_no'][1] = $ring = "SUB_PS2";
			$inst_no = "405";
		} else {
			$this->data['ring_no'][0] = $ring = ResourceManager::ring_number($hostname, $interface['interface-id']); // newly added 9th aug
			//$inst_no = ResourceManager::get_instance_number($hostname,$ring);	\$this->data['ring_no'][] = $ring = ResourceManager::ring_number($hostname,$interface['interface-id']); // newly added 9th aug
			$inst_no = ResourceManager::get_instance_number($hostname, $ring);
		}

		$this->data['demarc-inst-no'][] = $serviceInst[0]['inst-no'] = $instNo = (int) $inst_no; //$this->data["userDefined"]['demarc-raps-vlan-1'];//$inst_no;//$this->data["userDefined"]['demarc-raps-vlan-1'];//$inst_no;
		$this->data['demarc-raps-vlan'][] = $serviceInst[0]['raps-vlan'] = $this->data["userDefined"]['demarc-raps-vlan-1']; //$vlan_raps;//$this->data["userDefined"]['demarc-raps-vlan-1'];

		if($this->data["dual_homed_devices"]==2){
			$this->data['demarc-inst-no'][] = $serviceInst[1]['inst-no'] = $instNo + 10;
			$this->data['demarc-raps-vlan'][] = $serviceInst[1]['raps-vlan'] = (int) $this->data["userDefined"]['demarc-raps-vlan-2']; //$this->data["userDefined"]['demarc-raps-vlan-2'];
		}
		
		$deviceType = substr($hostname, 8, 3);
		$ag1Devices = $this->data["devices"]["AG1"];
		$ag2Devices = $this->data["devices"]["AG2"];


		// If single homed and pe-type is single, then add nei-ip/vpn-id and also for 2.55,2.56,2.58,2.59
		if(($this->data["userDefined"]["pe-type"] == "single" && !$this->data["dual_homed"]) || strpos($this->variables["sub-service-id"],"2.5")!==false){
			if (in_array($deviceType, ["ESR", "CSS"])) {
				$nei_ip = $this->getDeviceLoopback($ag1Devices[0]);
			} else if ($deviceType == "ECR") {
				$nei_ip = $this->getDeviceLoopback($ag2Devices[0]);
			}
			$serviceInst[0]['nei-ip'] = $nei_ip;
			$serviceInst[0]['vpn-id'] = $this->data["vpn-id"];
		}
		$interface['service-instance-no'] = $serviceInst;
		return $interface;
	}

	/* For AG2 devices */
	public function getPHWE($topology, $ipType, $custvlanpool, $serviceInstance, $variablesIdMap) {
		$phweArr = array();
		$phwe = array();
		$ag2Devices = $this->data["devices"]["AG2"];
		//$i = 1;
		$template_id = $serviceInstance["serviceModel"]["serviceModelTemplate"][0]["id"];
		for($i=1;$i<=$this->data["dual_homed_devices"];$i++){

			if(!$this->isModuleBlocksExist('phwe','AG2_'.$i)){
				continue;
			}
			$hostname = $ag2Devices[$i-1];
			$this->sdVariables[$hostname][$this->variables['sd_val']['hostname']] = $phweArr['ag2-device'] = $hostname;
				$phweArr['ip-type'] = $ipType;
				$bviConfig = array();
				$this->sdVariables[$hostname][$this->variables['sd_val']['bvi-no']] = $bviConfig['bvi-no'] = $this->data[$hostname]['cust-vlan'];

				$this->sdVariables[$hostname][$this->variables['sd_val']['vrf-name']] = $bviConfig['vrf-name'] = $this->data["vrf-name"];
				$phweArr['bvi-config'] = $bviConfig;
				$routingProtocol = array();
				$device = Device::getDeviceByHostname($hostname);
				$device_id = $device["id"];

				$ipv4Assignment = new Ipv4Assignment();
				$resultIpv4 = $ipv4Assignment->asssignIp($serviceInstance['id'], $template_id, $device_id, 1, [
					$variablesIdMap["ce-intf-ip"] => "ce-intf-ip",
				]);
				$this->sdVariables[$hostname][$this->variables['sd_val']['ce-intf-ip']] = $routingProtocol["ce-intf-ip"] = $resultIpv4['ce-intf-ip'];
				$this->sdVariables[$hostname][@$this->variables['sd_val']['cust-lan-pool']] = $routingProtocol['cust-lan-pool'] = $custvlanpool;
				$phweArr['routing-protocol'] = $routingProtocol;
				$phwe[] = $phweArr;

		}

		// foreach ($topology['parents'] as $key => $val) {
		// 	if (strstr($val['hostname'], "AAR")) {
		// 		//AG2
		// 		$this->sdVariables[$val['hostname']][$this->variables['sd_val']['hostname']] = $phweArr['ag2-device'] = $val['hostname'];
		// 		$phweArr['ip-type'] = $ipType;
		// 		$bviConfig = array();
		// 		$this->sdVariables[$val['hostname']][$this->variables['sd_val']['bvi-no']] = $bviConfig['bvi-no'] = $this->data[$val['hostname']]['cust-vlan'];

		// 		$this->sdVariables[$val['hostname']][$this->variables['sd_val']['vrf-name']] = $bviConfig['vrf-name'] = $this->data["vrf-name"];
		// 		$phweArr['bvi-config'] = $bviConfig;
		// 		$routingProtocol = array();
		// 		$device = Device::getDeviceByHostname($val['hostname']);
		// 		$device_id = $device["id"];

		// 		$ipv4Assignment = new Ipv4Assignment();
		// 		$resultIpv4 = $ipv4Assignment->asssignIp($serviceInstance['id'], $template_id, $device_id, 1, [
		// 			$variablesIdMap["ce-intf-ip"] => "ce-intf-ip",
		// 		]);
		// 		$this->sdVariables[$val['hostname']][$this->variables['sd_val']['ce-intf-ip']] = $routingProtocol["ce-intf-ip"] = $resultIpv4['ce-intf-ip'];
		// 		$this->sdVariables[$val['hostname']][@$this->variables['sd_val']['cust-lan-pool']] = $routingProtocol['cust-lan-pool'] = $custvlanpool;
		// 		$phweArr['routing-protocol'] = $routingProtocol;
		// 		$phwe[] = $phweArr;
		// 		//$i++;
		// 	}
		// }
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

			// Get  CSS/ESR devices from topology
			if (strpos($neighDevice["hostname"], "ESR") !== false || strpos($neighDevice["hostname"], "CSS") !== false) {
				$devices["esr"][] = $neighDevice["loopback0"];
			}

			// Get ECR devices from topology
			if (strpos($neighDevice["hostname"], "ECR") !== false) {
				$devices["ecr"][] = $neighDevice["loopback0"];
			}
		}

		$ag2Devices = $this->data["devices"]["AG2"];
		for($i=1;$i<=$this->data["dual_homed_devices"];$i++){

			if(!$this->isModuleBlocksExist('l2vpn','AG2_'.$i)){
				continue;
			}
			$hostname = $ag2Devices[$i-1];
		//foreach ($topology['parents'] as $key => $val) {
		//	if (strstr($val['hostname'], "AAR")) {
				// for AG2

				$device_id = Device::getDeviceByHostname($hostname);
				$l2vpnArr['name'] = "L2vpnAG2Device-" . $i;
				$l2vpnArr['device'] = $hostname;
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
						$neighbor['ip'] = $devices['ag1'][$i - 1];
						$neighbor['vpn-id'] = $this->data["vpn-id"];
					} else if (strstr($hostname2, 'ECR')) {
						$neighbor['ip'] = $devices['ecr'][0];
						$neighbor['vpn-id'] = $this->data["vpn-id"];
					}
				}

				$l2vpnArr['neighbor'] = $neighbor;
				$l2vpnArr['cust-vlan'] = "BVI" . $this->data[$hostname]["cust-vlan"];
				$l2vpn[] = $l2vpnArr;
				//$i++;
		// 	}
		// }
		}
		return $l2vpn;
	}

	// function to generate the Erps Config module for L3vpn
	public function getErpsConfig($topology, $hostname1, $hostname2, $userDefined, $service_instance_id) {
		// foreach ($topology["parents"] as $parentDevice) {
		// 	if (strpos($parentDevice["hostname"], "AAR") !== false) {
		// 		$devices["ag2"][] = $parentDevice["hostname"];
		// 	}
		// }
		$devices["ag2"] = $this->data["devices"]["AG2"];

		$inclusionVlan = $hostname1 . "," . $hostname2;
		$model = ServiceInstance::findOne($service_instance_id);
		$model->inclusion_hostname = $inclusionVlan;
		$model->save();
		$erpsConfig = array();
		$instNO = rand(2, 2000);

		if($this->isModuleBlocksExist('erps-config','CSS_1')){
			$erpsConfig[] = $this->getErpsConfigBlock($hostname1, $hostname2, 'primary', $instNO, $devices, $userDefined);
		}
		if($this->data["dual_homed_devices"]==2 && $this->isModuleBlocksExist('erps-config','CSS_2')){
			$erpsConfig[] = $this->getErpsConfigBlock($hostname2, $hostname1, 'secondary', $instNO + 2, $devices, $userDefined);
		}
		//}
		return $erpsConfig;

	}


	public function getErpsConfigBlock($hostname, $neighbors_hostname, $deviceMode, $instNo, $devices, $userDefined) {

		$erps = array();

		$erps['device'] = $hostname;
		if ($deviceMode == "secondary") {
			$ringNo = $this->data['ring_no'][1];
		} else {
			$ringNo = $this->data['ring_no'][0];
		}
		//$ringNo =  $this->data['ring_no'];//ResourceManager::ring_number_new($hostname); //ResourceManager:: ring_no($hostname);//ResourceManager:: ring_data($hostname);
		$erps['ring-no'] = $ringNo;
		$erps['device-mode'] = $deviceMode;
		$erps['erps-desc'] = ResourceManager::description_erps($hostname, $ringNo); //ResourceManager::ring_number($hostname); // as per new logic ResourceManager:: description_erps($ringNo);

		// If ring no sub ring then exclusion list will not added
		//if(strpos(strtolower($ringNo),"sub")===false){ // removed
		//$erps['exclusion-list'][]["vlan-id"] = $this->data[$devices['ag2'][0]]['cust-vlan']; //json_decode(ResourceManager::exclusion_vlan($hostname, $neighbors_hostname, ''), true);
		//$erps['exclusion-list'][]["vlan-id"] = $this->data[$devices['ag2'][1]]['cust-vlan'];
		//}
		if(isset($this->data["cust-vlan"]) && !empty($this->data["cust-vlan"])){
			foreach ($this->data["cust-vlan"] as $key => $value) {
				$erps['exclusion-list'][$key]["vlan-id"] = $value;
			}
		}
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
		for ($j = 1; $j <= $this->data["dual_homed_devices"]; $j++) {
			$i = $j-1;
		//for ($i = 0; $i <= 1; $i++) {
			$erpsInst['erps-inst-no'] = $i + 1;
			//$erpsInst['cust-vlan'] = $this->data[$devices['ag2'][$i]]['cust-vlan'];
			//$erpsInst['raps-vlan'] = $this->data['raps-vlan'][$i];
			if ($i == 0) {
				$erpsInst['inclusion-list'] = json_decode(ResourceManager::getVlans($hostname, $neighbors_hostname, $this->data[$devices['ag2'][$i]]['cust-vlan'], $ringNo, $this->data['raps-vlan'][$i]), true);
			} else {				
				$incl[]['vlan-id'] = $this->data[$devices['ag2'][$i]]['cust-vlan'];
				$incl[]['vlan-id'] = $this->data['demarc-raps-vlan'][$i];
				$erpsInst['inclusion-list'] = $incl;
			}
			$erpsInst['service-inst-other'] = (string) $this->data['inst-no'][$i]; //ResourceManager::service_inst($hostname,$interface);
			//$erpsInst['service-inst-demarc'] =  $this->data['demarc-inst-no'][$deviceMode][$i];
			$erpsInst['service-inst-demarc'] = (string) $this->data['demarc-inst-no'][$i]; //$this->data["userDefined"]["demarc-inst-no-" . $erpsInst['erps-inst-no']];
			$erpsInstance[] = $erpsInst;
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

		if(!isset($staticBlock[0]['rd'])){
			$deviceID = $this->getDeviceId($this->data["devices"]["AG2"][0]);	
			$l3vpnStatic['rd'] =  ResourceManager::getRD($service_model_template_id, $deviceID, $variablesIdMap['rd'], $customer_id);
		}else{
			$l3vpnStatic['rd'] = $staticBlock[0]['rd'];
		}
		return $l3vpnStatic;
	}


	// function to generate static block
	public function getStaticBlock($topology, $ipType, $customer_lan_pool_ipv4, $customer_lan_pool_ipv6, $phwe, $service_model_template_id, $variablesIdMap, $customer_id) {
		$staticBlock = array();
		$block = array();
		$i = 0;
		$rand = rand(2, 10000);
		$ag2Devices = $this->data["devices"]["AG2"];
		for($i=1;$i<=$this->data["dual_homed_devices"];$i++){
			if(!$this->isModuleBlocksExist('static','AG2_'.$i)){
				continue;
			}
		// foreach ($topology['parents'] as $key => $val) {

		// 	if (strstr($val['hostname'], "AAR")) { //AG2
				$hostname = $ag2Devices[$i-1];
				$deviceID = Device::getDeviceByHostname($hostname);
				$block['device'] = $hostname;
				$block['vrf-name'] = $this->data['vrf-name'];
				$block['address-family'] = $ipType;
				$block['customer-lan-pool-ipv4'] = $customer_lan_pool_ipv4;
				$block['customer-lan-pool-ipv6'] = $customer_lan_pool_ipv6;
				$block['ipv4-address'] = $phwe[$i-1]['routing-protocol']['ce-intf-ip'];
				//$block['ipv6-address'] = $phwe[$i-1]['routing-protocol']['ce-intf-ipv6'];
				$block['rd'] = ResourceManager::getRD($service_model_template_id, $deviceID, $variablesIdMap['rd'], $customer_id);
				if ($i == 1) {
					$block['bgp-id'] = $rand;
				} else {
					$block['bgp-id'] = $rand + 1;
				}
				//$i++;
				$staticBlock[] = $block;
		// 	}
		// }
		}		
		return $staticBlock;
	}

	/*For AG2 Device*/
	public function getPrefixRoutePolicy($topology, $ipType, $lan_pool_ipv4_mask, $lan_pool_ipv6_mask, $customer_name) {

		$routePolicy = array();
		$route = array();
		$ag2Devices = $this->data["devices"]["AG2"];

		for($i=1;$i<=$this->data["dual_homed_devices"];$i++){
		// foreach ($topology['parents'] as $key => $val) {
			if(!$this->isModuleBlocksExist('erps-config','AG2_'.$i)){
				continue;
			}
		// 	if (strstr($val['hostname'], "AAR")) {
				$hostname = $ag2Devices[$i-1];
				$role = empty($route) ? "Primary" : "Secondary";
				$routePolicy['deviceName'] = $hostname;
				$routePolicy['ip-type'] = $ipType;
				if (in_array($ipType, ["both", "ipv4"])) {
					$routePolicy['prefix-set-ipv4'] = ResourceManager::getFttpLp($customer_name, "ipv4");
					$routePolicy['lan-pool-ipv4-mask'] = $lan_pool_ipv4_mask;
					$routePolicy['lp-route-policy-v4'] = ResourceManager::getFttpLp($customer_name, 'ipv4');
				}
				if (in_array($ipType, ["both", "ipv6"])) {
					$routePolicy['prefix-set-ipv6'] = ResourceManager::getFttpLp($customer_name, "ipv6");
					$routePolicy['lan-pool-ipv6-mask'] = $lan_pool_ipv6_mask;
					$routePolicy['lp-route-policy-v6'] = ResourceManager::getFttpLp($customer_name, 'ipv6');
				}

				$routePolicy["role"] = $role;

				$route[] = $routePolicy;
		// 	}
		// }
		}		
		return $route;
	}

	

	public function getRoutePolicyPassAll($topology) {
		$passAllArr = array();
		$passAll = array();
		$ag2Devices = $this->data["devices"]["AG2"];
		for($i=1;$i<=$this->data["dual_homed_devices"];$i++){

			if(!$this->isModuleBlocksExist('route-policy-pass-all','AG2_'.$i)){
				continue;
			}
		// foreach ($topology['parents'] as $key => $val) {
		// 	if (strstr($val['hostname'], "AAR")) {
				$passAllArr['route-policy-name'] = "rjil-pass-all";
				$passAllArr['device-name'] = $ag2Devices[$i-1];//$val['hostname'];
				$passAll[] = $passAllArr;
		// 	}
		// }
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

	private function setIpTypeForLab($userDefined) {

		$variables = array_keys($userDefined);
		if (in_array("ipv4-wan-address", $variables) && in_array("ipv6-wan-address", $variables)) {
			$userDefined["ip-type"] = "both";
		} else if (in_array("ipv4-wan-address", $variables)) {
			$userDefined["ip-type"] = "ipv4";
		} else if (in_array("ipv6-wan-address", $variables)) {
			$userDefined["ip-type"] = "ipv6";
		}
		return $userDefined;
	}

	public function findCustomerService($customer_id, $service_id) {
		$serviceInstance = ServiceInstance::find()->alias("si")
			->joinWith(["serviceModel" => function ($q) {return $q->alias("sm");}])
			->where([
				"sm.service_id" => $service_id,
				"si.customer_id" => $customer_id,
			])
			->andWhere(["IS NOT", "si.rt_export_import", null])
			->asArray()->one();

		return $serviceInstance;

	}

	private function findTopology($hostname1){
			// Todo::remove static json for topology as topoly is not there in local
		$topology = '{"id":"1003","hostname":"MUMBRLB1ECR001","devicetype":"ECR","sapid":"","neid":"INMUMUMBRLB1NB0020AG3ECR001","facid":"","loopback0":"10.64.229.251","neighbours":[{"id":"1014","hostname":"MUMBRLB1ECR003","ip":"10.64.231.6","localport":"TenGigabitEthernet0\/0\/27","sapid":"","neid":"INKAMUMBRJJDNB0031MA1ECR003","facid":"","loopback0":"10.64.231.15","devicetype":"ECR"},{"id":"1012","hostname":"MUMBRLB1PAR001","ip":"10.64.229.253","localport":"TenGigabitEthernet0\/0\/26","sapid":"","neid":"","facid":"","loopback0":"10.64.2.68","devicetype":"ASR903"}],"parents":[{"id":"1012","hostname":"MUMBRLB1PAR001","sapid":"","facid":"","neid":"INMUMUMBRLB1NB0020AG3PAR001","loopback0":"10.64.2.68","devicetype":"ASR903"},{"id":"1013","hostname":"MUMBRLB2PAR001","sapid":"","facid":"","neid":"INMUMUMBRLB2NB0020AG3PAR001","loopback0":"10.64.2.69","devicetype":"ASR903"},{"id":"1015","hostname":"MUMBRLB1AAR001","sapid":"","facid":"","neid":"INMUMUMBRLB1NB0020AG3AAR001","loopback0":"10.64.2.66","devicetype":"ASR9K"},{"id":"1019","hostname":"MUMBRLB2AAR001","sapid":"","facid":"","neid":"INMUMUMBRLB2NB0020AG3AAR001","loopback0":"10.64.2.67","devicetype":"ASR9K"}]}';
		//$topology = ResourceManager::getTopology($hostname1);
		if (!$topology) {
			$this->errors[] = "Topology Not found";
			return false;
		}

		$topology = json_decode($topology,true);
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
	private function isModuleBlocksExist($module,$block){

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

	private function getDeviceId($hostname){
		return isset($this->data["devices_id"][$hostname])?$this->data["devices_id"][$hostname]:false;
	}

	private function getUD($variable){	
		return isset($this->data["userDefined"][$variable])?$this->data["userDefined"][$variable]:false;
	}

}