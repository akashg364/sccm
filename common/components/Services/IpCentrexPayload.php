<?php
namespace common\components\services;

use backend\models\ResourceManager;
/*
bgp - Done
erps-config - Done
demarc - Done	
vrf-definition - Done

esar-config - 
	ip allocation
	sar1-sar2 interface - not getting from inventory
	import-export
	
interface -
	ip allocation remain in BDI

//UD variables : static
"demarc-inst-no-1"=> 1,//
	"demarc-inst-no-2"=> 2,//
	"demarc-raps-vlan-1"=>1,//
	"demarc-raps-vlan-2"=>2,//
*/
Class IpCentrexPayload extends \common\components\services\Payload{
	
	private $sarDevices = [
		"MUMBRLB1CSR005",
		"MUMBRLB1CSR006",
		//"BGLRRLABCSR001",
		//"BGLRRLABCSR002",
	];
	
	public function getBgpBlock(){		
		
		$bgp = [];
		$ipType = $this->getUD("ip-type");
		$provideAsNumber = ResourceManager::getVariablevalue($this->data["variablesIdMap"]["provider-as-number"], $this->data["customer_id"]);
		foreach($this->data["devices"] as $deviceType=>$devices){
			
			if(in_array($deviceType,["ESR","ECR","AG1"])){
			
				$blockDeviceType = in_array($deviceType,["ESR","ECR"])?"CSS":$deviceType;				
				foreach($devices as $key=>$device){
					
					if(!$this->isModuleBlocksExist('bgp',$blockDeviceType."_".($key+1))){						
						continue;
					}
					$bgp[] = [
						"vrf-name" => $this->data["vrf-name"],
						"provider-as-number" => $provideAsNumber,
						"ip-type" => $ipType,
						"device" => $device,
					];
				}
	    	}
    	}
		return $bgp;
	}
	
	public function getInterfaceBlock(){
		
		$homedDevices = isset($this->data["devices"]["ECR"])?$this->data["devices"]["ECR"]:$this->data["devices"]["CSS"];
		
		$hostname1 = $homedDevices[0];
		$hostname2 = $homedDevices[1];
		$ipType = $this->getUD("ip-type");
		$dualHomed = $this->data["dual_homed"];
		$userDefined = $this->data["userDefined"];
		$topology = $this->data["topology"];
		$ag2Devices = $this->data["devices"]["AG2"];
		$custVlanArray = explode(",",$userDefined["cust-vlan"]);
		$vlan  = [];
		foreach($custVlanArray as $custVlan){
			$vlan[] = ["vlan-id"=>$custVlan];
		}
		
		$interfaces[] = $this->getCssToDemarcInterface($hostname1, $userDefined, 'primary', $dualHomed, $vlan,$topology,$ag2Devices);
		$interfaces[] = $this->getCssToDemarcInterface($hostname2, $userDefined, 'secondary', $dualHomed, $vlan,$topology,$ag2Devices);
		
		$interfaces[] = $this->getCssToCssInterface($hostname1, $hostname2, 'primary', $topology, $ipType, $dualHomed, $vlan, $hostname1,$custVlanArray);
		$interfaces[] = $this->getCssToCssInterface($hostname2, $hostname1, 'secondary', $topology, $ipType, $dualHomed, $vlan, $hostname2,$custVlanArray);
		
		
		$interfaces = array_map(function($intf) {		
			foreach($intf as $key=>$value){
			
				if($key=="connected-device"){
					$key = "device-name";
				}else if($key=="service-instance-no")continue;
				$ret[$key] = $value;
			}			
			return $ret;
		}, $interfaces);
		
		$interfacesBDI = $this->getInterfacesBDI($homedDevices,$custVlanArray);		
		$interfaces = array_merge($interfaces,$interfacesBDI);
		
		return $interfaces;
	}
	
	protected function getInterfacesBDI($homedDevices,$custVlanArray){
	
		$interfaces =[];
		$ipType = $this->getUD("ip-type");
		$interfaceDescription = ResourceManager::getInterfaceDescription($this->data["service_instance_id"],23,$this->data["is_managed"]);
		foreach($custVlanArray as $custVlan){
			foreach($homedDevices as $key=>$device){
				
				 $deviceMode = $key==0?"primary":"secondary";				 
				 $interfaces[] = [ 
					"interface-id"=> "BDI".$custVlan, // take first cust-vlan 234 logical interface
					"device-name"=> $device,
					"device-mode"=> $deviceMode,
					"ip-type"=> $ipType,
					"customer-vlan"=> [
					  [
						"vlan-id"=> $custVlan
					  ]
					],
					"desc"	=> $interfaceDescription,
					"ipv4-wan-address"=> "10.64.56.1 255.255.255.248",//UD/SD
					"ipv6-wan-address"=> "2405:0200:1410:1401::1/123", //UD/SD
					"vrf-name"=> $this->data["vrf-name"],
					"dhcp-helper-address-1"=> $this->getUD("dhcp-helper-address-1"), // UD
					"dhcp-helper-address-2"=> $this->getUD("dhcp-helper-address-2"), // UD
					"relay-destination-ipv6"=> $this->getUD("relay-destination-ipv6"),//UD
					"group-no"=> $custVlan,
					"vip-ipv4"=> "10.64.56.3", // SD assign pool
					"ipv6-address1"=> "FE80:0200:1410:1401::3",//UD/SD assign same value in mapping
					"ipv6-address2"=> "2405:0200:1410:1401::3/123"//UD/SD  
					];
			}
		}
		
		return $interfaces;
	}
	
	public function getEsarConfigBlock(){
		
		$esarConfig = [];
		
		$userDefined = $this->data["userDefined"];
		$custVlanArray = explode(",",$userDefined["cust-vlan"]);
		$ipType = $this->getUD("ip-type");

		$dfbGroupNoId = $this->data["variablesIdMap"]["dfp-group-no"];	
		$device_id = $this->getDeviceId($this->sarDevices[0]);
		$dfbGroupNo = ResourceManager::getVariableValueFromPool($this->data["template_id"], $device_id, $dfbGroupNoId, $this->data["customer_id"]);
		
		
		foreach($this->sarDevices as $key=>$sar_device){ 
		
				$deviceMode = $key==0 ? "primary" : "secondary";	
				$custVlan = isset($custVlanArray[$key])? $custVlanArray[$key]: "";
				$otherSar = $key==0? $this->sarDevices[1]: $this->sarDevices[0];
//				$esarPhyIntf = ResourceManager::getNeibhourHost($sar_device, $otherSar);
//				$esarPhyIntf = substr($esarPhyIntf, 0, 3) . string_after_number($esarPhyIntf);
			
				$esarConfig[] = [
					"device"=> $sar_device,// SAR Device
					"device-mode"=> $deviceMode,
					"ip-type"=> $ipType,
					"vrf-name"=>$this->data["vrf-name"],
					"rt-import"=> [
						[
						"rt"=> "65000:319" // ECR/CSS take from VRF-definition
						]
					],
					"rt-export"=> [
						[
						"rt"=> "65000:319"
						]
					],
					"customer-vlan4"=> $custVlan, //Cust Vlan
					"customer-vlan6"=> $custVlan, //Cust Vlan
					"description"=> "Towards CPE",//Static value as now
						"esar-phy-intf"=> "xe-2/0/2",//SAR Primary -SAR Secondary
					"esar-interface-des"=> "esar-des1213",
					"esbc-phy-intf"=> $this->getUD("esbc-phy-intf"),// UD : SAR Primary to Server 
					"esbc-interface-des"=> "Towards ESAR",
					"esbc-routing-des"=> "esbc-des1213",//Static
						"ipv4-wan-address"=> "10.64.88.17 255.255.255.252",
						"ipv6-wan-address"=> "2405:0200:1410:1401:0000:0000:0004:0110/127",// same as logical interface for ipv4/ipv6 
						"vipv4-address"=> "25.25.23.20", 
						"vipv6-address"=> "2405:0200:1410:1401:0000:0000:0004:0111",
					"dfp-group-no"=> $dfbGroupNo,// SD : take from pool : range 1-100
				];
			}
			
			
			return $esarConfig;
	}

}