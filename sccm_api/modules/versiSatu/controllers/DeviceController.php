<?php

namespace app\modules\versiSatu\controllers;

use app\models\Device;
use yii\helpers\Json;
use yii;

class DeviceController extends \app\components\Controller {

	public function actionGetDeviceByNeid() {
		$data = [];
		try {		
			
			//return Yii::$app->inventoryApi->getDeviceDetails(["neid"=>Yii::$app->request->get("neid")]);
			$deviceData	=	Yii::$app->inventoryApi->getDeviceDetails(["neid"=>Yii::$app->request->get("neid")]);			
			if($deviceData){
			foreach ($deviceData['devices'][0] as $k=>$val){
				if(isset($val['ports'])){				
					unset($deviceData['devices'][0][$k]['ports']);	
				}	
			}
			}else{
				return $this->apiResponse(200, 300, 3000, $data, $message = "",$error="Device not found");	

			}	
			$data['devices']	=	$deviceData['devices'][0];			
			if($data){
				return $this->apiResponse(200, 200, 2000, $data, $message = "Data retrival successfully");
			}else{
				return $this->apiResponse(200, 300, 3000, $data, $message = "",$error="Device not found");	
			}      
		} catch (\Exception $ex) {

			$error = $ex->getMessage();
			return $this->apiResponse(200, 300, 3000, $data, $message = "", $error);
		}

		return $data;
	}

	public function actionFindDevices() {

		$data = [];
		try {
			 $data = Device::findDevices(Yii::$app->request->get());
			
			if($data){
				return $this->apiResponse(200, 200, 2000, $data, $message = "Data retrival successfully");
			}else{
				return $this->apiResponse(200, 300, 3000, $data, $message = "",$error="Device not found");	
			}

		} catch (\Exception $ex) {

			$error = $ex->getMessage();
			return $this->apiResponse(200, 300, 3000, $data, $message = "", $error);
		}

		return $data;
	}
}