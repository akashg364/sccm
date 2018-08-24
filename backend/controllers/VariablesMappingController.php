<?php

namespace backend\controllers;

use backend\models\VariablesMapping;
use backend\models\VariablesMappingSearch;
use backend\models\VariablesValues;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use backend\models\Ipv4Pool;
use backend\models\Ipv6Pool;
use yii\helpers\ArrayHelper;

/**
 * VariablesMappingController implements the CRUD actions for VariablesMapping model.
 */
class VariablesMappingController extends Controller {
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
	 * Lists all VariablesMapping models.
	 * @return mixed
	 */
	public function actionIndex() {

		try{

		$searchModel = new VariablesMappingSearch();
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
	 * Displays a single VariablesMapping model.
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

	public function saveVariableValues() {

	}

	/**
	 * Creates a new VariablesMapping model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	public function actionCreate() {

		try{

		$model = new VariablesMapping();

		if ($model->load(Yii::$app->request->post())) {
			$transaction = Yii::$app->db->beginTransaction();
			try {

				if ($model->save()) {
					$valuesModel = new VariablesValues();
					$valuesModel->variable_mapping_id = $model->id;
					$valuesModel->value1 = $model->value1;
					$valuesModel->value2 = $model->value2;
					if (!$valuesModel->save()) {
                        $transaction->rollBack();
						return Json::encode([
							"success" => false,
							"errors" => \yii\widgets\ActiveForm::validate($valuesModel),
						]);
					}

                    $transaction->commit();
					Yii::$app->session->setFlash("success", "Variable Mapped Successfully");
					return Json::encode([
						"success" => true,
						"data" => [],
					]);

					// return $this->redirect(["index"]);
				} else {
                    $transaction->rollBack();
					return Json::encode([
						"success" => false,
						"errors" => \yii\widgets\ActiveForm::validate($model),
					]);
				}

				
			} catch (\Exception $e) {
				$transaction->rollBack();
				throw $e;
			}
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
	 * Updates an existing VariablesMapping model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionUpdate($id) {

		try{

		$model = $this->findModel($id);

		$usedPools = [];
		if (isset($model->variableValues)) {
			$values = $model->variableValues;
			if (count($values) == 1 && isset($values[0]) &&  isset($values[0]->is_completed) && !$values[0]->is_completed) {
				$model->value1 = $values[0]->value1;
				$model->value2 = $values[0]->value2;
			} else {
				foreach ($values as $key => $valueModel) {
					if ($valueModel->is_completed) {
						$usedPools[] = [
							"value1" => $valueModel->value1,
							"value2" => $valueModel->value2,
						];
					} else {
						$model->value1 = $valueModel->value1;
						$model->value2 = $valueModel->value2;
					}
				}
			}
		}
		if ($model->load(Yii::$app->request->post()) ) {
            if($model->save()){

                    $valuesModel = VariablesValues::find()
                                    ->where([
                                        "is_completed"=>0,
                                        "variable_mapping_id"=>$model->id
                                    ])->one();
                    if($valuesModel){ // Update

                        $valuesModel->variable_mapping_id = $model->id;
                        $valuesModel->value1 = $model->value1;
                        $valuesModel->value2 = $model->value2;
                        if (!$valuesModel->save()) {
                            return Json::encode([
                                "success" => false,
                                 "errors" => \yii\widgets\ActiveForm::validate($valuesModel),
                            ]);
                        }
                        return Json::encode([
                            "success" => true,
                            "data" => [],
                        ]);
                    }else{ // Insert
                        $valuesModel = new VariablesValues();
                        $valuesModel->variable_mapping_id = $model->id;
                        $valuesModel->value1 = $model->value1;
                        $valuesModel->value2 = $model->value2;
                        if (!$valuesModel->save()) {
                            $transaction->rollBack();
                            return Json::encode([
                                "success" => false,
                                "errors" => \yii\widgets\ActiveForm::validate($valuesModel),
                            ]);
                        }
                         return Json::encode([
                            "success" => true,
                            "data" => [],
                        ]);
 
                    }                                    
                   
            }else{
                return Json::encode([
                            "success" => false,
                            "errors" => \yii\widgets\ActiveForm::validate($model),
                        ]);  
            }
			//return $this->redirect(["index"]);
		}

		$renderMethod = (Yii::$app->request->isAjax) ? "renderAjax" : "render";
		return $this->$renderMethod('update', [
			'model' => $model,
			"usedPools" => $usedPools,
		]);

		} catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
	}

	/**
	 * Deletes an existing VariablesMapping model.
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
	 * Finds the VariablesMapping model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return VariablesMapping the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id) {
		if (($model = VariablesMapping::findOne($id)) !== null) {
			return $model;
		}

		throw new NotFoundHttpException('The requested page does not exist.');
	}

	//Ajax Call in Variable Mapping form
	public function actionGetPool(){

		$ips = [];
		$ip_type = strtolower(Yii::$app->request->get("ip_type"));
		if($ip_type =='ipv4'){
			$qry = Ipv4Pool::find();
		}else if($ip_type=="ipv6"){	
			$qry = Ipv6Pool::find();
		}
		if(isset($qry)){
			$data = $qry->where(["is_full"=>0])->asArray()->all();
			$ips = ArrayHelper::map($data,"id","pool");

		}
		return Json::encode($ips);
	}
}
