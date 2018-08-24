<?php

namespace backend\controllers;

use yii;
use yii\helpers\Json;
use backend\modules\api\models\Auth;

class BaseController extends \yii\web\Controller {
    
    protected $request_methods =[];
    public $enableCsrfValidation = false;
 

    public function response_status($code = 200, $status = 'success', $message = '',$data=[]) {
        $ret['code'] = $code;
        $ret['status'] = $status;		
        if ($status == 'success') {
            $ret['message'] = $message;
            $ret['data'] = $data;
        } else {
            $ret['errors'] = $message;
        }
        $response = \Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data = $ret;
	return $response;
    }

    protected function modelErrors($errors) {
        
        if(!$errors) {
            return $errors;
        }
        $ret =[];
        foreach($errors as $field=>$errArray) {
            $ret[$field]=$errArray[0];
        }
        return $ret;
    }

    /*
    @Desc: To use this function declare property with action method and their required method in your controller like
            protected $request_methods = [
                    "dry-run" => 'POST',
            ];
    */
    protected function validateRequestMethod($controller,$action,$requestType) {
        if(!$requestType) return false;
        $request = Yii::$app->request;
        if(!empty($requestType)) {
            if($requestType =="POST" && !$request->isPost) {
                return false;
            }
            if($requestType =="GET" && !$request->isGet) {
                return false;
            }
            if($requestType =="PATCH" && !$request->isPatch) {
                return false;
            }
			if($requestType =="PUT" && !$request->isPut) {
                return false;
            }
			if($requestType =="DELETE" && !$request->isDelete) {
                return false;
            }
        }
        return true;
    } 

    private function validateAuthToken($controller,$action,$requestType) {
        $request = Yii::$app->request;
        $token = false;
        if($controller!="auth" && $action!="login") {
            if($requestType =="POST"){		
                if(!$request->post("token")) {
                    return false;
                }
                $token = $request->post("token");
            }
            if($requestType =="GET"){
                if(!$request->get("token")) {
                    return false;
                }
                $token = $request->get("token");
            }			
        }
        if(!empty($token) && !Auth::validateToken($token)) {
            $this->response_status($code = 404, $status = 'fail',["token"=>'Invalid Token']); 		
            Yii::$app->end();
            return false;
        }
        return true;
    }

	/*
    public function beforeAction($action) {
        $controller = Yii::$app->controller->id;
        $action = Yii::$app->controller->action->id;
        $requestType = isset($this->request_methods[$action]) ? $this->request_methods[$action]:false;
        if(!$this->validateRequestMethod($controller,$action,$requestType)) {
             $this->response_status($code = 404, $status = 'fail','Invalid request method'); 		
            Yii::$app->end();
        }

        if(!$this->validateAuthToken($controller,$action,$requestType)) {
            $this->response_status($code = 404, $status = 'fail','Invalid request. Token must required to access this api'); 		
            Yii::$app->end();
            return false;
        }
        return parent::beforeAction($action);
    }
	**/
}