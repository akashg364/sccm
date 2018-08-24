<?php

namespace backend\controllers;

use Yii;
use backend\models\ServiceInstance;
use backend\models\ServiceInstanceSearch;
use yii\widgets\ActiveForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;


/**
 * CompareController implements the CRUD actions for ServiceInstance model.
 */
class CompareController extends Controller
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
 
    public function actionCompareConfig($id) {
        return $this->render('compare-config');
    }
    
     public function actionIndex() {
        $searchModel = new ServiceInstanceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('get-service-status-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);   
    }  
}

