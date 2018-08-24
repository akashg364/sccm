<?php

namespace backend\controllers;

use Yii;
use yii\base\ErrorException;
use backend\models\Customer;
use backend\models\CustomerSearch;
use backend\models\Provider;
use backend\models\ProviderSearch;
use backend\models\Role;
use backend\models\RoleSearch;
use mdm\admin\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use \backend\models\AuditLog;
/**
 * CustomerController implements the CRUD actions for Customer model.
 */
class CustomerController extends Controller
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
     * Lists all Customer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel  = new CustomerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        try {
            return $this->render('index', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } catch (ErrorException $e) {
                AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
                return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
            }
           
    }

    /**
     * Displays a single Customer model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {            
        $model        = $this->findModel($id);        
        $dataProvider = ArrayHelper::map(ProviderSearch::find()->all(), 'id', 'company_name');   
        try { 

            $renderMethod = (Yii::$app->request->isAjax) ?"renderAjax":"render";       
            return $this->$renderMethod('view', [
                'model'    => $model,
                'provider' =>  $dataProvider,
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Creates a new Customer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        
        try {

        $user = User::find()->where(['id' => Yii::$app->user->id])->one(); 
        $model = new Customer();
        // $roleSearchModel  =   new RoleSearch();
        // $providerSearchModel    =   new ProviderSearch();
        // $ddRoleItems    =   ArrayHelper::map(RoleSearch::find()->all(), 'id', 'role');
        $ddProviderItems    =   ArrayHelper::map(ProviderSearch::find()->all(), 'id', 'company_name');
        if ($model->load(Yii::$app->request->post())) {              
        // $role   =   Yii::$app->request->post('RoleSearch');
        // $model->role_id =   $role['role'];   
           
            $customerArr  =   Yii::$app->request->post('Customer');
           // $model->active_status   =   $customerArr['active_status'];
            $model->active_status   = 0;
            $model->approve_status = 0;
            $model->created_by = Yii::$app->user->id;
            $model->created_date = date('Y-m-d H:i:s');
            $model->updated_date = date('Y-m-d H:i:s');
            $model->save();
           // return $this->redirect(['view', 'id' => $model->id]);           
            return $this->redirect(['index']);
        }
        $renderMethod =(Yii::$app->request->isAjax) ?"renderAjax":"render";
        return $this->$renderMethod('create', [
            'model' => $model,
//            'dataRoles'  =>  $ddRoleItems,
            'dataProvider'  =>  $ddProviderItems,
            'user'  =>  $user
//            'roleSearchModel'   =>  $roleSearchModel
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Updates an existing Customer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        try {
        $model = $this->findModel($id);
//        $roleSearchModel  =   new RoleSearch();
//        $ddRoleItems    =   ArrayHelper::map(RoleSearch::find()->all(), 'id', 'role');
        $ddProviderItems    =   ArrayHelper::map(ProviderSearch::find()->all(), 'id', 'company_name');
        $userId = Yii::$app->user->id;
        $user = User::find()->where(['id' => $userId])->one();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {  
            //return Json::encode(["sucess"=>true,]);
            return $this->redirect(['index', 'id' => $model->id]);
           // return $this->redirect(['view', 'id' => $model->id]);
            //return $this->redirect(['index']);
        }
        $renderMethod =(Yii::$app->request->isAjax) ?"renderAjax":"render";
        return $this->$renderMethod('update', [
            'model' => $model,
//            'dataRoles'  =>  $ddRoleItems,
            'dataProvider'  =>  $ddProviderItems,
            'user' => $user
//            'roleSearchModel'   =>  $roleSearchModel
        ]);
        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }   

    /**
     * Deletes an existing Customer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
       //$this->findModel($id)->delete();
        try { 
        $model = new Customer();
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
     * Finds the Customer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Customer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Customer::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

     public function actionSelect2Autocomplte($q){

        $data = Customer::find()
        ->where(["active_status"=>1])
        ->andWhere(["LIKE","company_name",$q])
        ->asArray()
        ->all();

        $ret = [];
        foreach ($data as $key => $d) {
                $ret[] = [
                    'id' => $d["id"],
                    'text'=>$d["first_name"]." ".$d["last_name"],
                ];
        }
        return Json::encode(["results"=>$ret]);
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

        try {

        $model = new Customer();
        $model = $this->findModel($id);
//        if($model->approve_status === 0  && $model->active_status === 0){
//            $model->active_status = $status;
//        }
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
     * in the time of insert 0->inactive,1->active
     */
    public function actionUpdateactiveinactive($id, $status) {
        try {
       $model = new Customer();
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
