<?php

namespace backend\controllers;

use Yii;
use backend\models\SubServices;
use backend\models\SubServiceFilters;
use backend\models\SubServicesSearch;
use backend\models\ServiceComponentReference;
use backend\models\Component;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;

/**
 * SubServicesController implements the CRUD actions for SubServices model.
 */
class SubServicesController extends Controller {

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
     * Lists all SubServices models.
     * @return mixed
     */
    public function actionIndex() {
        try{
        $searchModel = new SubServicesSearch();
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
     * Displays a single SubServices model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        try{

        return $this->renderAjax('view', [
                    'model' => $this->findModel($id),
        ]);
        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Creates a new SubServices model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        try{
        $model = new SubServices();
        //$modelSubServiceFilters = new SubServiceFilters();
        // $subServiceFilters = new SubServiceFilters();		 
        // $sub_service_filters = $subServiceFilters->getSubServiceFilter();
        //$sub_service_filters = '123';

        $components = Component::find()
                ->joinWith(["componentBlocks"])
                ->asArray()->all();

        if ($model->load(Yii::$app->request->post())) {
            
            $components_data = isset(Yii::$app->request->post()["SubServices"]["components"]) ? Yii::$app->request->post()["SubServices"]["components"] : array();
            if ($model->save()) {
                if (!empty($components_data) && is_array($components_data)) {
                    ServiceComponentReference::setServiceComponentReference($components_data, $model->id);
                    Yii::$app->session->setFlash("success","Sub service ".$model->ref_id." created successfully");
                }
                $this->redirect(['index']);
            }
        }
        return $this->renderAjax('create', [
                    'model' => $model,
                    'sub_service_filters' => $this->getFilters(),
                    'components' => $components
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    public function getFilters() {
        $subServiceFilters = new SubServiceFilters();
        return $sub_service_filters = $subServiceFilters->getSubServiceFilter();
    }
    
    

    
    /**
     * Updates an existing SubServices model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        try{

        $model = $this->findModel($id);
       $components = Component::find()
                ->joinWith(["componentBlocks"])
                ->asArray()->all();

        $service_components_ref = $assign_components = $component_ids = [];
        $assign_components = ServiceComponentReference::getComponentBlocks($id);

        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $components_data = isset(Yii::$app->request->post()["SubServices"]["components"]) ? 
                    Yii::$app->request->post()["SubServices"]["components"] : array();
            if (!empty($components_data) && is_array($components_data)) {
                ServiceComponentReference::setServiceComponentReference($components_data, $model->id);
            } else {
                ServiceComponentReference::deleteAll(['service_id' => $model->id]);
            }
            Yii::$app->session->setFlash("success","Sub service ".$model->ref_id." updated successfully");
            $this->redirect(['index']);
        }

        return $this->renderAjax('update', [
                    'model' => $model,
                    'components' => $components,
                    'sub_service_filters' => $this->getFilters(),
                    'assign_components' => $assign_components
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Deletes an existing SubServices model.
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
     * Finds the SubServices model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SubServices the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = SubServices::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /* get all sub-services */

    public function actionSubservicesAll($id = null, $selectedId = null) {

        $countofservices = SubServices::find()
                ->where(['sid' => $id])
                ->count();

        $servicesAll = SubServices::find()
                ->where(['sid' => $id])
                ->All();

        if ($countofservices > 0) {
            foreach ($servicesAll as $value) {
                if ($selectedId == $value->id) {
                    echo "<option value='" . $value->id . "' selected>" . $value->name . "</option>";
                } else {
                    echo "<option value='" . $value->id . "'>" . $value->name . "</option>";
                }
            }
        } else {
            echo "<option value=''>-</option>";
        }

        // return $servicesAll;
    }

    public function actionGetsubservice() {
        $out = array();
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $service_id = $parents[0];
                $out = SubServices::getSubserviceList($service_id);
                return Json::encode(['output' => $out, 'selected' => '']);
            }
        }
    }

}
