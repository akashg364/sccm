<?php

namespace app\models;

use yii;

/**
 * @SWG\Definition(
 *   definition="Device",
 *   type="object",
 *   @SWG\Property(property="customer_name", type="string"),
 *   @SWG\Property(property="customer_id", type="integer"),
 * )
 *
 * @SWG\Definition(
 *   definition="DeviceByLocation",
 *   type="object",
 *   required={"status_code","sub_status_code","message","data"},
 *   @SWG\Property(property="status_code", type="integer",example="200"),
 *   @SWG\Property(property="sub_status_code", type="integer",example="2000"),
 *   @SWG\Property(property="message", type="string",example="Device Data retrieval successfully"),
 *   @SWG\Property(property="data",type="array",@SWG\Items(@SWG\Property(property="devices", type="array",@SWG\Items(@SWG\Property(property="id",type="integer",example="1"),@SWG\Property(property="device_id",type="integer",example="1"),@SWG\Property(property="hostname",type="string"),@SWG\Property(property="ipv4",type="string"),@SWG\Property(property="ipv6",type="string"),@SWG\Property(property="neid",type="string"),@SWG\Property(property="sapid",type="string"),@SWG\Property(property="make",type="string"),@SWG\Property(property="model",type="string"),@SWG\Property(property="ports", type="array",@SWG\Items())))))
 * )
 *
 * @SWG\Definition(
 *   definition="DeviceByNeid",
 *   type="object",
 *   required={"status_code","sub_status_code","message","data"},
 *   @SWG\Property(property="status_code", type="integer",example="200"),
 *   @SWG\Property(property="sub_status_code", type="integer",example="2000"),
 *   @SWG\Property(property="message", type="string",example="Device Data retrieval successfully"),
 *   @SWG\Property(property="data",type="array",@SWG\Items(@SWG\Property(property="devices", type="array",@SWG\Items(@SWG\Property(property="id",type="integer",example="1"),@SWG\Property(property="hostname",type="string"),@SWG\Property(property="ipv4",type="string"),@SWG\Property(property="ipv6",type="string"),@SWG\Property(property="neid",type="string"),@SWG\Property(property="sapid",type="string"),@SWG\Property(property="make",type="string"),@SWG\Property(property="model",type="string")))))
 * )
 */
class Device extends \common\models\Device
{

	public static function getDeviceByNeid($neid){
		
		if(strpos($neid,",")){
			$neid = explode(",",$neid);
		}

		$data = self::find()
				 ->select("id,hostname,loopback as ipv4,loopback as ipv6,neid,sapid as sap")
				 ->where(["neid"=>$neid])
				 ->asArray()
				 ->all();

		if($data){
			foreach ($data as $key => $value) {
				$data[$key]["make"] ="Cisco";
				$data[$key]["model"] = "Cisco ASR 920o";
			}
			
		}		 
		return 	$data;	 
	}
        


	public static function findDevices($req){

		$ret = ['wrong_input'];
		if(isset($req["latitude"]) && !empty($req["latitude"]) && isset($req["longitude"]) && !empty($req["longitude"])){
			 $ret = Yii::$app->inventoryApi->getNearByDevicesUsingLatLong($req);
		}
		else if(isset($req["pincode"]) && !empty($req["pincode"]) && isset($req["device_type"]) && !empty($req["device_type"])){
			$ret = Yii::$app->inventoryApi->getNearByDevicesUsingPincode($req);
			$ports = array("3","4","5","6");
			if(isset($req['device_type']) && $req['device_type'] == "demark"){
				foreach($ret['devices'] as $k=>$v){
					$ret['devices'][$k]['ports'] = $ports;
				}
			}
		}
		return $ret;
	}
        
        public static function getDeviceById($id){
		
		if(!is_array($id) && strpos($id,",")){
			$id = explode(",",$id);
		}

		$data = self::find()
				 ->select("id,hostname,loopback as ipv4,loopback as ipv6,neid,sapid as sap")
				 ->where(["id"=>$id])
				 ->asArray()
				 ->all();

		if($data){
			foreach ($data as $key => $value) {
				$data[$key]["make"] ="Cisco";
				$data[$key]["model"] = "Cisco ASR 920o";
			}
			
		}		 
		return 	$data;	 
	}
}