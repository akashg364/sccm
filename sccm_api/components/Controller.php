<?php

namespace app\components;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use common\components\SccmCommonFunction;

/**
 * Controller yang digunakan di app extend dari \yii\rest\Controller
 *
 * @author Muhamad Alfan <muhamad.alfan01@gmail.com>
 * @since 1.0
 */
class Controller extends \yii\rest\Controller
{
    use TraitController;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
        ];
        return $behaviors;
    }

    /**
     * Api Validate error response
     */
    public function apiValidate($errors, $message = false)
    {
        Yii::$app->response->statusCode = 422;
        return [
            'statusCode' => 422,
            'name' => 'ValidateErrorException',
            'message' => $message ? $message : 'Error validation',
            'errors' => $errors
        ];
    }

    /**
     * Api Created response
     */
    public function apiCreated($data, $message = false, $status, $substatus_code)
    {
        Yii::$app->response->statusCode = 201;
        return [
            'status_code' => $status,
            'sub_status_code' => $substatus_code,
            'message' => $message ? $message : 'Created successfully',
            'data' => $data
        ];
    }

    /**
     * Api Updated response
     */
    public function apiUpdated($data, $message = false)
    {
        Yii::$app->response->statusCode = 202;
        return [
            'statusCode' => 202,
            'message' => $message ? $message : 'Updated successfully',
            'data' => $data
        ];
    }

    /**
     * Api Deleted response
     */
    public function apiDeleted($data, $message = false)
    {
        Yii::$app->response->statusCode = 202;
        return [
            'statusCode' => 202,
            'message' => $message ? $message : 'Deleted successfully',
            'data' => $data
        ];
    }

    /**
     * Api Item response
     */
    public function apiItem($data, $message = false)
    {
        Yii::$app->response->statusCode = 200;
        return [
            'statusCode' => 200,
            'message' => $message ? $message : 'Data retrieval successfully',
            'data' => $data
        ];
    }

    /**
     * Api Collection response
     */
    public function apiCollection($data, $total = 0, $message = false)
    {
        Yii::$app->response->statusCode = 200;
        return [
            'statusCode' => 200,
            'message' => $message ? $message : 'Data retrieval successfully',
            'data' => $data,
            'total' => 0
        ];
    }

    /**
     * Api Success response
     */
    public function apiSuccess($message = false)
    {
        Yii::$app->response->statusCode = 200;
        return [
            'statusCode' => 200,
            'message' => $message ? $message : 'Success',
        ];
    }
    /**
     * Api response
     */
    public function apiResponse($httpstatus, $status, $substatus, $data = array(), $message = '', $error = '',$additional = array())
    {
        Yii::$app->response->statusCode = $httpstatus;

        if(!empty($message)) {
           $key   = "message";
           $value = $message ? $message : 'Success';
        } else {
           $key   = "error";
           $value = $error ? $error : 'Error';
        }
        return [
            'status_code' => $status,
            'sub_status_code' => $substatus,
            $key => $value,
            'data' => $data,
            'app_data' => $additional
        ];       
    }
        
        
    public function afterAction($action, $result)
    {           
        if(!in_array($action->id,array("step1","step2","step3"))){
            $this->saveApiLog($result);
    }
        return parent::afterAction($action, $result); 
    }
        
    public function saveApiLog($response) {

        $controller       = Yii::$app->controller->id;
        $action           = Yii::$app->controller->action->id;
        $request_api_name = $controller . "-" . $action;
        $request          = Yii::$app->request->getBodyParams();
        $request_api_url  = Yii::$app->request->getUrl();
        $request_method   = Yii::$app->request->method;
        $request_api_str  = json_encode($request);
        $request_api_log  = new SccmCommonFunction();
        $str_response     = json_encode($response);
        $log_array        = array(
            'api_url'          => $request_api_url, 
            'request_method'   => $request_method, 
            'request'          => $request_api_str, 
            'response'         => $str_response, 
            'request_api_name' => $request_api_name
        );
        $request_api_log->createLog($str_response, $log_array);
    }
}
