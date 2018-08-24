<?php

namespace backend\controllers;

use backend\models\ServiceMapping;
use backend\models\ServiceMappingSearch;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\Json;
use backend\models\SubServices;
/**
 * ServiceMappingController implements the CRUD actions for ServiceMapping model.
 */
class ServiceMappingController extends Controller {
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
	 * Lists all ServiceMapping models.
	 * @return mixed
	 */
	public function actionIndex() {

		try{

		$searchModel = new ServiceMappingSearch();
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
	 * Displays a single ServiceMapping model.
	 * @param integer $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionView($id) {
		try{

		$renderMethod = (Yii::$app->request->isAjax) ? "renderAjax" : "render";
		return $this->$renderMethod('view', [
			'model' => $this->findModel($id),
		]);

		} catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
	}

	/**
	 * Creates a new ServiceMapping model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	public function actionCreate() {
		
		try{

		$model = new ServiceMapping();

		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		}
		$renderMethod = (Yii::$app->request->isAjax) ? "renderAjax" : "render";
		return $this->$renderMethod('create', [
			'model' => $model,
		]);

		} catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
	}

	/**
	 * Updates an existing ServiceMapping model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionUpdate($id) {
		
		try{

		$model = $this->findModel($id);

		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		}
		$renderMethod = (Yii::$app->request->isAjax) ? "renderAjax" : "render";
		return $this->$renderMethod('update', [
			'model' => $model,
		]);

		} catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
	}

	/**
	 * Deletes an existing ServiceMapping model.
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
	 * Finds the ServiceMapping model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return ServiceMapping the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id) {
		if (($model = ServiceMapping::findOne($id)) !== null) {
			return $model;
		}

		throw new NotFoundHttpException('The requested page does not exist.');
	}

	public function actionGetsubservice() {

		if (isset($_POST['depdrop_parents'])) {
			$parents = $_POST['depdrop_parents'];
			if ($parents != null) {
				$service_id = $parents[0];

				$ret = [];
				$data = ServiceMapping::find()
                    ->alias("sm")
                    ->select(["sm.sub_service_id","sm.service_id"])
					->joinWith(["subService"=>function($q){return $q->alias("ss");}])
					->where(["sm.service_id" => $service_id])
					->asArray()
					->groupBy(["sm.sub_service_id"])
					->all();

				if ($data) {
					foreach ($data as $key => $d) {
						$subservice = $d["subService"];
						$ret[] = ["id" => $subservice["id"], "name" => $subservice["ref_id"]."- ".$subservice["name"]];
					}
					return Json::encode(['output' => $ret, 'selected' => '']);
				}
				$out = SubServices::getSubserviceList($service_id);
				return Json::encode(['output' => $out, 'selected' => '']);
			}
		}

	}

    public function actionGettopology() {

        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $service_id = $parents[0];
                $sub_service_id = $parents[1];

                $ret = [];
                $data = ServiceMapping::find()
                    ->joinWith(["topology"])
                    ->where(["service_id" => $service_id,"sub_service_id"=>$sub_service_id])
                    ->asArray()
                    ->all();

                if ($data) {
                    foreach ($data as $key => $d) {
                        $topology = $d["topology"];
                        $ret[] = ["id" => $topology["id"], "name" => $topology["name"]];
                    }
                    return Json::encode(['output' => $ret, 'selected' => '']);
                }
                // $out = SubServices::getSubserviceList($service_id);
                // return Json::encode(['output' => $out, 'selected' => '']);
            }
        }

    }
}
