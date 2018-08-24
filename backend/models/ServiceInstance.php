<?php

namespace backend\models;

use backend\models\DeviceRole;
use backend\models\ServiceInstanceDevices;
use backend\models\ServiceModel;
use backend\models\Services;
use common\components\Nso;
use Yii;

/**
 * This is the model class for table "tbl_service_instance".
 *
 * @property int $id
 * @property int $service_order_id
 * @property int $customer_id
 * @property int $service_model_id
 * @property int $endpoints
 * @property string $scheduled_status
 * @property string $scheduled_date
 * @property int $is_active
 */
class ServiceInstance extends \yii\db\ActiveRecord {

    public $form_error;
    public $serviceStatusArr = [
        0 => 'Added', 
        1 => 'Ready To Process', 
        2 => 'In Processing', 
        3 => 'Completed'
    ];
    public $serviceStatusColorArr = [
        0 => '#f5f5f5', 
        1 => '#33b5e5', 
        2 => '#ffbb33', 
        3 => '#00C851'
    ];
    public $serviceActionArr = [
        0 => 'No Action',
        1 => 'Commit', 
        2 => 'Schedule', 
        3=>'Future Use',
        4 => 'Dry Run'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'tbl_service_instance';
    }

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			//  [['name', 'customer_id', 'service_model_id', 'endpoints', 'scheduled_date'], 'required'],
			[['service_order_id', 'customer_id', 'service_model_id', 'endpoints'], 'required'],
			[['customer_id', 'endpoints', 'service_model_id'], 'integer'],
			[['user_defined_data', 'system_defined_data', 'device_list', 'service_order_id', 'scheduled_status'], 'string'],
			[['endpoints'], 'integer', 'min' => 1, 'max' => 10],
			[['scheduled_date','status','action'], 'safe'],
            //[['service_order_id'], 'unique', 'targetAttribute' => ['service_order_id'], 'message' => 'Service Order Id Already Exist!!!'],
			//[['service_order_id'], 'unique'], // 16th Aug 2.018
			
