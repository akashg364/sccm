<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;
use api\models\Sccmcrd;
use yii\helpers\Json;
 use yii\web\Response;
class SubserviceController extends \api\components\	ApiController
{	

   
   /*
	 *$params : service_id
	 * is_managed:
	 * terminated_at:
	 * routing_protocol: 
	 */
	private function actionGetSubServiceId($service_id,$is_managed,$terminated_at,$routing_protocol){
		
		$data = [];	
	
		try {
			
			$req = Yii::$app->request;
			$service_id 	= $req->get("service_id");
			$is_managed 	= $req->get("is_managed");
			$terminated_at 	= $req->get("terminated_at");
			$routing_protocol = $req->get("routing_protocol");
			$topology_type 	= $req->get("topology_type");
			
			$data = Sccmcrd::getSubService($service_id,$is_managed,$terminated_at,$routing_protocol,$topology_type) ;
			
			if($data){
				return $this->apiResponse(200, 200, 2000, $data, $message = "Success");
			}else{
				return $this->apiResponse(200, 300, 3000, $data, $message = "", $error="Sub service not found");
			}
			
			
		}catch (\Exception $ex) {
			$error = $ex->getMessage();
			return $this->apiResponse(200, 300, 3000, $data, $message = "", $error);
		}
	
	}
}
