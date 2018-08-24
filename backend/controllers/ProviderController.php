<?php

namespace backend\controllers;

use Yii;
use backend\models\Provider;
use backend\models\ProviderSearch;
use backend\models\Role;
use backend\models\RoleSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
/**
 * ProviderController implements the CRUD actions for Provider model.
 */
class ProviderController extends Controller {

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
     * Lists all Provider models.
     * @return mixed
     */
    public function actionIndex() {
        try{

        $searchModel = new ProviderSearch();
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
     * Displays a single Provider model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        
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
     * Creates a new Provider model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        
        try{

        $model = new Provider();
//        $roleSearchModel  =   new RoleSearch();
//        $ddRoleItems    =   ArrayHelper::map(RoleSearch::find()->all(), 'id', 'role');
        if ($model->load(Yii::$app->request->post())) {
//            $role   =   Yii::$app->request->post('RoleSearch');
//            $model->role_id =   $role['role'];            
            $providerArr = Yii::$app->request->post('Provider');
            // $model->active_status   =   $providerArr['active_status'];
            $model->active_status = 0;
            $model->approve_status = 0;
            $model->added_by = Yii::$app->user->id;
            
            $model->save();
            //return $this->redirect(['view', 'id' => $model->id]);
             return $this->redirect(['index']);
        }
        $renderMethod =(Yii::$app->request->isAjax) ?"renderAjax":"render";        
        return $this->$renderMethod('create', [
                    'model' => $model,
//            'dataProvider'  =>  $ddRoleItems,
//            'roleSearchModel'   =>  $roleSearchModel
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Updates an existing Provider model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        
        try{

        $model = $this->findModel($id);
        $roleSearchModel = new RoleSearch();
        $ddRoleItems = ArrayHelper::map(RoleSearch::find()->all(), 'id', 'role');
        if ($model->load(Yii::$app->request->post())) {
            $role = Yii::$app->request->post('RoleSearch');
            $model->role_id = $role['role'];
            $providerArr = Yii::$app->request->post('Provider');
            // $model->active_status = $providerArr['active_status'];
            $model->save();
            //return $this->redirect(['view', 'id' => $model->id]);
            return $this->redirect(['index', 'id' => $model->id]);
        }
        $renderMethod =(Yii::$app->request->isAjax) ?"renderAjax":"render";
        return $this->$renderMethod('update', [
                    'model' => $model,
                    'dataProvider' => $ddRoleItems,
                    'roleSearchModel' => $roleSearchModel
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Deletes an existing Provider model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
       // $this->findModel($id)->delete();
        try{

        $model = new Provider();
        $model = $this->findModel($id);
        $model->active_status = 3;
        $model->save();
        return $this->redirect(['index']);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Finds the Provider model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Provider the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Provider::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

     /**
     * 
     * @param int $id
     * @param int $status
     * @return index
     * this function is update active_status field for accept/reject
     * in the time of insert 0->default,1->accepted,2->rejected
     */
    public function actionUpdatestatus($id, $status) {
        
        try{

        $model = new Provider();
        $model = $this->findModel($id);
        if($status  ==  2){
            $model->active_status = 0;
            $model->approve_status = $status;
        }else{
            $model->active_status = $status;
            $model->approve_status = $status;
        }
        $model->acceptance_action_taken_by = Yii::$app->user->id;
        $model->save(false);
        return $this->redirect(['index']);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }
    
    /**
     * 
     * @param int $id
     * @param int $status
     * @return index
     * this function is update active_status field for active/inactive
     * in the time of insert 0->default,2->inactive,1->active
     */
    public function actionUpdateactiveinactive($id, $status) {
        
        try {
        $model = new Provider();
        $model = $this->findModel($id);
        $model->active_status = $status;
//        $model->approve_status = 0;
        $model->status_action_taken_by = Yii::$app->user->id;
        $model->save(false);
        return $this->redirect(['index']);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }

    }

}
