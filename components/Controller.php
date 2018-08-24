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
        
    public function afterAction($action, $result)
    {           
        if(!in_array($action->id,array("step1","step2","step3"))){
            $this->saveApiLog($result);
    }
        return parent::afterAction($action, $result); 
    }
        
    public function saveLog($response) {

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
        $request_api_log->createLog($str_response, $log_array, false);
    }
}
