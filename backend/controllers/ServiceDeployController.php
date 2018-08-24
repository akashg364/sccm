<?php

namespace backend\controllers;

use Yii;
use backend\models\ServiceInstance;
use common\components\Nso;
use yii\web\Response;

Class ServiceDeployController extends \yii\web\Controller{
		
		
		public function actionDryRun(){
			
			if(Yii::$app->request->isAjax){
				Yii::$app->response->format = Response::FORMAT_JSON;
			}
			$response = [
				"success"=> true,
				"data"=>[],
				"errors"=>[]
			];

			//Todo::remove check on LAB 
			return $response = [
				"success"=> true,
				"data"=> $this->renderAjax("dry-run",["data"=>"Dry RUN Output from NSO"]),
				"errors"=>[]
			];
			$id = Yii::$app->request->get("id");
			
			if(empty($id)){
				$response["success"] = false;
				$response["data"] 		 = "Invalid Request. Service Instance Id cannot be blank";				
				return $response;
			}
			
			$serviceInstance = ServiceInstance::find()->where(["id"=>$id])->limit(1)->one();			
			if(!$serviceInstance){
				$response["success"] = false;
				$response["data"] 		 = "Invalid Request. Service Instance not found";				
				return $response;
			}
			
			
			$nso = new Nso();
			$res = $nso->pushToService($serviceInstance->finalNsoPayload, $serviceInstance->name, $isDryRun= true);

			$response["data"]  =  $this->renderAjax("dry-run",["data"=>$res]);
			
			return $response;		
		}

}
