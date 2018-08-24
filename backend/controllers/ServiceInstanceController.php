<?php

namespace backend\controllers;

use Yii;
use backend\models\ServiceInstance;
use backend\models\ResourceManager;
use backend\models\ServiceInstanceSearch;
use backend\models\ServiceInstanceDevices;
use backend\models\ServiceDeploy;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\Payload;

/**
 * ServiceInstanceController implements the CRUD actions for ServiceInstance model.
 */
class ServiceInstanceController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all ServiceInstance models.
     * @return mixed
     */
    public function actionIndex() { 

        try{

        $searchModel = new ServiceInstanceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Displays a single ServiceInstance model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        try{

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('view', [
                'model' => $this->findModel($id),
            ]);
        }
        
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Creates a new ServiceInstance model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
 	public function actionCreate() {
        $response = [
             "success" => true,
             "data"    => [],
             "errors"  => []   
        ];

        $isAjax = Yii::$app->request->isAjax;

        $model = new ServiceInstance();
        
        if ($isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $res = ActiveForm::validate($model);
            if($res){
                $response["success"] = false;
                $response["errors"] = $res;
                return $response;
            }            
        }
        
        if ($model->load(Yii::$app->request->post())) {
            //$model->service_order_id = $model->service_order_id."_".rand(1,9999);
            $postVariables = Yii::$app->request->post();
			
            $devices = $postVariables['ServiceInstance']['devices'];
            $template = (!empty($postVariables['ServiceInstance']['template'])?$postVariables['ServiceInstance']['template']:'');
			$userdefined = (!empty($postVariables['ServiceInstance']['UserDefined'])?$postVariables['ServiceInstance']['UserDefined']:"");
			
        $transaction = Yii::$app->db->beginTransaction();
        try {	
            if ($model->save()) {            
					$instanceDevices = new ServiceInstanceDevices();
                    $serviceInstance = $instanceDevices->service_instance_id = $model->id;
					$instTemplate = "";
			    /*  $instTemplate    = (!empty($device['template']))?$device['template']:'';
                    $instDevice      = (!empty($device['device']))?$device['device']:'';
                    $instRole        = (!empty($device['role']))?$device['role']:'1';
                */    
                    if (!isset($userdefined) || empty($userdefined)) {
                        if($isAjax){
                             $model->addError("form_error","Invalid user defined data.");
                             $response["success"] = false;
                             $response["errors"] = $model->errors;
                             return $response;
                        }
                        Yii::$app->session->setFlash("error","Invalid user defined data.");
                        return $this->redirect(['index']);
                    }
					
                    $instUserDefined = (isset($userdefined) && count($userdefined) > 0)?$userdefined:'';
                    $instUserDefined = (!empty($instUserDefined))?serialize($instUserDefined):'';
                    
					$instanceDevices->template_id         = $template;
                    $instanceDevices->role_id             = 1;
                    $instanceDevices->user_defined_data   = $instUserDefined;
					
                  /*   $systemValues = ResourceManager::getSystemDefinedVariablesArray($instRole, $instDevice, $model->service_model_id, $model->customer_id, $model->id, $device['UserDefined']);
                   */
				    $payload = new \common\components\PayLoadFrontend();
					
			    	$finalPayload = ["payload"=>"","variables"=>[]];//$payload->createPayload($serviceInstance,$devices,$userdefined);
					
				
					$returnvariables =  [ //Todo::static
                        12 => [
                            "device_id"=>12,
                            "hostname"=>"MUMB",
                            "user_defined" => [
                                "value" => ["sadsad"=>"123sd",
                                 "sadsad"=>array("61er23","df234") 
                				],
                				 "key" => [
                					1=>"123434dsf",
                					2=>array("12sdf56","2sdf34")
                				]				 
                            ],
                			"system_defined" => [
                                "value" => ["sadsad"=>"123sd",
                                 "sadsad"=>array("61er23","df234") 
                				],
                				 "key" => [
                					1=>"123434dsf",
                					2=>array("12sdf56","2sdf34")
                				]				 
                            ]	
                        ],
                        6 => [
                            "device_id"=>6,
                            "hostname"=>"MUMB",
                            "user_defined" => [
                                "value" => ["sadsad"=>"123sd",
                                 "sadsad"=>array("61er23","df234") 
                				],
                				 "key" => [
                					1=>"123434dsf",
                					2=>array("12sdf56","2sdf34")
                				]				 
                            ],
                			"system_defined" => [
                                "value" => ["sadsad"=>"123sd",
                                 "sadsad"=>array("61er23","df234") 
                				],
                				 "key" => [
                					1=>"123434dsf",
                					2=>array("12sdf56","2sdf34")
                				]				 
                            ]	
                        ]
                    ];
				foreach ($returnvariables as $key => $value){
					$device['SystemDefined'] = $value['system_defined']['value'];
                    $instanceDevices->system_defined_data = serialize($value['system_defined']['value']);
                    $instanceDevices->device_id           = $key;
                    
                    
                    if(!empty($serviceInstance) && !empty($value['user_defined']['value'])){
                        $device['UserDefined'] = $value['user_defined']['value'];
						$device['template'] = $template;
						$device['role'] =  1;
						$instanceDevices->nso_payload =  $finalPayload['payload'];
						//$instanceDevices->nso_payload = ServiceInstance::setPayloadValues($device);
                    }
					ResourceManager::insertDeviceVariableMapping($model->id, $serviceInstance, $key, $value['system_defined']['value']);
                    if ($instanceDevices->save()) {
							"SAVED";
                    } else {
							"NOT-SAVED";
                    }
					
    				//$finalPayload = ServiceInstance::getFinalNsoPayload($model->id);
                    $model->final_nso_payload = json_encode($finalPayload['payload'],JSON_UNESCAPED_SLASHES);
    				$res = $model->save();
				}	
                
				# new added by Jitendra
                $transaction_id = $model->id;
                $action = '';
                $scheduled_date = '';
                //$ret = ServiceDeploy::ServiceInvokeStep3($transaction_id, $action, $scheduled_date);
                $transaction->commit();
             
                ###
                
                 if($isAjax){
                     $response["data"] = [
                        "service_instance_id"=> $model->id,
                        "msg"=>"Service Instance Saved Successfully"
                     ];
                     return $response;
                }
                Yii::$app->session->setFlash("success","Service Instance added Successfully");
                return $this->redirect(['index']);
            }
        # New added
        } catch (\Exception $e) {
        $transaction->rollBack();
        throw $e;
        }
        ## New Added
    }
        
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }


	public function actionCreateOld() {

        $model = new ServiceInstance();
        
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        
        if ($model->load(Yii::$app->request->post())) {
            $postVariables = Yii::$app->request->post();
			
            $devices = $postVariables['ServiceInstance']['devices'];
            
        $transaction = Yii::$app->db->beginTransaction();
        try {	
            if ($model->save()) {
            
					foreach($devices as $device) {
                    $instanceDevices = new ServiceInstanceDevices();
                    $instanceDevices->service_instance_id = $model->id;
                    $instTemplate    = (!empty($device['template']))?$device['template']:'';
                    $instDevice      = (!empty($device['device']))?$device['device']:'';
                    $instRole        = (!empty($device['role']))?$device['role']:'';
                    
                    if (!isset($device['UserDefined']) || empty($device['UserDefined'])) {
                        Yii::$app->session->setFlash("error","Invalid user defined data.");
                        return $this->redirect(['index']);
                    }
                    $instUserDefined = (isset($device['UserDefined']) && count($device['UserDefined']) > 0)?$device['UserDefined']:'';
                    $instUserDefined = (!empty($instUserDefined))?serialize($instUserDefined):'';
                    $instanceDevices->template_id         = $instTemplate;
                    $instanceDevices->device_id           = $instDevice;
                    $instanceDevices->role_id             = $instRole;
                    $instanceDevices->user_defined_data   = $instUserDefined;
					
                     $systemValues = ResourceManager::getSystemDefinedVariablesArray($instRole, $instDevice, $model->service_model_id, $model->customer_id, $model->id, $device['UserDefined']);
                   

                    $device['SystemDefined'] = $systemValues['value'];
                    $instanceDevices->system_defined_data = serialize($systemValues['value']);
                   // pe($systemValues,ServiceInstance::setPayloadValues($device));
                    
                    if(!empty($instTemplate) && !empty($instUserDefined)){
                        $instanceDevices->nso_payload = ServiceInstance::setPayloadValues($device);
                    }
				    
                    ResourceManager::insertDeviceVariableMapping($model->id,$instTemplate, $instDevice, $systemValues['key']);
                    if ($instanceDevices->save()) {

                    } else {

                    }
                }
                $finalPayload = ServiceInstance::getFinalNsoPayload($model->id);
                $model->final_nso_payload = $finalPayload;
                $model->save();
                
                # new added by Jitendra
                $transaction_id = $model->id;
                $action = '';
                $scheduled_date = '';
                $ret = ServiceDeploy::ServiceInvokeStep3($transaction_id, $action, $scheduled_date);
                $transaction->commit();
                ###
                
                Yii::$app->session->setFlash("success","Service Instance added Successfully");
                return $this->redirect(['index']);
            }
        # New added
        } catch (\Exception $e) {
        $transaction->rollBack();
        throw $e;
        }
        ## New Added
    }
        
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }


    /**
     * Updates an existing ServiceInstance model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            //$this->setParameterValue($model);      
            if ($model->save()) {
				
				# By Jitendra
				$transaction_id = $model->id;
				$action = '';
				$scheduled_date = '';
				$ret = ServiceDeploy::ServiceInvokeStep3($transaction_id, $action, $scheduled_date);
				##
				
                Yii::$app->session->setFlash("success","Service Instance Updated Successfully");
                return $this->redirect(['index', 'id' => $model->id]);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);  
        }
        
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ServiceInstance model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        try{

        $this->findModel($id)->delete();
        return $this->redirect(['index']);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Finds the ServiceInstance model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ServiceInstance the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = ServiceInstance::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    public function actionTest(){        
		$hostname1 = "BGLRRLABESR001";
        $hostname2 = "MUMBRLB1EDS017";
        $interface = "Gig0/0/9";

        //$topo = ResourceManager::rin($hostname1,'SUB_PS');
        $topo = ResourceManager::getNeibhourHost($hostname1,$hostname2);
        pe($topo);
    }

    public function actionDeleteService(){
		$id = Yii::$app->request->get("id");
		$response = [
				"success"=> true,
				"data"=>[],
				"errors"=>[],
		];
		if($id){
			$serviceInstance = new ServiceInstance();
			$res = $serviceInstance->deleteService($id);		
			if(!$res){
				$response["success"] = false;
				$response["errors"] = $serviceInstance->getFirstError("id");
			}else{			
				$response["msg"] = "Service (".$res->service_order_id.") Deleted Successfully ";
			}
		}else{
			$response["success"] = false;
			$response["errors"] = "Invalid Request. Service Instance Id must required";
		}
		
		return json_encode($response);
	}
}
