<?php
namespace app\modules\versiSatu\controllers;
use app\components\Controller;
use app\models\Services;
use app\models\Customers;
use yii\web\NotFoundHttpException;
use Yii;
class CustomersController extends Controller {    
    
    public function actionIndex() {
        
    }
    
    public function actionCreate() {
        $data=[];
        try {
            $appData    =   array();    
            $dataRequest['Customers'] = Yii::$app->request->getBodyParams();        
            $model = new Customers();
            $model->company_name =  $dataRequest['Customers']['customer_name'];
            $model->customer_id =   $dataRequest['Customers']['customer_id'];
            $model->description =   $dataRequest['Customers']['customer_name'];
            $model->provider_id =   1;// provider id of Provider Company Name as Reliance or RJIL
            $model->email_id =   'info@'. strtolower($dataRequest['Customers']['customer_name'])."com";
            $model->mobile_number =   '1111111111';
            $model->address =   $dataRequest['Customers']['customer_name'];
			$model->created_by =   	1;
            $model->city =   'Mumbai';
            $model->state =   'Maharashtra';
            $model->country =   'India';
            $model->active_status =   1;
            $model->approve_status =   1;
            $model->created_date = date('Y-m-d H:i:s');
            $model->updated_date = date('Y-m-d H:i:s');
            if(isset($dataRequest['Customers']['app_data'])) {
                $appData    =   $dataRequest['Customers']['app_data'];
            }
            if ($model->save()) {
                $data['customer_id']    =   $dataRequest['Customers']['customer_id'];
                /* if(isset($appData)){
                    $data['app_data']   =   $appData;
                } */            
                return $this->apiResponse(200, 1001, 2001, $data, "Customer Created Successfully", $error="", $appData);
            } else {
                $this->saveApiLog('Customer already present, kindly provide valid values of Customer');
                throw new NotFoundHttpException('Customer already present, kindly provide valid values of Customer');
            }
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            return $this->apiResponse(200, 5000, 5002, $data, $message = "", $error);
        }        
    }
        
    public function actionUpdate($id) {
        $data = array();
        try {
            $appData    =   array();
            $dataRequest['Customers'] = Yii::$app->request->getBodyParams();  
            $model = $this->findModelByCustomerId($id);        
            $model->company_name    =   $dataRequest['Customers']['customer_name'];
            $model->updated_date = date('Y-m-d H:i:s');
            if(isset($dataRequest['Customers']['app_data'])) {
                $appData = $dataRequest['Customers']['app_data'];
            }
            
            if ($model->save()) {
                $data['customer_id'] = $id;
                return $this->apiResponse(200, 1002, 2001, $data, "Customer Updated Successfully", $error="");
            } else {
                throw new NotFoundHttpException('Kindly provide valid values of Customer');
            }
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            return $this->apiResponse(200, 5000, 5001, $data, $message = "", $error, $appData);
        }  
    }
    
    public function actionDelete($id) {
        $data = [];
        try {
            $appData = array();    
            $dataRequest['Customers'] = Yii::$app->request->getBodyParams(); 
            if(isset($dataRequest['Customers']['app_data'])) {
                $appData = $dataRequest['Customers']['app_data'];
            }
            $model = $this->findModelByCustomerId($id);      
            $model->approve_status  = 1;
            $model->active_status   = 0;
            if ($model->save()) {
                $data['customer_id'] = $model['customer_id'];           
                return $this->apiResponse(200, 1003, 3001, $model, "Customer Deleted Successfully", $error="");
            }
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            return $this->apiResponse(200, 5000, 5003, $data, $message = "", $error,$appData);
        }  
    }

    protected function findModel($id) {
        if (($model = Customers::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Resource not found');
        }
    }

    protected function findModelByCustomerId($id) {
        if (($model = Customers::find()->where(['customer_id'=>$id])->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Customer Resource not found');
        }
    }
    
    public function actionServicedata($id) {
        $data  =  array();
        try {
            $model = $this->findModelByCustomerId($id);                  
            $serviceModel = new Services();
            $services = $serviceModel->getAllServicesByCustomerId($model['id']);
            $serviceData    =array();
            foreach($services as $service){
                $serviceData[]  =$service;
            }
            $data['customer_id']    =   $id;
            $data['customer_name']  =   $model['company_name'];
            $data['services']   = $serviceData;
            return $this->apiResponse(200,1004,4001,$data,$message = "Customer Data retrieval successfully");
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            return $this->apiResponse(200, 5000, 5003, $data, $message = "", $error);
        }
    }
}