			//[['is_active'], 'string', 'max' => 1],
		];
	}

    public function behaviors() {
        if (isset(Yii::$app->user->isGuest)) {
            return [];
        }
        return [
            [
                'class' => \yii\behaviors\BlameableBehavior::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
   public function attributeLabels() {
        return [
            'id' => 'ID',
            'service_order_id' => 'Service Order ID',
            'customer_id' => 'Customer',
            'service_model_id' => 'Service Model',
            'endpoints' => 'Endpoints',
            'user_defined_data' => 'User Defined Data',
            'system_defined_data' => 'System Defined Data',
            'device_list' => 'Device List',
            'final_nso_payload' => 'Final Nso Payload',
            'scheduled_status' => 'Deploy Status',
            'scheduled_date' => 'Scheduled Date',
            'created_by' => 'Created By',
            'created_on' => 'Created On',
            'updated_by' => 'Updated By',
            'updated_on' => 'Updated On',
            'is_active' => 'Status',
            'service_instance_status' => 'Service Instance Status',
            'type_of_change' => 'Type Of Change',
            'inclusion_hostname' => 'Inclusion Hostname',
            'rt_export_import' => 'Rt Export Import',
            'topology' => 'Topology',
            'hub_hostname' => 'Hub Hostname',
            'uniqueId' => 'Unique ID',
            'status' => 'Status',
            'action' => 'Action',
            'remarks' => 'Remarks',
        ];
    }

    public static function get_string_between($string, $start="{", $end="}"){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public static function findVariables($cli_config){

        $cli_config_arr = explode("\n",$cli_config);
        $variables = [];
        foreach ($cli_config_arr as $key => $line) {
            $variable = self::get_string_between($line);
            if(!empty($variable) &&  strpos($variable,":")===false){
                    $variables[] = "{".$variable."}";

            }
        }
        return array_unique($variables);
    }

    public static function setPayloadValues($device) {
        $tagValues = array();
        $instancePayload  = ServiceModelTemplate::getTemplatePayload($device['template']);
        
       // $device['SystemDefined'] = array();
        $payloadVariables = array_merge($device['UserDefined'], $device['SystemDefined']);
	    foreach ($payloadVariables as $ukey => $uvalue) {
            // if ($ukey == 'rt_value' || $ukey == 'rd_value') {
                // $tagValues['{' . $ukey . '}'] = $uvalue;
            // }
			$uvalue = (is_array($uvalue)?serialize($uvalue):$uvalue);
			if (strpos($uvalue,"[")!==false || strpos($uvalue,"{")!==false) {
               
			   $tagValues['{' . $ukey . '}'] = $uvalue;
            }
			else {
                $tagValues['{' . $ukey . '}'] = '"'.$uvalue.'"';
            }
        }
        $finalPayload = strtr($instancePayload, $tagValues);
			
        $remainingVariables = self::findVariables($finalPayload);

        if(!empty($remainingVariables)){
            $remainingVariables = array_fill_keys($remainingVariables,'""'); 
            $finalPayload = strtr($finalPayload, $remainingVariables);  
        }
		
        return $finalPayload;
    }

    public static function getFinalNsoPayload($serviceInstanceId) {

        if (($model = ServiceInstance::findOne($serviceInstanceId)) !== null) {
            $deviceConfig = array();
            $serviceModel = ServiceModel::getServiceModel($model->service_model_id);
            $serviceName = Services::getServiceName($serviceModel['service_id']);
			$subserviceModel	=	SubServices::find()->where(['id' =>	$serviceModel['sub_service_id']])->one();
            $serviceDevices = ServiceInstanceDevices::find()
                              ->where(['service_instance_id' => $serviceInstanceId, "is_active" => 1])
                              ->asArray()
                              ->All();
            
                                
            foreach ($serviceDevices as $device) {
                $deviceRole = DeviceRole::getDeviceRole($device['role_id']);
                $deviceConfig[$deviceRole][] = json_decode($device['nso_payload']);
               
            }

            //$final_nso_payload = array($serviceName => array(array_merge(array("service-order-id" => $model->service_order_id, "topology" => "1.13"), $deviceConfig)));
            //$final_nso_payload = array($serviceName => array(array_merge(array("service-order-id" => $model->service_order_id, "topology" => $subserviceModel['ref_id']), $deviceConfig)));
			/* $final_nso_payload = array(
                                $serviceName => array_merge(
                                                array("service-order-id" => $model->service_order_id, "topology" => $subserviceModel['ref_id']), 
                                                $deviceConfig
                                             )
                            ); */
							// return json_encode($final_nso_payload);
            return json_encode($deviceConfig);
        }
        return null;
    }

    /*Relation with Customer*/
    public function getCustomer() {
        return $this->hasOne(Customer::className(), ["id" => "customer_id"]);
    }

    /*Relation with Service Model*/
    public function getServiceModel() {
        return $this->hasOne(ServiceModel::className(), ["id" => "service_model_id"]);
    }
	
	/*
		@Desc : Delete Service from NSO 
	*/
	public function deleteService($service_instance_id){
	
			$serviceInstance = ServiceInstance::find()->alias("si")
										->joinWith(["serviceModel"])
										->where(["si.id"=> $service_instance_id])
										->one();
							
			if($serviceInstance){
				$payload_key = $serviceInstance->serviceModel->payload_key;
				$nso = new Nso();
				$res = $nso->deleteService($payload_key,$serviceInstance->service_order_id);
				if(empty($res)){
					return $res;
				}
				if(!empty($res) && $res === false){
					$this->addError("id","Service Deletion failed for service order id : ".$res);
				}
				return $res;
			}
			
			$this->addError("id","Service Instance Not Found");
			return false;
	}

    
}
