<?php 
namespace common\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\FileHelper;
use backend\models\ApiLog;
use backend\models\AuditLog;

class SccmCommonFunction {
    
    public static function getTest() {
        $test = "Testing";
        return $test;
    }

    /*
        By Jitendra
        Date : 15th January, 2018
        Upgraded by : Satish <Satish.Rane@infinitylabs.in>	
    */
    public static function createLog($message_log, $log_array = array(), $folder = true) {
        if ($message_log != "") {
            ## insert in database
            $api_url 		 = isset($log_array['api_url'])?$log_array['api_url']:'';
            $api_call_name       = isset($log_array['request_api_name'])?$log_array['request_api_name']:'';
            $request_method 	 = isset($log_array['request_method'])?$log_array['request_method']:''; 
            $request 		 = isset($log_array['request'])?$log_array['request']:'';
            $response 		 = isset($log_array['response'])?$log_array['response']:'';
            $service_template_id = isset($log_array['service_template_id'])?$log_array['service_template_id']:'';
            $folder_name         = ($folder)?"api_log":"logs";
            
            $userid 	      = Yii::$app->user->identity->id;
            //$username       = self::getUser($userid);
            $ipAddress 	      = $_SERVER['REMOTE_ADDR'];
            $useragent        = $_SERVER ['HTTP_USER_AGENT'];
            $log_str          = "\r\n[" . date('Y-m-d H:i:s') . "] [api_call_name : " . $api_call_name . "] [api_url : " . $api_url . "]";
            $log_str         .= ($request != '[]')?"\r\n[" . date('Y-m-d H:i:s') . "] [request : " . $request . "]\r\n":"\r\n";
            $log_str         .= "[" . date('Y-m-d H:i:s') . "] [ip : " . $ipAddress . "] [uid : " . $userid . "] [" . str_replace("\\n","\r\n",$message_log) . "]" . "[" . $useragent . "]" . "\r\n";
            $directoryPath    = \Yii::$app->basePath . "/runtime/" . $folder_name . "/" . date('Y-m-d');
            $auditLogFilePath = $directoryPath . "/" . $api_call_name . ".txt";
            $mkdirStatus      = FileHelper::createDirectory($directoryPath, $mode = 0777, $recursive = true);

            if(file_put_contents($auditLogFilePath, $log_str, FILE_APPEND | LOCK_EX))
            {
                //echo "<br /> LOG file created successfully";
            }

            $log_details 	         = $log_str;
            $ApiLog                      = new ApiLog();
            $ApiLog->api_url 		 = $api_url;
            $ApiLog->request_method      = $request_method;
            $ApiLog->request 		 = $request;
            $ApiLog->response 	 	 = $response;
            $ApiLog->log_details 	 = $log_details;
            $ApiLog->log_file_path 	 = $auditLogFilePath;
            $ApiLog->service_template_id = $service_template_id;
            $ApiLog->created_date 	 = date('Y-m-d H:i:s');
            $ApiLog->created_by   	 = $userid;

            if($ApiLog->save()) {
                // echo "<br /> API LOG record successfully updated";		
                // $data = ApiLog::find()->orderBy(['id'=> SORT_DESC])->limit(2)->all();
                // echo ">>>>><pre>";
                // print_r($data);
            } else {
                echo "<pre>";
                print_r($ApiLog->getErrors());
                exit;
            }
            ########################### create service inventory #######################
                   #if(count($log_array) > 0) {
                           #$this->createServiceInventory($log_array);
                   #}
            ############################################################################
        } else {
            echo("No Log Found");
        }
    } // EO createLog()
	
    /*
    function created by akash/pritee on 23.08.2018.
    purpose to create : this function will get call to generate the log file if any error or failure occure during method execution.
    */

