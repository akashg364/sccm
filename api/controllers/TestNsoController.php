<?php

namespace api\controllers;

use Yii;
use yii\web\Controller;
use backend\models\ServiceInstance;
use backend\models\ServiceModel;
use backend\models\ServiceInstanceDevices;
use backend\models\ServiceModelTemplate;
use backend\models\DeviceRole;
use backend\models\Services;
use backend\models\ServiceDeploy;

use common\components\Nso;


class TestNsoController extends \yii\web\Controller {

	public function beforeAction($action) {
		$this->enableCsrfValidation = false;
		return parent::beforeAction($action);
	}
	
	public function actionIndex() {
		return "test";
	}
	
	public function actionCallNso() {
	   $payload = '';
	   $service = '';
	   $isDryRun = true;
	   $nso = new Nso();
       $res = $nso->pushToService($devicesPayload, $model->service);
	}
	
	public function actionGetServices() {
			  $nso = new Nso();
	          print $res = $nso->getServices();
			 exit;
	} // actionGetServices()
	
	/*
	Sample data :
	{ 	"service_order_id":"9871001123456743454534Te0/0/1"
		"app_data": {} 
	}
	**/
	public function actionDeleteFullService() {
		
		//$str_request = Yii::$app->request->post('api_data');
		
		$str_request = Yii::$app->request->getBodyParams();
		
		print_r($str_request);
		exit;
		
		if (trim($str_request) == '') {
			$response = [
							"status_code" => "300", 
							"sub_status_code" => "3000", 
							"message" => "data missing.",
							"data" => array(),
							"app_data" => array()
						];
			return  json_encode($response);
		}
		
		$obj = json_decode($str_request);
		$service_order_id = $obj->service_order_id;
	
		$Service = Services::getServiceNameByServiceOrder($service_order_id);
		$nso = new Nso();
		
		// $ret = $nso->deleteService($Service);
		$device = 'device3';
		$ret = $nso->deleteDevice($device);
		
		print_r($ret);
		exit;
		
		$response = [
							"status_code" => "200", 
							"sub_status_code" => "2000", 
							"message" => "Deleted successfully",
							"data" => array("service_order_id" => $service_order_id),
							"app_data" => array()
						];
		return  json_encode($response);
	} // EO actionDeleteFullService()
	
	public function actionTestApi() {
		$transaction_id = 1;
		$action = '';
		$scheduled_date = '';
		
		$test = ServiceDeploy::ServiceInvokeStep3($transaction_id, $action, $scheduled_date);
		print $test;
	} // actionTestApi()
	
	
	
}  // EO Class TestNsoController
