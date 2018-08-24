<?php

namespace backend\controllers;

use Yii;
use backend\models\Device;
use backend\models\DeviceSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use mdm\admin\models\User;
use common\components\Nso;
use common\components\SccmCommonFunction;
use common\components\InventoryApi;

/**
 * DeviceController implements the CRUD actions for Device model.
 */
class DeviceController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                  //  'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Device models.
     * @return mixed
     */
    public function actionIndex()
    {
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();    
        $searchModel = new DeviceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        try{
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'user'         =>   $user,
        ]);
        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Displays a single Device model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        try{
        $renderMethod =(Yii::$app->request->isAjax) ?"renderAjax":"render";
        return $this->$renderMethod('view', [
            'model' => $this->findModel($id),
        ]);
        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Creates a new Device model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
    	try {

        $model = new Device();

		$model->created_by = 1;
		$model->updated_by = 1;
		$model->created_date = date("Y-m-d H:i:s", time());
		$model->updated_date = date("Y-m-d H:i:s", time());
		
        if ($model->load(Yii::$app->request->post())) {
		   
		    $postData = Yii::$app->request->post();
			$model->hostname = $postData['Device']['hostname'];
			$model->sapid = $postData['Device']['sapid'];
			$model->loopback = $postData['Device']['loopback'];
		    $router_type = isset( $postData['Device']['device_type']) ? trim( $postData['Device']['device_type'])  : '';
            $device_type = '';
            if ($router_type == 'asr920' || $router_type == 'asr903' || $router_type == 'ecr') {
                $device_type = 'xe';
            }
            if ($router_type == 'asr9k') {
                $device_type = 'xr';
            }
            $model->device_type = $device_type;
				
            $action = 'POST';
            $data['device_type'] = $device_type;
            $data['host_name'] = $postData['Device']['hostname'];
            $data['ip_address'] = isset($postData['Device']['ip_address'])?$postData['Device']['ip_address']:'127.0.0.1';
            $data['customer_name'] = '';
            $data['authgroup'] = 'default';
				
           $nso = new Nso();	
           $data = $nso->addDevice($data, $action);
            
            // To add device in inventory
            $input = [];
            $input['hostname'] = isset($postData['Device']['hostname']) ? trim($postData['Device']['hostname']): '';
            $input['sapid'] = isset($postData['Device']['sapid']) ? trim($postData['Device']['sapid']): '';
            $input['loopback0'] = isset($postData['Device']['loopback']) ? trim($postData['Device']['loopback']): '';
            $input['loopback999'] = isset($postData['Device']['ip_address']) ? trim($postData['Device']['ip_address']): '';
            $input['routertype'] = strtoupper($router_type);
            
            $invResponse = InventoryApi::addNewDevice($input);
             
            if (!empty($invResponse) && (!empty($input['routertype']) && !empty($input['hostname']) && !empty($input['sapid']) && !empty($input['loopback0']) && !empty($input['loopback999']))) {
                \Yii::$app->getSession()->setFlash('success', $invResponse);
            } else {
                \Yii::$app->getSession()->setFlash('error', 'Invalid arguments provided');
            }
			
			//if (empty($data)) {
				if ($model->save()) {
					\Yii::$app->getSession()->setFlash('success', 'Device added succesfully');
					return $this->redirect(['index']);
				} else {
					return $model->getErrors();
				}
			//}
			
			if (!empty($data)) {
					$xml = xmlToArray($data);
					if (isset($xml['result']) && isset($xml['info'])) {
						return $this->response_status($code = 200, $status = 'fail', $xml['info']);
					}
					
					if (isset($xml['error'])) {
						return $this->response_status($code = 300, $status = 'fail', $xml['error']['error-message'], $xml['error']['error-message']);
						\Yii::$app->getSession()->setFlash('error', $xml['error']['error-message']);
						return $this->redirect(['index']);
					}
				}
	    }

        return $this->renderAjax('create', [
            'model' => $model,
        ]);
    	} catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
	
	}

    /**
     * Updates an existing Device model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
    	try{

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Deletes an existing Device model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
    	try{
       $model = $this->findModel($id);
       $hostname = $model->hostname;
	   $data['host_name'] = $hostname;
		
	   $nso = new Nso();
	   $data = $nso->deleteDevice($data);
	   $this->findModel($id)->delete();
		
		/*
		if (!empty($data)) {
            $xml = xmlToArray($data);
            if (isset($xml['result']) && isset($xml['info'])) {
                return $this->response_status($code = 200, $status = 'fail', $xml['info']);
            }
            if (isset($xml['result']) && $xml['result'] != 'error') {
                return $this->response_status($code = 200, $status = 'success', '', ['delete' => $xml['result']]);
            }
        }
        **/
		
		\Yii::$app->getSession()->setFlash('success', 'Device deleted succesfully');
        return $this->redirect(['index']);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }
	
	public function actionSyncFrom($id) {
		
		try{

		$id = Yii::$app->request->get('id'); 
		$sql = "SELECT * FROM tbl_device where id = $id LIMIT 1";			
		$results = \Yii::$app->db->createCommand($sql)->queryOne();
	    $device_name = $results['hostname'];
		
		$nso = new Nso();
		$data = $nso->syncfrom($device_name);
		$status = $data?"success":"failed";
		return $this->renderAjax("sync-from", [
			"devices" => [
				[
					"hostname"=>$device_name,
					"status"=>$status,
					"errors"	=> !empty($nso->errors)?$nso->errors:"",	
				]
			]
		]);

		} catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
		// if (!empty($data)) {
            // $xml = xmlToArray($data);
            // if (isset($xml['result']) && isset($xml['info'])) {
                // return $this->response_status($code = 200, $status = 'fail', $xml['info']);
            // }
            // if (isset($xml['result']) && $xml['result'] != 'error') {
                // return $this->response_status($code = 200, $status = 'success', '', ['syncFrom' => $xml['result']]);
            // }
        // }
        //return $this->response_status($code = 200, $status = 'success', '', $data);
	} // actionSyncFrom()
	
	
	public function actionSyncFromAll(){
		
		try{

		$device_id = Yii::$app->request->post('device_id');		
        $device_id = implode(",", $device_id);	
		
		$sql = "SELECT * FROM tbl_device where id in ($device_id)";			
		$results = \Yii::$app->db->createCommand($sql)->queryAll();
	    
		$result_all = '';
		$devices = [];
		
		foreach ($results as $key =>$val) {
			$device_name = $val['hostname'];
			$nso = new Nso();	
			$data = $nso->syncfrom($device_name);
			
			$status = $data?"success":"failed";
			$devices[] = [
				"hostname"	=> $device_name,
				"status"	=> $status,
				"errors"	=> !empty($nso->errors)?$nso->errors:"",	
			];
			// if (!empty($data)) {
				// $xml = xmlToArray($data);
				
				// if (isset($xml['result']) && $xml['result'] === 'false') {
				   // $result_all .= $xml['info']."<br>";
					// //$result_all .= $xml['info']."SyncFrom done successfully for device $device_name"."<br>";
				// }
				
				// if (isset($xml['result']) && $xml['result'] != 'false') {
					 // $result_all .= $xml['info']."<br>";
				// }
				
				// /*
				// if (isset($xml['result']) && $xml['result'] != 'error') {
					// $result_all .= "SyncFrom done successfully for device $device_name"."<br>";
				// }
				
				// if (isset($xml['result']) && isset($xml['info'])) {
					// $result_all .= "SyncFrom error for device $device_name"."<br>";
				// }
				// ***/				
			// }
		}
	   return $this->renderAjax("sync-from", [
			"devices" => $devices,
		]);
		
	   return $result_all;

	   } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }

	} // actionSyncFromAll()
	
	public function actionSyncTo($id) {
		
		try{

		$id = $_REQUEST['id']; // role id
		$sql = "SELECT * FROM tbl_device where id = $id";			
		$results = \Yii::$app->db->createCommand($sql)->queryOne();
	    $device_name = $results['hostname'];
		
		$nso = new Nso();
		$data = $nso->syncto($device_name);
		$status = $data?"success":"failed";
		return $this->renderAjax("sync-from", [
			"devices" => [
				[
					"hostname"=>$device_name,
					"status"=>$status,
					"errors"	=> !empty($nso->errors)?$nso->errors:"",	
				]
			]
		]);
		//$result_all = '';
		// if (!empty($data)) {
            // $xml = xmlToArray($data);
			// if (isset($xml['result']) && $xml['result'] === 'false') {
				   // $result_all .= $xml['info']."<br>";
					// //$result_all .= $xml['info']."SyncFrom done successfully for device $device_name"."<br>";
				// }
				
			// if (isset($xml['result']) && $xml['result'] != 'false') {
				 // $result_all .= $xml['info']."<br>";
			// }
		// }
	  //return $result_all;	
		} catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }

	} 
	
	public function actionSyncToAll() {
		
		try {

		$device_id = Yii::$app->request->post('device_id');	
        $device_id = implode(",", $device_id);	
		
		$sql = "SELECT * FROM tbl_device where id in ($device_id)";			
		$results = \Yii::$app->db->createCommand($sql)->queryAll();
	    
		$devices = [];
		foreach ($results as $key =>$val) {
			$device_name = $val['hostname'];
			$nso = new Nso();
			$data =  $nso->syncto($device_name);
			$status = $data?"success":"failed";	
			$devices[] = [
				"hostname"	=> $device_name,
				"status"	=> $status,
				"errors"	=> !empty($nso->errors)?$nso->errors:"",	
			];
		}
		return $this->renderAjax("sync-from", [
			"devices" => $devices,
		]);
		
		} catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
		// $result_all = '';
		// $nso = new Nso();
		// foreach ($results as $key =>$val) {
			// $device_name = $val['hostname'];
			// $nso->syncto($device_name);
			// $result_all .= "SyncTo done successfully for device $device_name"."<br>";
			
		// }
	  // echo $result_all;
	} // actionSyncToAll()
	
   
    public function actionSyncCheck($id) {
	
		ini_set("display_errors", "1");
		error_reporting(E_ALL);
		
		$id = $_REQUEST['id']; // role id
		$sql = "SELECT * FROM tbl_device where id = $id";			
		$results = \Yii::$app->db->createCommand($sql)->queryOne();
	    $device_name = $results['hostname'];
		
		$nso = new Nso();
		$data = $nso->getChecksync($device_name);
		
		$result_all = '';
		if (!empty($data)) {
            $xml = xmlToArray($data);
			if (isset($xml['result']) && $xml['result'] === 'false') {
				   $result_all .= $xml['info']."<br>";
					//$result_all .= $xml['info']."SyncFrom done successfully for device $device_name"."<br>";
				}
				
			if (isset($xml['result']) && $xml['result'] != 'false') {
				 $result_all .= $xml['info']."<br>";
			}
		}
		return $result_all;
	} // actionSyncCheck()
   
   
   
   /**
     * Finds the Device model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Device the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Device::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	
}
	
