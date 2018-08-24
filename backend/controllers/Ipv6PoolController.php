<?php

namespace backend\controllers;

use Yii;
use backend\models\Ipv6Pool;
use backend\models\Ipv6PoolSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\widgets\ActiveForm;
use common\components\IPV6SubnetCalculator;

/**
 * Ipv6PoolController implements the CRUD actions for Ipv6Pool model.
 */
class Ipv6PoolController extends Controller
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
    
    public function incrementIp($ip, $increment)
    {
      $addr = inet_pton ( $ip );

      for ( $i = strlen ( $addr ) - 1; $increment > 0 && $i >= 0; --$i )
      {
        $val = ord($addr[$i]) + $increment;
        $increment = $val / 256;
        $addr[$i] = chr($val % 256);
      }

      return inet_ntop ( $addr );
    }

    /**
     * Lists all Ipv6Pool models.
     * @return mixed
     */
    public function actionIndex()
    {
        /*$ipv6Obj = new IPV6SubnetCalculator();
        if($ipv6Obj->testValidAddress("2002:c0a8:0002:0000:0000:0000:0000:0000")) {
           //echo $ipv6Obj->unabbreviateAddress("2002:c0a8:2::");
           //echo $ipv6Obj->abbreviateAddress("2002:c0a8:0002:0000:0000:0000:0000:0000");
           //$arripv6 = $ipv6Obj->getAddressRange("2002:c0a8:0002:0000:0000:0000:0000:0000", 6);
            
            $arripv6 = $this->incrementIp("2002:c0a8:0002:1000:1000:0000:0000:DDDD", 30);
           echo $arripv6;
            //echo "Valid IPv6";
            die;
        } else {
            echo "Invalid Address";
            die;
        }*/
        
        try {

        $searchModel = new Ipv6PoolSearch();
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
     * Displays a single Ipv6Pool model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
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
     * Creates a new Ipv6Pool model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        try{

        $model = new Ipv6Pool();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        
        if ($model->load(Yii::$app->request->post())) {
            $model->created_by = 1;
            $model->created_date = "2018-05-08 11:28:00";
            $model->updated_by = 1;
            $model->updated_date = "2018-05-08 11:28:00";
            
            if($model->save()) {
               return $this->redirect(['index']);
            }
        }
        
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
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
     * Updates an existing Ipv6Pool model.
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
            return $this->redirect(['index']);
        }
        
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);  
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
     * Deletes an existing Ipv6Pool model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
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
     * Finds the Ipv6Pool model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Ipv6Pool the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ipv6Pool::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
