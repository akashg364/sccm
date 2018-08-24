<?php

namespace app\modules\versiSatu\controllers;
use Yii;
use app\components\Controller;
use app\models\Webhook;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

class WebhookController extends Controller {


	
	public function actionCreate(){
		$data=[];
		try {
                        $appData    =   array();    
                        $dataRequest = Yii::$app->request->getBodyParams(); 
			$model = new Webhook();
                        $model->client_id = $dataRequest['client_id'];
                        $model->endpoint = $dataRequest['endpoint'];
                        if (!filter_var(trim($dataRequest['endpoint']), FILTER_VALIDATE_URL)) { 
                            return $this->apiResponse(200, 11000, 11000, $data, $message = "", "The endpoint URL is not valid, Kindly provide valid URL");
                          }
                        if(isset($dataRequest['app_data'])){
                            $appData    =   $dataRequest['app_data'];
                        }
			if ($model->save()) {                             
                        return $this->apiResponse(200, 2012, 2012, $data, "Webhook Endpoint added successfully",$error="", $appData);
                        }else{
                            throw new NotFoundHttpException('Client ID already present, kindly provide valid values of Client ID');
                        }
		}catch (\Exception $ex) {
			$error = $ex->getMessage();
			return $this->apiResponse(200, 11000, 11000, $data, $message = "", $error);
		}
		return $data;
	}

      
}