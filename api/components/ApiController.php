<?php
namespace api\components;

use Yii;
use yii\web\Response;

class ApiController extends \yii\rest\Controller
{	
 

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
		return $behaviors;
	}
   
    /**
     * Api response
     */
    public function apiResponse($httpstatus, $status, $substatus, $data = array(), $message = '', $error = '')
    {
        Yii::$app->response->statusCode = $httpstatus;

         if(!empty($message)){
            $key = "message";
            $value = $message ? $message : 'Success';
         }else{
            $key = "error";
            $value = $error ? $error : 'Error';
         }
        return [
                'status_code' => $status,
                'sub_status_code' => $substatus,
                $key => $value,
                'data' => $data
         ];
       
    }  
}