    public static function createAuditLog($message_log, $log_array = array(), $folder = true) {
        if ($message_log != "") {
            ## insert in database
            $api_url         = isset($log_array['api_url'])?$log_array['api_url']:'';
            $api_call_name       = 'log_'.date('d-m-Y');//isset($log_array['request_api_name'])?$log_array['request_api_name']:'';
            $request_method      = isset($log_array['request_method'])?$log_array['request_method']:''; 
            $request         = isset($log_array['request'])?$log_array['request']:'';
            $response        = isset($log_array['response'])?$log_array['response']:'';
            $service_template_id = isset($log_array['service_template_id'])?$log_array['service_template_id']:'';
            $folder_name         = ($folder)?"api_log":"logs";
            
            $userid           = Yii::$app->user->identity->id;
            //$username       = self::getUser($userid);
            $ipAddress        = $_SERVER['REMOTE_ADDR'];
            $useragent        = $_SERVER ['HTTP_USER_AGENT'];
            $log_str          = "\r\n[" . date('Y-m-d H:i:s') . "] [api_call_name : " . $api_call_name . "] [api_url : " . $api_url . "]";
            $log_str         .= ($request != '[]')?"\r\n[" . date('Y-m-d H:i:s') . "] [request : " . $request . "]\r\n":"\r\n";
            $log_str         .= "[" . date('Y-m-d H:i:s') . "] [ip : " . $ipAddress . "] [uid : " . $userid . "] [" . str_replace("\\n","\r\n",$message_log) . "]" . "[" . $useragent . "]" . "\r\n";
            $directoryPath    = \Yii::$app->basePath . "/runtime/" . $folder_name . "/";
            $auditLogFilePath = $directoryPath . "/" . 'log_'.date('d_m_Y') . ".txt";
            $mkdirStatus      = FileHelper::createDirectory($directoryPath, $mode = 0777, $recursive = true);

            if(file_put_contents($auditLogFilePath, $log_str, FILE_APPEND | LOCK_EX))
            {
                //echo "<br /> LOG file created successfully";
            }

            $log_details             = $log_str;
            $AuditLog                      = new AuditLog();
            $AuditLog->api_url         = $api_url;
            $AuditLog->request_method      = $request_method;
            $AuditLog->request         = $request;
            $AuditLog->response        = $response;
            $AuditLog->log_details     = $log_details;
            $AuditLog->log_file_path   = $auditLogFilePath;
            $AuditLog->service_template_id = $service_template_id;
            $AuditLog->created_date    = date('Y-m-d H:i:s');
            $AuditLog->created_by      = $userid;

            if($AuditLog->save()) {
                // echo "<br /> API LOG record successfully updated";       
                // $data = ApiLog::find()->orderBy(['id'=> SORT_DESC])->limit(2)->all();
                // echo ">>>>><pre>";
                // print_r($data);
            } else {
                echo "<pre>";
                print_r($AuditLog->getErrors());
                exit;
            }
            ########################### create service inventory #######################
                   #if(count($log_array) > 0) {
                           #$this->createServiceInventory($log_array);
                   #}
            ############################################################################
        } else {
            echo("No Log Found");
        }
    } // EO createLog()

    public static function createServiceInventory($log_array) {
        $role_id = $log_array['role_id'];
        $sql = "SELECT sdc.service_template_id, sdc.customer_id, c.first_name, c.last_name, st.template_version, 
                st.user_variable, st.system_variable, sdc.hostname, sc.service_desc
                from service_device_config sdc 
                JOIN service_template st ON sdc.service_template_id = st.id 
                JOIN service_role sr ON sr.id = st.role_id 
                JOIN service_catalog sc ON sc.id = sr.service_id
                JOIN customer c ON c.id = sdc.customer_id
                WHERE sr.id = {$role_id} and nso_config is not null";
        $results = \Yii::$app->db->createCommand($sql)->queryAll();

        $serviceInventory = new ServiceInventory();
        foreach($results as $val) {
            $serviceInventory->service_template_id       = $val['service_template_id'];
            $serviceInventory->customer_id               = $val['customer_id'];
            $serviceInventory->customer_firstname        = $val['first_name'];
            $serviceInventory->customer_lastname         = $val['last_name'];
            $serviceInventory->service_template_version  = $val['template_version'];
            $serviceInventory->user_defined_parameters   = $val['user_variable'];
            $serviceInventory->system_defined_parameters = $val['system_variable'];
            $serviceInventory->device_name               = $val['hostname'];
            $serviceInventory->description               = $val['service_desc'];
            $serviceInventory->created_date              = date('Y-m-d H:i:s');
            $serviceInventory->created_by                = 1;
            $serviceInventory->modified_date             = date('Y-m-d H:i:s');
            $serviceInventory->modified_by               = 1;

            if($serviceInventory->save()) {

            } else {
                echo "<pre>";
                print_r($serviceInventory->getErrors());
                exit;
            }
        }
    } // EO createServiceInventory()

    public function convert_multi_array_to_string($array) {
        $str = implode("&",array_map(function($a) {return implode("~",$a);},$array));
        return $str;
    }
}
?>