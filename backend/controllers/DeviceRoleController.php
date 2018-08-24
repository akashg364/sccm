<?php

namespace backend\controllers;

use Yii;
use backend\models\DeviceRole;
use backend\models\DeviceRoleSearch;

use backend\models\ServiceModelTemplate;
use mdm\admin\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DeviceRoleController implements the CRUD actions for DeviceRole model.
 */
class DeviceRoleController extends Controller
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
     * Lists all DeviceRole models.
     * @return mixed
     */
    public function actionIndex()
    {
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();    
        $searchModel = new DeviceRoleSearch();
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
     * Displays a single DeviceRole model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
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
     * Creates a new DeviceRole model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        try {

        $model = new DeviceRole();
		 
        $model->created_by = 1;
		$model->updated_by = 1;
		$model->created_date = date("Y-m-d H:i:s", time());
		$model->updated_date = date("Y-m-d H:i:s", time());
		
		
		if ($model->load(Yii::$app->request->post())) {
			
			if ($model->validate()) {
				// all inputs are valid
			} else {
				// validation failed: $errors is an array containing error messages
				$errors = $model->errors;
				$errors_str = '';
				$i = 0;
				foreach($errors as $key => $val) {
					$errors_str .= $val[$i]."\n";
					$i++;
				}
				\Yii::$app->getSession()->setFlash('error', $errors_str);
				return $this->redirect(['index']);
			}
		}
		
		
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // return $this->redirect(['view', 'id' => $model->id]);
			// \Yii::$app->getSession()->setFlash('error', 'Unable to delete records');
			\Yii::$app->getSession()->setFlash('success', 'Device role added succesfully');
			return $this->redirect(['index']);
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
     * Updates an existing DeviceRole model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        try{

        $model = $this->findModel($id);

       
		if ($model->load(Yii::$app->request->post())) {
			
			if ($model->validate()) {
				// all inputs are valid
			} else {
				// validation failed: $errors is an array containing error messages
				$errors = $model->errors;
				$errors_str = '';
				$i = 0;
				foreach($errors as $key => $val) {
					$errors_str .= $val[$i]."\n";
					$i++;
				}
				\Yii::$app->getSession()->setFlash('error', $errors_str);
				return $this->redirect(['index']);
			}
		}

	   if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // return $this->redirect(['view', 'id' => $model->id]);
			\Yii::$app->getSession()->setFlash('success', 'Device role updated succesfully');
			return $this->redirect(['index']);
        }

        return $this->renderAjax('update', [
            'model' => $model,
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Deletes an existing DeviceRole model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        try{

        ini_set("display_errors", "1");
		error_reporting(E_ALL);
		
		$ServiceModelTemplate = ServiceModelTemplate::find()->where(['device_role_id' => $id])->one();
		
		if ($ServiceModelTemplate != '') {
			\Yii::$app->getSession()->setFlash('error', 'Unable to delete records');
		} else {
			\Yii::$app->getSession()->setFlash('success', 'Successfully deleted records.');
			$this->findModel($id)->delete();
		}
        return $this->redirect(['index']);	
        
        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Finds the DeviceRole model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return DeviceRole the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DeviceRole::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}