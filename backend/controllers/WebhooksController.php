<?php

namespace backend\controllers;

use Yii;
use backend\models\Webhooks;
use backend\models\WebhooksSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
/**
 * WebhooksController implements the CRUD actions for Webhooks model.
 */
class WebhooksController extends Controller
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
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Webhooks models.
     * @return mixed
     */
    public function actionIndex()
    {
        try{

        $searchModel = new WebhooksSearch();
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
     * Displays a single Webhooks model.
     * @param integer $id
     * @param string $created_date
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        try{

        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Creates a new Webhooks model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        try{

        $model = new Webhooks();

        if ($model->load(Yii::$app->request->post())) {
            $model->created_by = \Yii::$app->user->id;
            $model->created_date = date("Y-m-d H:m:s");
            if ($model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }





    }

    /**
     * Updates an existing Webhooks model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @param string $created_date
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        try{

        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
        $model->modified_by = \Yii::$app->user->id;
        $model->modified_date = date("Y-m-d H:m:s");
        if ($model->save()) {

            return $this->redirect(['view', 'id' => $model->id]);
        }
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
     * Deletes an existing Webhooks model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @param string $created_date
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        try{

        $this->findModel($id)->delete();

        return $this->redirect(['index']);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Finds the Webhooks model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param string $created_date
     * @return Webhooks the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Webhooks::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionWebhook($service_order_id=null,$provider_id=null,$status=null,$description=null,$device_list=null){
        $provider_id=1;
        $webhook_data = Webhooks::findOne(['id' => $provider_id]);
        if(!empty($webhook_data->url)){
            
            $request_url=$webhook_data->url;
            $service_order_id=$status=$description="testing";
            $device_list=array('1'=>'data1','2'=>'data2','3'=>'data3');
            
            #curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$request_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(array('service_order_id'=>$service_order_id,'status'=>$status,'description'=>$description,'device_list'=>$device_list)));
            
            #server response
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec ($ch);
            curl_close ($ch);
            
            // further processing/...
            if (!empty($server_output)) {  
                print_r($server_output);
            } else { 
                echo "No data available";
            } 
        }
        else
        {
            echo "No data available to send a request";
        }

       
    }



}
