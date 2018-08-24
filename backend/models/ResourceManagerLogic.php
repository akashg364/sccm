<?php

namespace backend\models;

use Yii;
use yii\helpers\Json;

Trait ResourceManagerLogic {

    public static $ring_config;
    public static $variables;
    public static $interface;
    public static $neighbors_hostname;
    public static $device_hostname;
    
	//GIg0/0/7
	public static function getInterfaceFullname($demarcInterface){
		$demarcInterface = strtolower($demarcInterface);		
		$interfaceArray = [
			"Gig" => "gigabitethernet",
			"TenGig" => "tengigabitethernet",
			"Hun" =>  "HundredGigEthernet",
			"Fou" =>"FourtyGigEthernet",
		];
		
		$ret = "";
		foreach ($interfaceArray as $key => $value) {
			
				if (strpos($demarcInterface, strtolower($key)) !== false) {			
					$ret = $value . string_after_number($demarcInterface);
					return $ret;					
				}		
			
		}
	}

    public static function getTopologyParams($hostname){
        $get_topology = Yii::$app->inventoryApi->getTopology([
            "hostname" => $hostname,
        ]);
        if(isset($get_topology['neighbours'][0]['hostname'])) {
          return self::$neighbors_hostname = $get_topology['neighbours'][0]['hostname'];
        } else {
            return false;
        }
       
    }

    public static function getTopology($hostname){
			
			$get_topology = Yii::$app->inventoryApi->getTopology([
            "hostname" => $hostname,
        ]);
            //pe($get_topology);
        if(isset($get_topology['neighbours']) && isset($get_topology['parents'])) {
          return self::$neighbors_hostname = $get_topology;
        } else {
          return false;
        }
       
    }
    
    public static function getNeibhourHost($hostname, $neighbors_hostname) {
		
        $intf_facing_ecr = Yii::$app->inventoryApi->getLocalInterface([
            "hostname" => $hostname,
            "device_hostname" => $neighbors_hostname,
        ]);
		
        if(isset($intf_facing_ecr)){
            // $start = substr($intf_facing_ecr, 0, 3);
            // return $start . string_after_number($intf_facing_ecr);
            return $intf_facing_ecr; 
        } else {
            return false;
        }
        
    }

    public static function ringConfig($hostname) {
        if (!empty(self::$ring_config)) {
            return self::$ring_config;
        }
        $ret = Yii::$app->inventoryApi->getCommandData([
            "hostname" => $hostname,
            "commandcode" => "l2ringdata",
        ]);
        if (isset($ret[$hostname])) {
            self::$ring_config = $ret[$hostname];
            return $ret[$hostname];
        }
        return false;
    }

    //Add format in variable mapping
	public static function router_id($hostname){
        $get_topology = Yii::$app->inventoryApi->getTopology([
            "hostname" => $hostname,
        ]);
        if(isset($get_topology['loopback0'])) {
          return $get_topology['loopback0'];
        } else {
            return false;
        }
       
    }
	
    public static function description_erps_old($ring_no) {
        return "Ring {$ring_no} Sub ring";
    }

    public static function description_erps($hostname,$ring_no) {
        $ring_data = "";
        $ret = Yii::$app->inventoryApi->getCommandData([
            "hostname" => $hostname,
            "commandcode" => "l2ringdata",
        ]);       
        if (isset($ret[$hostname][$ring_no]) && !empty($ret[$hostname][$ring_no] && is_array($ret[$hostname][$ring_no]) && count($ret[$hostname]) > 0 )) {  
                return $ret[$hostname][$ring_no]['description'];
        }
        return "Ring {$ring_no} Sub ring";
    }

    public static function ring_no($hostname) {

        $ret = Yii::$app->inventoryApi->getCommandData([
            "hostname" => $hostname,
            "commandcode" => "l2ringcount",
        ]);
        if (isset($ret[$hostname]) && !empty($ret[$hostname]) && isset($ret[$hostname]["ring_count"])) {

            $ring_count = $ret[$hostname]["ring_count"];
            if ($ring_count == 0) {
                self::$variables["ring_no"] = "Major_Ring";
                return "Major_Ring";
            } else if ($ring_count > 0) {
                self::$variables["ring_no"] = "Sub_Ring" . $ring_count;
                return "Sub_Ring" . $ring_count;
            }
        }
        return false;
    }

    public static function ring_data_old($hostname) {
		$ring_data = "";
        $ret = Yii::$app->inventoryApi->getCommandData([
            "hostname" => $hostname,
            "commandcode" => "l2ringdata",
        ]);       
		//pe($ret);
        if (isset($ret[$hostname]) && !empty($ret[$hostname] && is_array($ret[$hostname]) && count($ret[$hostname]) > 0 )) {            
            foreach($ret[$hostname] as $k=>$val){
					if(strstr(strtolower($k),"major")){
							$ring_data = "Sub_Ring";
							return $ring_data;
							break;
					}else{
						$ring_data = "Major_Ring";
					}
        }
		return $ring_data;        
    }
	return false;
	}
	
	 public static function ring_number($hostname,$interfaceDemarc) {
	
		$ring_data = "";
        $ret = Yii::$app->inventoryApi->getCommandData([
            "hostname" => $hostname,
            "commandcode" => "l2ringdata",
        ]); 
		
		$incl_vlan = "883";
		$interface = self::getInterfaceFullname($interfaceDemarc);		
        if (isset($ret[$hostname]) && !empty($ret[$hostname] && is_array($ret[$hostname]) && count($ret[$hostname]) > 0 )) {            
            foreach($ret[$hostname] as $k=>$val){
				$vlans = explode(",",$val['inclusion-list_vlan_ids']);		
				if(in_array($incl_vlan,$vlans) && isset($val['port0_interface']) && strstr(strtolower($val['port0_interface']),$interface)){				
						$ring = $val['ethernet_ring'];
						return $ring;
				}
        }
		return false;        
    }
	return false;
	}
	
		 public static function ring_number_new($hostname) {
	
		$ring_data = "";
        $ret = Yii::$app->inventoryApi->getCommandData([
            "hostname" => $hostname,
            "commandcode" => "l2ringdata",
        ]); 
		
		$incl_vlan = "883";
		//$interface = self::getInterfaceFullname($interfaceDemarc);		
        if (isset($ret[$hostname]) && !empty($ret[$hostname] && is_array($ret[$hostname]) && count($ret[$hostname]) > 0 )) {            
            foreach($ret[$hostname] as $k=>$val){
				$vlans = explode(",",$val['inclusion-list_vlan_ids']);		
				if(in_array($incl_vlan,$vlans)){				
						$ring = $val['ethernet_ring'];
						return $ring;
				}
        }
		return false;        
    }
	return false;
	}
	
	public static function ring_data_instance($hostname) {
		$ring_data = "";
        $ret = Yii::$app->inventoryApi->getCommandData([
            "hostname" => $hostname,
            "commandcode" => "l2ringdata",
        ]);   
pe($ret);		
        if (isset($ret[$hostname]) && !empty($ret[$hostname] && is_array($ret[$hostname]) && count($ret[$hostname]) > 0 )) {            
            foreach($ret[$hostname] as $k=>$val){
					if(strstr(strtolower($k),"major")){
							$ring_data = "Sub_Ring";
							return $ring_data;
							break;
					}else{
						$ring_data = "Major_Ring";
					}
        }
		return $ring_data;        
    }
	return false;
	}
	
		public static function get_instance_number($hostname,$ring) {		
		$ring_data = "";
        $ret = Yii::$app->inventoryApi->getCommandData([
            "hostname" => $hostname,
            "commandcode" => "l2ringdata",
        ]);      
		
        if (isset($ret[$hostname]) && !empty($ret[$hostname] && is_array($ret[$hostname]) && count($ret[$hostname]) > 0 )) {   

			$ring_data = $ret[$hostname][$ring];                      
			$service_instance = $ring_data['port0_service_instance'];     // always take the port0_service_instance        		
			preg_match_all('!\d+!', $service_instance, $matches);
			$inst_no = $matches[0][0];		
			return $inst_no;
		}
	return false;
	}
	
	
	
	
	
	
    public static function enc_vlan($hostname, $neighbors_hostname, $vlan_cust) {
        return self::exclusion_vlan($hostname, $neighbors_hostname, $vlan_cust);
    }

    public static function exclusion_vlan($hostname, $neighbors_hostname, $vlan_cust) {
        $exclusion_vlan = Yii::$app->inventoryApi->getExclusionVlan([
            "hostname" => $hostname,
            "neighbors_hostname" => $neighbors_hostname,
        ]);
		
        if (!$exclusion_vlan) {
            return false;
        }
        if (is_array($exclusion_vlan)) {
            array_push($exclusion_vlan, $vlan_cust);
			
            $exclusion_list = [];
            foreach ($exclusion_vlan as $vlan_id) {
              if (trim($vlan_id) != '') {
				$exclusion_list[] = ["vlan-id" => $vlan_id];
			   }
            }
			
            return Json::encode($exclusion_list);
        }
        return false;
    }

    public static function intf_facing_ecr($hostname, $neighbors_hostname, $vlan_cust) {
        $intf_facing_ecr = Yii::$app->inventoryApi->getLocalInterface([
            "hostname" => $hostname,
            "device_hostname" => $neighbors_hostname,
        ]);
        if (!$intf_facing_ecr) {
            return false;
        }
        if (isset($intf_facing_ecr)) {
            $start = substr($intf_facing_ecr, 0, 3);
            return $start . string_after_number($intf_facing_ecr);
        }
        return false;
    }

    public static function erps_inst_no($hostname) {
        $ringConfig = self::ringConfig($hostname);
        if (isset($ringConfig['Major_Ring']["instance"])) {
            $instance = $ringConfig['Major_Ring']["instance"];
            return $instance;
        }
        return false;
    }

    public static function erps_inst_no_1($hostname) {
        return self::erps_inst_no($hostname);
    }

    public static function erps_inst_no_2($hostname) {
        return self::erps_inst_no($hostname);
    }

    public static function rpl_owner($hostname) {
        $rpl_owner = false;
        if (isset(self::$variables["ring_no"])) {
            $ring_no = self::$variables["ring_no"];
        } else {
            $ring_no = self::ring_no($hostname);
        }
        if (!$ring_no) {
            return false;
        }

        if ($ring_no == "Major_Ring") {
            $rpl_owner = "port1";
        } else if (strpos($ring_no, "Sub_Ring") !== false) {
            $rpl_owner = "port0";
        }
        return $rpl_owner;
    }

    public static function vlan_raps($hostname) {
        $vlan_raps = false;
        $inclusionVlan = Yii::$app->inventoryApi->getInclusionVlan($hostname);
        //pe($inclusionVlan);
        if (isset($inclusionVlan)) {
			$ringCount = Yii::$app->inventoryApi->getCommandData(["hostname" => $hostname,
																	"commandcode" => "l2ringcount"]);
            if ($ringCount == 0 || $ringCount == '') {
                // Major Ring
                $vlan_raps = 401;
            } else {
                $vlan_raps = 401;
                $vlan_ids = explode(",", $inclusionVlan);
                foreach ($vlan_ids as $key => $vlan_id) {
                    if (strpos($vlan_id, "-")) {
                        unset($vlan_ids[$key]);
                        $vlan_id_array = explode("-", $vlan_id);
                        for ($i = $vlan_id_array[0]; $i <= $vlan_id_array[1]; $i++) {
                            $vlan_ids[] = $i;
                        }
                    }
                }
                $vlan_raps = self::checkInInclusionVlanIds($vlan_raps, $vlan_ids);
            }
        }
        return $vlan_raps;
    }

    private static function checkInInclusionVlanIds($vlan_raps, $vlan_ids) {
        $vlan_raps += 1;
        if (!in_array($vlan_raps, $vlan_ids)) {
            return $vlan_raps;
        } else {
            return self::checkInInclusionVlanIds($vlan_raps, $vlan_ids);
        }
    }

    public static function service_inst($hostname, $interface) {
        $service_instance = Yii::$app->inventoryApi->getServiceInstance([
            "hostname" => $hostname,
            "interface" => $interface,
            "commandcode" => "serviceinst",
        ]);
       
	
	
	   if ($service_instance && is_array($service_instance)) {
           $service_inst_arr = explode(",", $service_instance[0]);
		  
		   foreach ($service_inst_arr as $key => $value) {
				if (strpos($value, 'trunk') !== false) {
					unset($service_inst_arr[$key]);
				}
			}
			return max($service_inst_arr) + 1;
        }
        return false;
    }

    public static function trunk_no($hostname, $interface) {
		
        $service_instance = Yii::$app->inventoryApi->getServiceInstance([
            "hostname" => $hostname,
            "interface" => $interface,
            "commandcode" => "serviceinst",
        ]);
		//pe($service_instance);
        if ($service_instance && is_array($service_instance)) {
             $service_inst_arr = explode(",", $service_instance[0]);
			foreach ($service_inst_arr as $key => $value) {
                if (strpos($value, "trunk") !== false) {
				    return trim(str_replace("trunk","",$value));
                }
            }
			//return "trunk " . rand(1, 10);
            return rand(1, 10);
        }
        return false;
    }

    public static function service_inst_demarc($ring_no) {
        //ring_no = Major_Ring - 11
        //ring_no = Sub_Ring3 - 31
        return $ring_no . "1";
    }

    /*
      Arun
      Date : 08thApl
     * */

    public static function encapsulaton_vlans($hostname, $interface, $commandcode = 'encapsulation') {

        $encapsulaton_vlans = Yii::$app->inventoryApi->getEncapsulationVlan([
            "hostname" => $hostname,
            "interface" => $interface,
            "commandcode" => $commandcode
        ]);

        if (!$encapsulaton_vlans[0]) {
            return false;
        }

        $encapsulaton_vlans = explode(',', $encapsulaton_vlans[0]);

        if (is_array($encapsulaton_vlans)) {
            $exclusion_list = [];
            foreach ($encapsulaton_vlans as $id) {
                $exclusion_list[] = ["id" => $id];
            }
            return json_encode($exclusion_list);
        }
        return false;
    }    

    public static function get_major_ring_number($hostname) {
        $ring_data = "";
        $ret = Yii::$app->inventoryApi->getCommandData([
            "hostname" => $hostname,
            "commandcode" => "l2ringdata",
        ]); 

        //$incl_vlan = "883";
        if (isset($ret[$hostname]) && !empty($ret[$hostname] && is_array($ret[$hostname]) && count($ret[$hostname]) > 0 )) {            
            foreach($ret[$hostname] as $k=>$val){                
                if(strstr(strtolower($val['ethernet_ring']),"major")){
                        return $val['ethernet_ring'];   
                }        
            }
        }
        return false;        
    }
    
    public static function getVlans($hostname, $neighbors_hostname, $vlan_cust,$ring,$raps_vlan) {
        $vlans = [];
        $ret = Yii::$app->inventoryApi->getCommandData([
            "hostname" => $hostname,
            "commandcode" => "l2ringdata",
        ]); 
        if (!$ret) {
            return false;
        }
         if (isset($ret[$hostname]) && !empty($ret[$hostname] && is_array($ret[$hostname]) && count($ret[$hostname]) > 0 )) {               
            $ring_data = $ret[$hostname][$ring];                  
            if(!empty($ring_data['inclusion-list_vlan_ids'])){
                $inclusionVlan = explode(",",$ring_data['inclusion-list_vlan_ids']);     
               
                foreach($inclusionVlan as $id){
                    $vlans[] = ["vlan-id" => $id];
                }
                $vlans[] = ["vlan-id" => $vlan_cust];
                $vlans[] = ["vlan-id" => $raps_vlan];                
                $vlans = array_map("unserialize", array_unique(array_map("serialize", $vlans)));
                return json_encode($vlans);
            }else{
                $vlans[] = ["vlan-id" => $vlan_cust];
                $vlans[] = ["vlan-id" => $raps_vlan];
            }  
            $vlans = array_map("unserialize", array_unique(array_map("serialize", $vlans)));
            return json_encode($vlans);
        }
        return false;
    }


}
