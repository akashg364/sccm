<?php

namespace common\components;

Class Nso {
	
    const USERNAME = "admin";
    const PASSWORD = "cisco123"; // admin
	
	const RFS_URL = "http://10.64.101.17:8080";    
	const CFS_URL = "http://10.64.101.18:8080";
	const CFS_DEVICE = "cisco-nso";
	
	public $errors = [];
	
   // private $_baseUrl = "http://10.137.40.84:4062/api/running"; //Jitendra NSO Container
   // private $_baseUrlApi = "http://10.137.40.84:4062/api"; //Jitendra NSO Container
    
	// private $_baseUrl = "http://10.137.40.84:4242/api/running"; //Vaibhav NSO Container
    // private $_baseUrlApi = "http://10.137.40.84:4242/api"; //Vaibhav NSO Container
	
    //private $_baseUrl = "http://10.105.217.15:8080/api/running"; //Bangalore NSO Container
    //private $_baseUrlApi = "http://10.105.217.15:8080/api"; //Bangalore NSO Containe
	
	// private $_baseUrl = "http://10.137.40.120:4504/api/running"; // New NSO Container
 //    private $_baseUrlApi = "http://10.137.40.120:4504/api"; // New NSO Containe

    //private $_baseUrl = "http://10.64.101.17:8080/api/running"; // New NSO Container
    //private $_baseUrlApi = "http://10.64.101.17:8080/api"; // New NSO Container

    // Reliance Lab API URL 
    private $_baseUrl = "http://10.64.101.18:8080/api/running"; 
    private $_baseUrlApi = "http://10.64.101.18:8080/api"; 
	
    public function getUrl($route) {
        return $this->_baseUrl . "/" . $route;
    }
    public function getUrlApi($route) {
        return $this->_baseUrlApi . "/" . $route;
    }

    public function getCnaapUrl($route) {
        return $this->_cnaapUrl . "/" . $route;
    }
    
    private function getAuth(){
        return 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
    }
	
	private function getHeaders($contentType='xml'){
		$headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
		if(in_array($contentType,["json","xml"])){
			$headers[] = 'Content-Type: application/vnd.yang.data+'.$contentType;
		}else{
			$headers[] = "";
		}
        
		return $headers;
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

        return $result;
    }

    public function updateTemplate($variable, $value) {

        $url = $this->getUrl("services/cnaap/css/" . $variable);

        $headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';

        $data = "<$variable>$value</$variable>";

        $res = $this->curl($url, 'PUT', $data, $headers);
        return $res;
    }

    public function getAuthGroupList() {

        $headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';
        //$headers[] = 'Accept: application/vnd.yang.data+json';
//        $data = "<input><outformat>cli</outformat></input>";
        $url = $this->getUrlApi("operational/devices/authgroups?deep");
       
        return $this->curl($url, 'GET', "", $headers);
    }
    
    public function getDevice($device) {

        $headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';
        //$headers[] = 'Accept: application/vnd.yang.data+json';
//        $data = "<input><outformat>cli</outformat></input>";
        $url = $this->getUrl("devices/device/$device?deep");
        return $this->curl($url, 'GET', "", $headers);
    }

    public function getDevices() {
        $url = $this->getUrl("devices");
        $headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';
        return $this->curl($url, 'GET', '', $headers);
    }

    public function getDeviceCompare($device) {
        $url = $this->getUrl("devices/device/" . $device . "/_operations/compare-config");
        $headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';
        $data = "<input><outformat>cli</outformat></input>";
        return $this->curl($url, 'POST', $data, $headers);
    }
    
     public function getChecksync($device) {
        $url = $this->getUrl("devices/device/" . $device . "/_operations/check-sync");
        $headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';
        $data = "<input><outformat>cli</outformat></input>"; 
        return $this->curl($url, 'POST', "", $headers);
    }
    
    public function syncto($device,$cfsDevice="") {
		$cfsDevice = !empty($cfsDevice)?$cfsDevice:self::CFS_DEVICE;
		$headers = $this->getHeaders(); 
		
		// RFS Device sync-to	-----------------------------------------------------------
		$url  = self::RFS_URL."/api/running/devices/device/{$device}/_operations/sync-to";		
		$rfsRes = $this->curl($url, 'POST', "", $headers);
		$rfsArray = xmlToArray($rfsRes);
		
		if(isset($rfsArray["result"]) && $rfsArray["result"] == 'true'){			
			// CFS Device sync-to -----------------------------------------------------------
			$url  = self::CFS_URL."/api/running/devices/device/{$cfsDevice}/_operations/sync-to";		
			$cfsRes = $this->curl($url, 'POST', "", $headers);
			 $cfsArray = xmlToArray($cfsRes);
			 
			  if(isset($cfsArray["result"]) && $cfsArray["result"] == 'true'){
				return true;
			  }else if(isset($cfsArray["result"]) && isset($cfsArray["info"])){
				$this->errors = $cfsArray["info"];
				return false;
			 }
		 }else if(isset($rfsArray["result"]) && isset($rfsArray["info"])){
			$this->errors = $rfsArray["info"];
			return false;
		 }
		 return false;
		 
		// Below id old one code
        // $url = $this->getUrl("devices/device/" . $device . "/_operations/sync-to");
        // $headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
        // $headers[] = 'Content-Type: application/vnd.yang.data+xml';
        // $data = "<input><outformat>cli</outformat></input>"; 
        // return $this->curl($url, 'POST', "", $headers);
    }
    

	/*Device Sync From : First it will sync-from from RFS server then it will sync-from CSF server	*/
    public function syncfrom($device,$cfsDevice="") {
	
		$cfsDevice = !empty($cfsDevice)?$cfsDevice:self::CFS_DEVICE;
		$headers = $this->getHeaders();    
		
		// RFS Device sync-from	-----------------------------------------------------------
		$url  = self::RFS_URL."/api/running/devices/device/{$device}/_operations/sync-from";
        $this->saveLog("NSO", 'RFS - syncfrom'. $url);		
		$rfsRes = $this->curl($url, 'POST', "", $headers);
		$rfsArray = xmlToArray($rfsRes);
		
		 if(isset($rfsArray["result"]) && $rfsArray["result"] == 'true'){			
			// CFS Device sync-from -----------------------------------------------------------
			$url  = self::CFS_URL."/api/running/devices/device/{$cfsDevice}/_operations/sync-from";
            $this->saveLog("NSO", 'CFS - syncfrom'. $url);  		
			$cfsRes = $this->curl($url, 'POST', "", $headers);
			 $cfsArray = xmlToArray($cfsRes);
			 
			  if(isset($cfsArray["result"]) && $cfsArray["result"] == 'true'){
				return true;
			  }else if(isset($cfsArray["result"]) && isset($cfsArray["info"])){
				$this->errors = $cfsArray["info"];
				return false;
			 }
		 }else if(isset($rfsArray["result"]) && isset($rfsArray["info"])){
			$this->errors = $rfsArray["info"];
			return false;
		 }		 
        //$data = "<input><outformat>cli</outformat></input>"; 
        //return $this->curl($url, 'POST', "", $headers);
		return false;
    }
    

    public function addDevice($data, $action) {
        
		//$headers[] = 'Authorization: Basic ' . base64_encode("$admin:$password");
		$headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
	    $headers[] = 'Content-Type: application/vnd.yang.data+xml';
        if (isset($data['device_type']) && $data['device_type'] == 'xr') {
            $device_type = 'cisco-ios-xr';
        } elseif (isset($data['device_type']) && $data['device_type'] == 'xe') {
            $device_type = 'ios';
        }
	
        $payload = "<device>";
        if (isset($data['host_name'])) {
            $payload .= "<name>" . $data['host_name'] . "</name>";
        }
        if (isset($data['ip_address'])) {
            $payload .= "<address>" . $data['ip_address'] . "</address>";
        }
        if (isset($data['authgroup'])) {
            $payload .= "<authgroup>" . $data['authgroup'] . "</authgroup>";
        }
        
        if ($action == 'POST') {
            $url = $this->getUrl("devices");
        $payload .= "<port>22</port>
        <state>
		    <admin-state>unlocked</admin-state>
		</state>
	   <device-type>
        <cli>
        <ned-id xmlns:" . $device_type . "-id = 'http://tail-f.com/ned/" . $device_type . "-id'>" . $device_type . "-id:" . $device_type . "</ned-id>
        <protocol>ssh</protocol>
        </cli>
        </device-type>
        <description>" . $data['customer_name'] . "</description>";
        }else{
            $url = $this->getUrl("devices/device");
        }
        $payload .= "</device>";
//        echo $url;die();
        
        return $this->curl($url, $action, $payload, $headers);
    }
    
    public function deleteDevice($data) {
        
        $url = $this->getUrl("devices/device");
        $headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';
        if (isset($data['host_name'])) {
            $url.= "/".$data['host_name'];
        }
        return $this->curl($url, "DELETE", "", $headers);
    }

    public function getServices() {
       $url = $this->getUrl("services?deep");
        $headers[] =  $this->getAuth();
        // $headers[] = 'Content-Type: application/vnd.yang.data+xml';
        $headers[] = 'Accept: application/vnd.yang.data+json';
        return $this->curl($url, 'GET', '', $headers);
    }

    public function pushNsoService($payload, $service, $isDryRun = false) {

        $headers[] = $this->getAuth;
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';

        if (in_array($service, ['l3vpnservice', 'l3vpnservice2'])) {
            $package = "l3vpn";
        } else if ($service == 'l2vpnservice') {
            $package = "l2vpn";
        }
        $data = '<' . $package . ' xmlns="http://com/example/' . $package . '" xmlns:y="http://tail-f.com/ns/rest"  xmlns:' . $package . '="http://com/example/' . $package . '"  xmlns:ncs="http://tail-f.com/ns/ncs">
        ' . $payload . '
        </' . $package . '>';


        $url = $this->getUrl("services/$package/$service");
//                        echo $url; die();
        if ($isDryRun) {
            $url = $url . "?dryrun";
        }
        $res = $this->curl($url, 'PUT', $data, $headers);
        // $res = $this->curl($url,'PATCH',$data,$headers);

        return $res;
    }
    
    

    // http://10.137.40.84:4242/api/running/services/l2vpn:l2vpn/l2vpnservice4/_operations/un-deploy
    public function undeployService($service, $instance) {

        $url = $this->getUrl("services/$service:$service/$instance/_operations/un-deploy");
        $headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';
        $res = $this->curl($url, 'POST', '', $headers);

        return $res;
    }

    //http://10.137.40.84:4242/api/running/services/l2vpn:l2vpn/l2vpnservice4/_operations/re-deploy
    public function redeployService($service, $instance) {
        $url = $this->getUrl("services/$service:$service/$instance/_operations/re-deploy");
        echo $url;
        $headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';
        return $this->curl($url, 'POST', '', $headers);
    }

    public function getPackages($api = false) {

        $res = $this->getServices();

        $packages = xmlToArray($res);
        $ret = [];
        foreach ($packages as $package_name => $package) {

            if (in_array($package_name, ["global-settings", "logging"])) {
                continue;
            }
            if (!isset($package["name"]) && !isset($package[0]["name"])) {
                continue;
            }
            if (in_array($package_name, ["l3vpn", "l2vpn", 'l3vpncmm', 'l3vpnccm', 'sip', 'service'])) {
                if ($api == false) {
                    $ret[$package_name] = $package_name;
                } else {
                    $ret[] = $package_name;
                }
            }
        }
        return $ret;
    }

    public function getServicesInstances() {
        $ret = [];
        $res = $this->getServices();

        $packages = xmlToArray($res);

        if (!$res) {
            return false;
        }
        if (!$packages) {
            return false;
        }

        foreach ($packages as $package_name => $package) {

            if (in_array($package_name, ["global-settings", "logging"])) {
                continue;
            }

            if (!isset($package["name"]) && !isset($package[0]["name"])) {
                continue;
            }

            $serviceArray = [];
            if (isset($package[0]["name"]) && !empty($package[0]["name"])) {
                $serviceArray = $package;
            } else {
                $serviceArray[] = $package;
            }

            if (isset($serviceArray)) {

                foreach ($serviceArray as $service) {
                    if (strpos($service["name"], $package_name) !== false)
                        $ret[$package_name][] = $service["name"];
                }
            }
        }

        return $ret;
    }

    public function pushToService($payload, $service_name, $isDryRun = false,$inclusionHostname) {

        $headers[] = $this->getAuth();
        $headers[] = 'Content-Type: application/vnd.yang.data+json';
        //$url = $this->getUrl("services/".$service_name);
        $url = $this->_baseUrl;
        $commitURL = $url;
        $url = $url . "?dry-run";
        /*if ($isDryRun) {
            //$url = $url . "?dryrun"; //  one
            $url = $url . "?dry-run";
        }*/
        //else{
            // call the delete function and then sync from
           // $this->saveLog("NSO", 'Device NAME  :: '. $inclusionHostname);
           // $inclusionHostnames = explode(",", $inclusionHostname);
                
           // $this->deleteInclusionList($inclusionHostnames);
       // } 
        $this->saveLog("NSO", 'PUSH TO SERVICE :: '. $url);
        $this->saveLog("NSO", 'PAYLOAD :: '. $payload);
        $res = $this->curl($url, 'PATCH', $payload, $headers);
        $this->saveLog("NSO", 'DRY RUN OUTPUT :: '. $res);
        if (!$isDryRun) {            
            $res = $this->curl($commitURL, 'PATCH', $payload, $headers);
            $this->saveLog("NSO", 'COMMIT OUTPUT :: '. $res);
        }        
        return $res;
    }

    public function getaccesstoken() {

        $url = "http://10.137.32.113/CNAAP-API/api/web/index.php/v1/api/get-access-token";
//        $headers[] = 'Authorization: Basic ' . base64_encode("admin:admin");
//        $headers[] = 'Content-Type: application/vnd.yang.data+xml';

        $data = '{
 "access_token": "zJtzBC5mw2ZoFe7uc0QS22p60zClGso6ZHsq8mCs",
"data": [{
"client_id": "testing",
"client_secret": "F327467SRDgdsfsdlfo326difjiWWF"
}]
}';

        $res = $this->curl($url, 'POST', $data);
        $rest = json_decode($res);
        if (isset($rest->IsSuccess) && $rest->IsSuccess == 1 && !empty($rest->Data)) {
            return $rest->Data->access_token;
        } else {
            return false;
        }
    }
    public function getDeviceByLatLong($variable, $latitude, $longitude, $accesstoken = 'vPwlztQamcrfDFQqktMHBXqsYnERh8mAMJL03r0d') {

        $accesstoken = $this->getaccesstoken();
        if($accesstoken== false){
            return false;
        }
        $url = $this->getCnaapUrl($variable);
//        $headers[] = 'Authorization: Basic ' . base64_encode("admin:admin");
//        $headers[] = 'Content-Type: application/vnd.yang.data+xml';

        $data = ' {
            "access_token":"' . $accesstoken . '",
            "data": {
                "latlong":"' . $latitude . '"
            }
        }';

        $res = $this->curl($url, 'POST', $data);
        $rest = json_decode($res);
        if ($rest->code == 200 && $rest->status == 'success') {
            return $rest->data;
        } else {
            return false;
        }
    }

    public function getFreePort($variable, $hostname, $accesstoken = 'vPwlztQamcrfDFQqktMHBXqsYnERh8mAMJL03r0d') {

        $url = $this->getCnaapUrl($variable);
//        $headers[] = 'Authorization: Basic ' . base64_encode("admin:admin");
//        $headers[] = 'Content-Type: application/vnd.yang.data+xml';

        $data = ' {
            "access_token":"' . $accesstoken . '",
            "data": {
                "host_name":"' . $hostname . '"
            }
        }';

        $res = $this->curl($url, 'POST', $data);

        $rest = json_decode($res);
        if ($rest->code == 200 && $rest->status == 'success') {
            return $rest->data;
        } else {
            return false;
        }
    }

    
    private function saveAuthGroup($method,$data) {

        $headers[] = $this->getAuth();
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';     
        $url = $this->getUrlApi("running/devices/authgroups");
        
        return $this->curl($url, $method, $data, $headers);
    }   
    
    public function addAuthGroup($group_name,$username,$password) {
        
        $data = "<group>
                    <name>$group_name</name>
                    <default-map>
                        <remote-name>$username</remote-name>
                        <remote-password>$password</remote-password>
                    </default-map>
                </group>";

        return $this->saveAuthGroup('POST',$data);
    }   
    
    public function updateAuthGroup($group_name,$username,$password) {  
        $data = "<authgroups>
                    <group>
                        <name>$group_name</name>
                        <default-map>
                            <remote-name>$username</remote-name>
                            <remote-password>$password</remote-password>
                        </default-map>
                    </group>
            </authgroups>";         
        return $this->saveAuthGroup("PATCH",$data);
    }
    
    public function deleteAuthGroup($auth_group){

        $headers[] = $this->getAuth();
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';     
        $url = $this->getUrlApi("running/devices/authgroups/group/$auth_group");
        
        return $this->curl($url, "DELETE", "", $headers);
    }
    
    #for device group
    /*
    Author : Jitendra
    Date : 15th Feb, 2018
    comment : Need to implement on ccm side
    ***/
    public function addDeviceGroup($payload) {
        $url = $this->getUrl("devices");
        $headers[] = 'Authorization: Basic ' . base64_encode("admin:admin");
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';
        
        $data = '<device-group xmlns="http://tail-f.com/ns/ncs" xmlns:y="http://tail-f.com/ns/rest"  xmlns:ncs="http://tail-f.com/ns/ncs">';
            $data .= $payload;  
        $data .= '</device-group>';
        
        return $this->curl($url, 'POST', $data, $headers);
    } // EO addDeviceGroup()

    
    /*
    Author : Jitendra
    Date : 16th Feb, 2018
    ****/
    public function deleteDeviceGroup($data) {
        $url = $this->getUrl("devices/device-group");
        $headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';
        if (isset($data['device_group_name'])) {
            $url.= "/".$data['device_group_name'];
        }
        return $this->curl($url, "DELETE", "", $headers);
    } // EO addDeviceGroup()
  
    /*
    Author : Jitendra
    Date : 16thFeb, 2018
    ****/
    public function getDeviceGroupList() {

        $headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
        $headers[] = 'Content-Type: application/vnd.yang.data+xml';
        //$url = $this->getUrlApi("operational/devices/authgroups?deep");
        $url = $this->getUrl("devices/device-group?deep");
        return $this->curl($url, 'GET', "", $headers);
    }
  
    /*
	Author : Jitendra
	Date : 27th April, 2018
	***/
	 public function deleteService($service,$service_order_id) {

        $url = $this->getUrl("$service/$service_order_id");
		$this->saveLog("NSO", 'Delete Service  :: '. $url);
        $headers = $this->getHeaders();
        $res = $this->curl($url, 'DELETE', '', $headers);	
        $this->saveLog("NSO", 'Delete Service Result  :: '. $res); 
        return $res;
    }
    
    public function deleteInclusionList($device) {
    
    if(is_array($device)){
        $result = array();
        foreach ($device as $key => $value) {
        
            $url = self::RFS_URL."/api/running/devices/device/".$value."/config/ios:ethernet/ring/g8032/g8032-list/SUB_PS/instance/1/inclusion-list?no-networking";
            $headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
            $headers[] = 'Content-Type: application/vnd.yang.data+json';
            $data = "<input><outformat>cli</outformat></input>"; 
            $this->saveLog("NSO", 'DELETE INCLUSION VLAN'. $url);
            $result[] =  $this->curl($url, 'DELETE', "", $headers);
            
            $this->syncfrom($value);
        }
    }else{

            $url = self::RFS_URL."/api/running/devices/device/".$device."/config/ios:ethernet/ring/g8032/g8032-list/SUB_PS/instance/1/inclusion-list?no-networking";
            $this->saveLog("NSO", 'DELETE INCLUSION VLAN'. $url);
            $headers[] = 'Authorization: Basic ' . base64_encode(SELF::USERNAME.":".SELF::PASSWORD);
            $headers[] = 'Content-Type: application/vnd.yang.data+json';
            $data = "<input><outformat>cli</outformat></input>"; 
            $this->saveLog("NSO", 'DELETE INCLUSION VLAN'. $url);
            $result[] =  $this->curl($url, 'DELETE', "", $headers);
            $this->syncfrom($device);
        }
        return $result;

    }

    public function saveLog($fname,  $dataToStore) {
        $fileName = "/var/www/html/nsologs/" ;
        if (!is_dir($fileName)) {
            mkdir($fileName, 0777, TRUE);
        }
    
        $fileName .= $fname .".txt";
        
        $msg = date ( 'Y-m-d H:i:s' ) . ' ->' . PHP_EOL;
        $msg .= $dataToStore . "\n\n\n\n"; 
         $msg .= PHP_EOL .PHP_EOL;
        //echo PHP_EOL.PHP_EOL.$fileName.PHP_EOL.PHP_EOL;
        file_put_contents ( $fileName, $msg, FILE_APPEND ); // Store log
    }    
    
}

