<?php

namespace backend\controllers;

use Yii;
use backend\models\Component;
use backend\models\ComponentBlocks;
use backend\models\ComponentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;

/**
 * ComponentController implements the CRUD actions for Component model.
 */
class ComponentController extends Controller {

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
     * Lists all Component models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new ComponentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Component model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        $model = $this->findModel($id);
        $model->setCreatedByUpdatedByUser();
        return $this->renderAjax('view', [
                    'model' => $model,
        ]);
    }

    /**
     * Creates a new Component model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new Component();
        if ($model->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post();
            $componentBlockList = $postData['Component']['component_blocks'];
            unset($model->component_blocks);
            if ($model->save()) {
                if (!empty($componentBlockList)) {
                    foreach ($componentBlockList as $val) {
                        $componentBlockModel = new ComponentBlocks();
                        $componentBlockModel->component_id = $model->id;
                        $componentBlockModel->block_name = $val;
                        $componentBlockModel->save(false);
                    }
                }
                if (Yii::$app->request->isAjax) {
                    return Json::encode([
                                "success" => true,
                                "data" => $model->attributes
                    ]);
                }
                return $this->redirect(['index']);
            }
        }

        if (Yii::$app->request->isAjax && $model->hasErrors()) {

            return Json::encode([
                        "success" => false,
                        "errors" => \yii\widgets\ActiveForm::validate($model)
            ]);
        }
        return $this->renderAjax('create', [
                    'model' => $model,
        ]);
    }

    /**
     * Updates an existing Component model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);
        if ($model->componentBlocks) {
            $variables = $model->getComponentBlocks()->asArray()->all();
            $variables = array_column($variables, 'block_name');
            $model->component_blocks = $variables;
        }

        if ($model->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post();
            $componentBlockList = $postData['Component']['component_blocks'];
            if ($model->save()) {
                $updateComponentBlock = $model->deleteComponentBlockById($id);
                if (!empty($componentBlockList)) {
                    foreach ($componentBlockList as $val) {
                        $componentBlockModel = new ComponentBlocks();
                        $componentBlockModel->component_id = $model->id;
                        $componentBlockModel->block_name = $val;
                        $componentBlockModel->save(false);
                    }
                } else {
                    
                }
                if (Yii::$app->request->isAjax) {
                    return Json::encode([
                                "success" => true,
                                "data" => $model->attributes
                    ]);
                }
                return $this->redirect(['index']);
            }
        }

        if (Yii::$app->request->isAjax && $model->hasErrors()) {
            return Json::encode([
                        "success" => false,
                        "errors" => \yii\widgets\ActiveForm::validate($model)
            ]);
        }
        return $this->renderAjax('update', [
                    'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Component model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Component model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Component the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Component::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
